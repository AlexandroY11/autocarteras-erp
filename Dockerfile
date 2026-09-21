FROM php:8.4-fpm-alpine

# Dependencias del sistema
RUN apk add --no-cache \
    git curl zip unzip nginx supervisor \
    libpng-dev oniguruma-dev libxml2-dev \
    postgresql-dev nodejs npm autoconf g++ make \
    libzip-dev

# Extensiones PHP
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd zip

# Redis via pecl
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar código
COPY . .

# Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Instalar dependencias JS y compilar
RUN npm install && npm run build && rm -rf node_modules

# Permisos — laravel-excel se crea en runtime (maatwebsite/excel), la
# pre-creamos aquí para que quede con el dueño correcto desde el build y no
# la cree primero un proceso con otro usuario (ej. root via tinker) dejándola
# sin permiso de escritura para php-fpm/nginx (corre como www-data).
RUN mkdir -p /var/www/storage/framework/cache/laravel-excel \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor — usar la carpeta que Alpine ya tiene configurada
RUN mkdir -p /etc/supervisor.d/
COPY docker/supervisor.conf /etc/supervisor.d/laravel.ini

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]