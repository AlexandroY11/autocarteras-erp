# Arquitectura

## Stack

- **Laravel 12**, **PHP 8.4** (imagen `php:8.4-fpm-alpine`).
- **PostgreSQL** — no MySQL/SQLite en ningún entorno real; los tests también corren contra Postgres (`phpunit.xml` apunta a una BD Postgres real, `autocarteras_cali_test`), no sqlite en memoria — el proyecto depende de comportamiento específico de Postgres (constraints CHECK, secuencias, `whereRaw`) que sqlite no reproduce fielmente.
- **Docker** — nginx + php-fpm vía supervisord; el worker de cola (`queue:work`) es **condicional**, activado solo en producción vía la variable de entorno `RUN_QUEUE_WORKER=true` (mismo Dockerfile para dev y producción).
- **Sanctum** — autenticación de tokens para consumidores API/integraciones (ver `01-roles-y-permisos.md`).
- **Eloquent** — ORM, sin capa de repositorio adicional.
- **Blade + Alpine.js** — frontend del panel web. **Livewire está instalado** (`livewire/livewire` en `composer.json`) **pero su continuidad o eliminación no está decidida** — no se usa activamente en ninguna vista de este proyecto hoy (el patrón real es Blade + Alpine.js). No tocar la dependencia de Livewire sin necesidad concreta ni sin preguntar primero.
- **API REST** — para integraciones externas (n8n, WhatsApp), bajo `routes/api.php`, con abilities de Sanctum por token.

## Qué NO introducir sin necesidad concreta

No agregar, sin una necesidad real y aprobada, ninguno de estos patrones — son sobre-ingeniería para el tamaño y complejidad actual del proyecto:
- Microservicios.
- Capa de repositorios (Repository pattern) sobre Eloquent.
- CQRS.
- Event sourcing.

## Regla de centralización: Web y API comparten la misma lógica de negocio

El flujo es **Controller → Service → Models/DB**. La lógica de negocio real (cálculos, validaciones de reglas, efectos secundarios) vive en la capa de **Service** (`app/Modules/*/Services/`, `app/Services/`), nunca duplicada entre un controlador Web y uno API para la misma acción. Cuando ambos canales necesitan la misma operación (ej. crear un pedido, registrar un pago), ambos deben llamar al mismo Service — nunca reimplementar la regla dos veces con el riesgo de que diverjan.

## Qué queda sin definir

- No hay una decisión tomada sobre el futuro de Livewire — si se decide adoptarlo activamente o eliminarlo, es una decisión de arquitectura que debe aprobarse explícitamente, no inferirse de que está en `composer.json`.
- No hay documentación de un límite de escala (usuarios concurrentes, volumen de datos) que justifique revisar esta arquitectura — no asumir que hace falta escalar sin que el negocio lo indique.
