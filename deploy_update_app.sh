#!/bin/bash

# Configuration
REMOTE_USER="clientessh"
REMOTE_HOST="cedesoft.utcv.edu.mx"
REMOTE_DIR="/home/clientessh/constancias"
# Default Configuration
DEFAULT_APP_PORT=8180
DEFAULT_DB_PORT=3309
DOCKER_IMAGE="ajfe/constancias:latest"

echo "=== Actualización de APP Constancias (SOLO APP) ==="
echo "Servidor: $REMOTE_USER@$REMOTE_HOST"
echo "Este script actualizará el CÓDIGO de la aplicación sin tocar la Base de Datos."
echo "------------------------------------------------------------------------------"

# 1. Credentials
read -s -p "Introduce la contraseña SUDO (para elevación de privilegios en servidor): " SUDO_PASS
echo ""

# SSH Configuration for Multiplexing
SSH_SOCKET=~/.ssh/constancias_deploy_socket
if [ -S "$SSH_SOCKET" ]; then
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
fi

echo ">> [1/6] Estableciendo conexión SSH maestra..."
ssh -M -S "$SSH_SOCKET" -f -N -o ControlPersist=10m "$REMOTE_USER@$REMOTE_HOST"

if [ $? -ne 0 ]; then
    echo "Error: No se pudo establecer conexión SSH."
    exit 1
fi

# Helper functions
remote_exec() {
    ssh -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "$1"
}

sudo_exec() {
    printf '%s\n' "$SUDO_PASS" | ssh -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "sudo -S -p '' $1"
}

scp_exec() {
    scp -o ControlPath="$SSH_SOCKET" "$1" "$REMOTE_USER@$REMOTE_HOST:$2"
}

# 1.5 Verify Sudo
if ! sudo_exec "-v"; then
    echo "Error: La contraseña SUDO parece incorrecta."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

# 1.8 Build Frontend Assets (CRITICAL)
echo ">> [1.8/6] Compilando assets frontend (npm run build)..."
npm run build

if [ $? -ne 0 ]; then
    echo "Error: Falló la compilación de assets."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

# 2. Build Images
echo ">> [2/6] Construyendo imágenes Docker..."

echo "   - [App] Construyendo..."
docker build --pull --no-cache -t "$DOCKER_IMAGE" --build-arg user=constancias --build-arg uid=1000 .

echo "   - [Nginx] Construyendo..."
docker build --pull --no-cache -t ajfe/constancias-nginx:latest -f docker/nginx/Dockerfile .

# 2.5 Compress
echo ">> [2.5/6] Comprimiendo imágenes..."
IMAGE_ARCHIVE="deploy_images_update.tar.gz"
docker save "$DOCKER_IMAGE" ajfe/constancias-nginx:latest | gzip > "$IMAGE_ARCHIVE"

echo "   - Tamaño: $(du -h $IMAGE_ARCHIVE | cut -f1)"

# 3. Transfer
echo ">> [3/6] Transfiriendo archivos..."
# Ensure remote dir exists
remote_exec "mkdir -p $REMOTE_DIR"

# Only upload things that might change code/config, skip .env if strictly code update? 
# Usually safest to upload .env and docker-compose just in case they changed locally.
scp_exec "docker-compose.prod.yml" "$REMOTE_DIR/docker-compose.yml"
scp_exec ".env.production" "$REMOTE_DIR/.env"
scp_exec "$IMAGE_ARCHIVE" "$REMOTE_DIR/$IMAGE_ARCHIVE"

# 4. Deploy
echo ">> [4/6] Desplegando en servidor..."

# Load images
echo "   - Cargando imágenes..."
sudo_exec "sh -c 'gunzip -c $REMOTE_DIR/$IMAGE_ARCHIVE | docker load'"
remote_exec "rm $REMOTE_DIR/$IMAGE_ARCHIVE"

# Restart containers
echo "   - Reiniciando contenedores..."
# We use 'up -d' which effectively updates only changed containers.
DEPLOY_CMD="sh -c 'cd $REMOTE_DIR && export COMPOSE_HTTP_TIMEOUT=300 && export DOCKER_CLIENT_TIMEOUT=300 && APP_PORT=$DEFAULT_APP_PORT DB_PORT=$DEFAULT_DB_PORT docker-compose up -d --force-recreate --remove-orphans'"
sudo_exec "$DEPLOY_CMD"

# 5. Migrations
echo ">> [5/6] Ejecutando migraciones (DB Schema)..."
sudo_exec "docker exec constancias-app php artisan migrate --force"

# Cleanup
echo ">> Cerrando..."
rm "$IMAGE_ARCHIVE"
ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null

echo ">> [Listo] ¡Código actualizado correctamente!"
