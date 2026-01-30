#!/bin/bash

# Configuration
REMOTE_USER="clientessh"
REMOTE_HOST="cedesoft.utcv.edu.mx"
REMOTE_DIR="/home/clientessh/constancias"
DOCKER_IMAGE="ajfe/constancias:latest"

echo "=== Despliegue Remoto (Solo App/Queue) ==="
echo "Servidor: $REMOTE_USER@$REMOTE_HOST"
echo "Imagen App: $DOCKER_IMAGE"
echo "Nota: Compila, construye local y transfiere la imagen al servidor."
echo "      No toca BD/Redis ni volúmenes."

# 1. Credentials
SSH_KEY="${SSH_KEY:-$HOME/.ssh/id_rsa}"
if [ ! -f "$SSH_KEY" ]; then
    echo "Error: no se encontró la llave SSH en $SSH_KEY"
    echo "Configura una llave SSH o exporta SSH_KEY=/ruta/a/llave"
    exit 1
fi

read -s -p "Introduce la contraseña SUDO (para elevación de privilegios en servidor): " SUDO_PASS
echo ""

# 1.8 Build Frontend Assets
echo ">> [1/7] Compilando assets frontend (npm run build)..."
npm run build

if [ $? -ne 0 ]; then
    echo "Error: Falló la compilación de assets."
    exit 1
fi

# 2. Build (Local)
echo ">> [2/7] Construyendo imagen App local..."
docker build --pull --no-cache -t "$DOCKER_IMAGE" --build-arg user=constancias --build-arg uid=1000 .

if [ $? -ne 0 ]; then
    echo "Error: Falló la construcción de la imagen App."
    exit 1
fi

# 3. Export (Local)
echo ">> [3/7] Exportando imagen para transferencia..."
IMAGE_ARCHIVE="deploy_app_image.tar.gz"
docker save "$DOCKER_IMAGE" | gzip > "$IMAGE_ARCHIVE"

if [ $? -ne 0 ]; then
    echo "Error: Falló la exportación de la imagen App."
    exit 1
fi

echo "   - Tamaño del archivo: $(du -h $IMAGE_ARCHIVE | cut -f1)"

# 4. SSH Multiplexing
SSH_SOCKET=~/.ssh/constancias_deploy_socket
if [ -S "$SSH_SOCKET" ]; then
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
fi

echo ">> [4/7] Estableciendo conexión SSH..."
ssh -i "$SSH_KEY" -M -S "$SSH_SOCKET" -f -N -o ControlPersist=10m "$REMOTE_USER@$REMOTE_HOST"
if [ $? -ne 0 ]; then
    echo "Error: No se pudo establecer conexión SSH."
    exit 1
fi

remote_exec() {
    local CMD="$1"
    ssh -i "$SSH_KEY" -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "$CMD"
}

sudo_exec() {
    local CMD="$1"
    printf '%s\n' "$SUDO_PASS" | ssh -i "$SSH_KEY" -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "sudo -S -p '' $CMD"
}

# Verify sudo
echo ">> [5/7] Verificando credenciales SUDO..."
if ! sudo_exec "-v"; then
    echo "Error: La contraseña SUDO parece incorrecta."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

# Transfer + Load + Deploy
echo ">> [6/7] Transfiriendo y desplegando app/queue..."
scp_exec() {
    local SRC="$1"
    local DEST="$2"
    scp -i "$SSH_KEY" -o ControlPath="$SSH_SOCKET" "$SRC" "$REMOTE_USER@$REMOTE_HOST:$DEST"
}

remote_exec "mkdir -p $REMOTE_DIR"
echo "   - Subiendo imagen comprimida..."
scp_exec "$IMAGE_ARCHIVE" "$REMOTE_DIR/$IMAGE_ARCHIVE"

echo "   - Cargando imagen en Docker remoto..."
LOAD_CMD="sh -c 'gunzip -c $REMOTE_DIR/$IMAGE_ARCHIVE | docker load'"
sudo_exec "$LOAD_CMD"

remote_exec "rm $REMOTE_DIR/$IMAGE_ARCHIVE"

echo "   - Reiniciando app/queue..."
sudo_exec "sh -c 'cd $REMOTE_DIR && docker-compose up -d --no-deps app queue'"

echo "   - Limpieza de imágenes colgantes..."
sudo_exec "docker image prune -f"

# Status + checks
echo ">> [7/7] Verificando estado y conectividad..."
sudo_exec "sh -c 'cd $REMOTE_DIR && docker-compose ps'"
sudo_exec "docker ps --format \"table {{.Names}}\t{{.Status}}\t{{.Ports}}\""
sudo_exec "sh -c 'cd $REMOTE_DIR && docker-compose port web 80 || true'"

# Check app/queue running
sudo_exec "docker inspect -f '{{.State.Status}}' constancias-app"
sudo_exec "docker inspect -f '{{.State.Status}}' constancias-queue"

# Basic connectivity from app to db/redis (service names)
sudo_exec "docker exec constancias-app php -r \"exit((int)!@fsockopen('db',3306));\""
sudo_exec "docker exec constancias-app php -r \"exit((int)!@fsockopen('redis',6379));\""

ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
rm "$IMAGE_ARCHIVE"
echo ">> [Listo] App/Queue actualizados sin tocar BD/Redis."
