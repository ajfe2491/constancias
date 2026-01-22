#!/bin/bash

# Configuracion
REMOTE_USER="clientessh"
REMOTE_HOST="cedesoft.utcv.edu.mx"
APP_CONTAINER="constancias-app"

echo "=== Verificacion remota de entorno y limpieza de cache ==="
echo "Servidor: $REMOTE_USER@$REMOTE_HOST"
echo "Contenedor: $APP_CONTAINER"
echo "---------------------------------------------------------"

read -s -p "Introduce la contraseña SUDO (para elevacion en servidor): " SUDO_PASS
echo ""

SSH_SOCKET=~/.ssh/constancias_verify_socket
if [ -S "$SSH_SOCKET" ]; then
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
fi

echo ">> Estableciendo conexion SSH maestra..."
ssh -M -S "$SSH_SOCKET" -f -N -o ControlPersist=10m "$REMOTE_USER@$REMOTE_HOST"

if [ $? -ne 0 ]; then
    echo "Error: No se pudo establecer conexion SSH."
    exit 1
fi

sudo_exec() {
    printf '%s\n' "$SUDO_PASS" | ssh -S "$SSH_SOCKET" "$REMOTE_USER@$REMOTE_HOST" "sudo -S -p '' $1"
}

echo ">> Verificando credenciales SUDO..."
if ! sudo_exec "-v"; then
    echo "Error: La contraseña SUDO parece incorrecta."
    ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null
    exit 1
fi

echo ">> Variables de entorno (DB/SESSION/REDIS):"
sudo_exec "docker exec $APP_CONTAINER printenv | egrep 'DB_|SESSION_|REDIS_' || true"

echo ">> Limpieza de cache de Laravel:"
sudo_exec "docker exec $APP_CONTAINER php artisan config:clear"
sudo_exec "docker exec $APP_CONTAINER php artisan cache:clear"

echo ">> Verificando conexion a base de datos:"
sudo_exec "docker exec $APP_CONTAINER php artisan tinker --execute='DB::connection()->getPdo(); echo \"OK\";'"

echo ">> Cerrando conexion SSH..."
ssh -S "$SSH_SOCKET" -O exit "$REMOTE_USER@$REMOTE_HOST" 2>/dev/null

echo ">> [Listo] Verificacion completada."
