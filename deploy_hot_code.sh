#!/bin/bash

# Configuration
REMOTE_USER="clientessh"
REMOTE_HOST="cedesoft.utcv.edu.mx"
REMOTE_DIR="/home/clientessh/constancias"
TMP_CODE_DIR="$REMOTE_DIR/hot_code"

echo "=== Despliegue Súper Rápido (Solo Código HOT SWAP) ==="
echo "Servidor: $REMOTE_USER@$REMOTE_HOST"
echo "Nota: Este script copia el código directamente al contenedor en ejecución."
echo "      No reconstruye la imagen de Docker, por lo que toma solo unos segundos."

# Credentials
read -s -p "Introduce la contraseña SUDO (para permisos de Docker en servidor): " SUDO_PASS
echo ""

# SSH Configuration for Multiplexing
SSH_SOCKET=~/.ssh/constancias_deploy_socket
if [ -S "$SSH_SOCKET" ]; then
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
fi

echo ">> [1/5] Estableciendo conexión SSH maestra..."
ssh -M -S "$SSH_SOCKET" -f -N -o ControlPersist=10m "$REMOTE_USER@$REMOTE_HOST"
if [ $? -ne 0 ]; then
    echo "Error: No se pudo establecer conexión SSH."
    exit 1
fi

remote_exec() {
    ssh -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "$1"
}

sudo_exec() {
    printf '%s\n' "$SUDO_PASS" | ssh -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "sudo -S -p '' $1"
}

# Verify Sudo
echo ">> [Verify] Verificando credenciales SUDO..."
if ! sudo_exec "-v"; then
    echo "Error: La contraseña SUDO parece incorrecta."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

echo ">> [2/5] Compilando assets (opcional)..."
if command -v npm &> /dev/null; then
    npm run build
else
    echo "Advertencia: npm no está instalado localmente. Se omitirá."
fi

echo ">> [3/5] Transfiriendo código fuente con rsync..."
remote_exec "mkdir -p $TMP_CODE_DIR"

# Usamos rsync para transferir solo lo necesario (mucho más rápido)
rsync -az -e "ssh -o ControlPath=$SSH_SOCKET" \
    --exclude 'vendor' \
    --exclude 'node_modules' \
    --exclude '.git' \
    --exclude 'storage/*' \
    --exclude 'bootstrap/cache/*' \
    --exclude 'public/qrs/*' \
    --exclude 'public/storage' \
    --exclude '.env*' \
    --exclude '*.tar.gz' \
    ./ "$REMOTE_USER@$REMOTE_HOST:$TMP_CODE_DIR/"

echo ">> [4/5] Inyectando código nuevo en los contenedores (app, queue y web)..."
# Copiamos los archivos directamente al contenedor en ejecución
sudo_exec "docker cp $TMP_CODE_DIR/. constancias-app:/var/www/html/"
sudo_exec "docker cp $TMP_CODE_DIR/. constancias-queue:/var/www/html/"
# IMPORTANTE: También copiar assets estáticos al contenedor nginx
sudo_exec "docker cp $TMP_CODE_DIR/public/build/. constancias-web:/var/www/html/public/build/"

echo ">> [5/5] Ajustando permisos y limpiando cachés en contenedores..."
# Restaurar permisos SOLO en las carpetas de codigo
sudo_exec "docker exec constancias-app chown -R constancias:constancias /var/www/html/app /var/www/html/config /var/www/html/routes /var/www/html/resources /var/www/html/database /var/www/html/public /var/www/html/bootstrap"
sudo_exec "docker exec constancias-queue chown -R constancias:constancias /var/www/html/app /var/www/html/config /var/www/html/routes /var/www/html/resources /var/www/html/database /var/www/html/public /var/www/html/bootstrap"

# Limpiar cache forzadamente antes de artisan (por si acaso artisan no puede iniciar)
sudo_exec "docker exec constancias-app sh -c 'rm -f /var/www/html/bootstrap/cache/*.php'"
sudo_exec "docker exec constancias-queue sh -c 'rm -f /var/www/html/bootstrap/cache/*.php'"

# Optimizar cachés

sudo_exec "docker exec -u constancias constancias-app php artisan optimize:clear"
sudo_exec "docker exec -u constancias constancias-app php artisan config:cache"
sudo_exec "docker exec -u constancias constancias-app php artisan view:cache"

# Reiniciar los workers para que tomen el nuevo código
sudo_exec "docker exec -u constancias constancias-queue php artisan queue:restart"

# Limpieza
remote_exec "rm -rf $TMP_CODE_DIR"
ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null

echo ">> [Listo] ¡Código inyectado en caliente! Despliegue finalizado en segundos."
