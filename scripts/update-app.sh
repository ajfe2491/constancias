#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="${ROOT_DIR}/docker-compose.prod.yml"

if [[ ! -f "$COMPOSE_FILE" ]]; then
  echo "No se encontró docker-compose.prod.yml en: $COMPOSE_FILE"
  exit 1
fi

cd "$ROOT_DIR"

echo "Actualizando solo app y queue (sin tocar db/redis)..."
docker compose -f "$COMPOSE_FILE" pull app queue
docker compose -f "$COMPOSE_FILE" up -d --no-deps app queue

echo "Listo. Contenedores app/queue actualizados."
