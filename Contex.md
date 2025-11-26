## Constancias – Contexto General

- **Objetivo**: replicar el flujo de constancia general del proyecto multidisciplinario en un nuevo stack moderno (Laravel 11, PHP 8.2+) y con infraestructura Dockerizada.
- **Ubicación**: `/home/erick/Documentos/Docker/constancias`.
- **Estado actual**:
  - Proyecto Laravel limpio generado con `composer create-project laravel/laravel`.
  - Pendiente configurar Docker (php-fpm + nginx + MySQL) y adaptar `.env`.
  - Aún no se ha migrado la lógica de constancias ni assets.
- **Siguientes pasos recomendados**:
  1. Definir Dockerfiles/compose y levantar entorno local.
  2. Configurar base de datos y autenticación.
  3. Migrar modelos/migraciones relacionados con constancias generales.
  4. Portar controladores, servicios y vistas necesarios.
  5. Agregar pruebas y documentación del flujo.

