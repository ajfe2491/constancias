#!/bin/bash

# Configuration
REMOTE_USER="clientessh"
REMOTE_HOST="cedesoft.utcv.edu.mx"
REMOTE_DIR="/home/clientessh/constancias"
# Default Configuration
DEFAULT_APP_PORT=8180
DEFAULT_DB_PORT=3309
DOCKER_IMAGE="ajfe/constancias:latest"

echo "=== Despliegue Remoto de Constancias (Modo: Transferencia Directa) ==="
echo "Servidor: $REMOTE_USER@$REMOTE_HOST"
echo "Imagen App: $DOCKER_IMAGE"
echo "Imagen Web: ajfe/constancias-nginx:latest"
echo "Nota: Las imágenes se enviarán comprimidas directamente al servidor (sin Docker Hub)."

# 1. Credentials
# Only ask for SUDO password manually since SSH will prompt natively
read -s -p "Introduce la contraseña SUDO (para elevación de privilegios en servidor): " SUDO_PASS
echo ""

# WARN USER ABOUT DB OVERWRITE
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
echo "¡ADVERTENCIA! Has elegido el modo de DESPLIEGUE COMPLETO (FULL)."
echo "Esto SOBREESCRIBIRÁ la base de datos remota con tu base de datos local."
echo "Se perderán los datos en el servidor si no están en tu local."
echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
read -p "¿Estás seguro de continuar? (escribe 'si' para confirmar): " CONFIRM
if [ "$CONFIRM" != "si" ]; then
    echo "Cancelado."
    exit 0
fi

# SSH Configuration for Multiplexing (avoids re-typing password without sshpass)
SSH_SOCKET=~/.ssh/constancias_deploy_socket
# Cleanup previous socket if exists
if [ -S "$SSH_SOCKET" ]; then
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
fi

echo ">> [1/6] Estableciendo conexión SSH maestra..."
echo "       (Por favor introduce tu contraseña SSH si se te solicita)"
# Start master connection in background (-f -N)
ssh -M -S "$SSH_SOCKET" -f -N -o ControlPersist=10m "$REMOTE_USER@$REMOTE_HOST"

if [ $? -ne 0 ]; then
    echo "Error: No se pudo establecer conexión SSH."
    exit 1
fi

# Helper function for remote command execution using the socket
remote_exec() {
    local CMD="$1"
    ssh -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "$CMD"
}

# Helper function to execute sudo commands remotely
# Pipes the password securely through SSH stdin
sudo_exec() {
    local CMD="$1"
    # echo "$SUDO_PASS" | ssh ... "sudo -S -p '' cmd"
    # Use printf locally to avoid shell expansion issues
    printf '%s\n' "$SUDO_PASS" | ssh -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "sudo -S -p '' $CMD"
}

# Helper function for SCP using the socket
scp_exec() {
    local SRC="$1"
    local DEST="$2"
    scp -o ControlPath="$SSH_SOCKET" "$SRC" "$REMOTE_USER@$REMOTE_HOST:$DEST"
}

# 1.5 Verify Sudo Credentials immediately
echo ">> [Verify] Verificando credenciales SUDO..."
if ! sudo_exec "-v"; then
    echo "Error: La contraseña SUDO parece incorrecta o no se pudo validar."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

# 1.8 Build Frontend Assets
echo ">> [1.8/6] Compilando assets frontend (npm run build)..."
npm run build

if [ $? -ne 0 ]; then
    echo "Error: Falló la compilación de assets."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

# 1.9 Dump Local Database
echo ">> [1.9/6] Exportando Base de Datos local (mysqldump)..."
DB_DUMP_FILE="constancias_dump.sql"
docker exec constancias-db mysqldump -u root -proot constancias > "$DB_DUMP_FILE" 2>/dev/null

if [ $? -ne 0 ]; then
    echo "Error: Falló el dump de la base de datos local. ¿Está corriendo el contenedor constancias-db?"
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi
echo "   - Dump creado: $DB_DUMP_FILE"


# 2. Build & Push (Local)
echo ">> [2/6] Preparando imagen Docker (Build & Push)..."

echo "   - [App] Construyendo imagen localmente..."
docker build --pull --no-cache -t "$DOCKER_IMAGE" --build-arg user=constancias --build-arg uid=1000 .

if [ $? -ne 0 ]; then
    echo "Error: Falló la construcción de la imagen App."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

echo "   - [Nginx] Construyendo imagen Nginx localmente..."
docker build --pull --no-cache -t ajfe/constancias-nginx:latest -f docker/nginx/Dockerfile .

if [ $? -ne 0 ]; then
    echo "Error: Falló la construcción de la imagen Nginx."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

# New Step: Save and Compress
echo ">> [2.5/6] Exportando y comprimiendo imágenes y BD para transferencia..."
IMAGE_ARCHIVE="deploy_images_full.tar.gz"

# Save both images AND the sql dump to a single tarball (trick: tar the sql and the docker save output stream? No simpler to just tar files)
# Better: Save docker images to file first then tar everything? No, takes space.
# Current approach: docker save | gzip. 
# Let's just scp the SQL separately or include it in a tarball.
# Let's keep images separate for clarity. 
echo "   - Generando archivo comprimido de imágenes..."
docker save "$DOCKER_IMAGE" ajfe/constancias-nginx:latest | gzip > "$IMAGE_ARCHIVE"

