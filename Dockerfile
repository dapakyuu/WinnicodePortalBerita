# ---------- Tahap 1: dependency PHP (Composer) ----------
FROM composer:2 AS vendor
WORKDIR /app

# Install dependency dulu agar layer ini ter-cache selama composer.* tidak berubah
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist \
        --no-scripts --no-autoloader --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# ---------- Tahap 2: PHP-FPM + Nginx ----------
FROM php:8.3-fpm-alpine

# Ekstensi bawaan image sudah mencakup pdo_sqlite, mbstring, curl, xml, dll.
# pdo_mysql ditambahkan agar bisa pindah ke MySQL cukup lewat environment.
RUN apk add --no-cache nginx \
    && docker-php-ext-install opcache pdo_mysql \
    && mkdir -p /run/nginx

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html

RUN sed -i 's/\r$//' /usr/local/bin/entrypoint \
    && chmod +x /usr/local/bin/entrypoint \
    && php artisan package:discover \
    && chown -R www-data:www-data storage bootstrap/cache

# Data yang harus bertahan saat redeploy: database SQLite & file unggahan
VOLUME ["/var/www/html/storage"]

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=90s --retries=3 \
  CMD wget -q --spider http://127.0.0.1/up || exit 1

ENTRYPOINT ["entrypoint"]
