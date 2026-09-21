#!/bin/sh
set -e

# El worker de cola solo debe correr en producción. Mismo Dockerfile para
# los dos entornos, así que la diferencia la decide esta variable de
# entorno, no el supervisor.conf en sí: si RUN_QUEUE_WORKER=true, se copia
# el .ini del worker a /etc/supervisor.d/ ANTES de que supervisord arranque
# y lea su configuración (mismo mecanismo con el que ya carga php-fpm y
# nginx) — evita cualquier condición de carrera de intentar arrancarlo
# después vía supervisorctl.
if [ "$RUN_QUEUE_WORKER" = "true" ]; then
    cp /var/www/docker/queue-worker.conf /etc/supervisor.d/queue-worker.ini
fi

exec supervisord -c /etc/supervisord.conf