if [ $? -ne 0 ]; then
    echo "Error: Falló la exportación de las imágenes."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

echo "   - Tamaño del archivo a transferir: $(du -h $IMAGE_ARCHIVE | cut -f1)"

# 3. Setup Remote Directory
echo ">> [3/6] Preparando directorio remoto..."
remote_exec "mkdir -p $REMOTE_DIR"

# 4. Transfer Files (Config Only)
echo ">> [4/6] Transfiriendo archivos de configuración y datos..."

echo "   - Subiendo docker-compose..."
scp_exec "docker-compose.prod.yml" "$REMOTE_DIR/docker-compose.yml"

echo "   - Subiendo archivo de entorno (.env)..."
scp_exec ".env.production" "$REMOTE_DIR/.env"

echo "   - Subiendo imágenes comprimidas..."
scp_exec "$IMAGE_ARCHIVE" "$REMOTE_DIR/$IMAGE_ARCHIVE"

echo "   - Subiendo respaldo de Base de Datos..."
scp_exec "$DB_DUMP_FILE" "$REMOTE_DIR/$DB_DUMP_FILE"


# 5. Remote Pull & Deploy
echo ">> [5/6] Actualizando infraestructura en el servidor..."

# Load images
echo "   - Cargando imágenes en Docker remoto (Load)..."
# We pipe gunzip to docker load (wrap in sh -c so sudo applies to the whole pipe)
LOAD_CMD="sh -c 'gunzip -c $REMOTE_DIR/$IMAGE_ARCHIVE | docker load'"
sudo_exec "$LOAD_CMD"

# Remove archive from server to save space
remote_exec "rm $REMOTE_DIR/$IMAGE_ARCHIVE"

# Restart containers
echo "   - Reiniciando servicios..."
# We pass env vars inside the sh -c to ensure they are set for docker-compose
# Included timeouts just in case
DEPLOY_CMD="sh -c 'cd $REMOTE_DIR && export COMPOSE_HTTP_TIMEOUT=300 && export DOCKER_CLIENT_TIMEOUT=300 && APP_PORT=$DEFAULT_APP_PORT DB_PORT=$DEFAULT_DB_PORT docker-compose up -d --force-recreate --remove-orphans'"
sudo_exec "$DEPLOY_CMD"

# Wait for DB
echo "   - Esperando a que el servicio de BD esté listo (15s)..."
sleep 15

# 5.4 Backup Remote Database (Safety Net)
echo ">> [5.4/6] Generando respaldo de seguridad de BD remota..."
BACKUP_DIR="/home/$REMOTE_USER/respaldo-bases-de-datos"
BACKUP_NAME="backup_constancias_pre_deploy_$(date +%Y%m%d_%H%M%S).sql"
BACKUP_PATH="$BACKUP_DIR/$BACKUP_NAME"

echo "   - Creando directorio de respaldos si no existe: $BACKUP_DIR"
# We use sudo_exec to ensure we can execute, but we need to make sure permissions are okay.
# Using mkdir -p. 
sudo_exec "mkdir -p $BACKUP_DIR"

echo "   - Guardando respaldo en: $BACKUP_PATH"
# Use sh -c to verify redirection works with sudo
BACKUP_CMD="sh -c 'docker exec constancias-db mysqldump -u root -proot constancias > $BACKUP_PATH'"
sudo_exec "$BACKUP_CMD"

if [ $? -ne 0 ]; then
    echo "ADVERTENCIA: No se pudo crear el respaldo remoto. ¿Es el primer despliegue?"
    echo "Continuando..."
else
    echo "   - Respaldo creado exitosamente."
fi

# 5.5 Restore Database
echo ">> [5.5/6] Restaurando Base de Datos local en remoto..."
# We pipe the file into the docker exec command (wrap in sh -c so sudo applies)
# Note: remote container name is constancias-db
RESTORE_CMD="sh -c 'cat $REMOTE_DIR/$DB_DUMP_FILE | docker exec -i constancias-db mysql -u root -proot constancias'"
sudo_exec "$RESTORE_CMD"

# 6. Database Migrations (Just in case)
echo ">> [6/6] Verificando migraciones..."
MIGRATE_CMD="docker exec constancias-app php artisan migrate --force"
sudo_exec "$MIGRATE_CMD"

# Cleanup
echo ">> Cerrando conexión SSH..."
# Remove dump remote
remote_exec "rm $REMOTE_DIR/$DB_DUMP_FILE"

# Check status before exiting
echo ">> Verificando estado de los contenedores..."
CHECK_CMD="docker ps --format \"table {{.Names}}\t{{.Status}}\t{{.Ports}}\""
sudo_exec "$CHECK_CMD"

ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null

echo ">> [Listo] ¡Despliegue completo con sincronización de BD finalizado!"
echo "La aplicación debería estar corriendo en: http://$REMOTE_HOST:$DEFAULT_APP_PORT"

# Local cleanup
rm "$IMAGE_ARCHIVE"
rm "$DB_DUMP_FILE"
