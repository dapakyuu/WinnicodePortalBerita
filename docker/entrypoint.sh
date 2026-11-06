#!/bin/sh
# Dijalankan setiap kali container start:
# siapkan folder storage, cache konfigurasi, migrasi database, lalu jalankan PHP-FPM + Nginx.
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY belum diisi. Buat dengan: php artisan key:generate --show" >&2
    exit 1
fi

# Volume storage masih kosong saat pertama kali dipakai
mkdir -p storage/app/public storage/database storage/logs \
         storage/framework/cache/data storage/framework/sessions storage/framework/views

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-/var/www/html/storage/database/database.sqlite}"
    [ -f "$DB_FILE" ] || touch "$DB_FILE"
fi

# public/storage -> storage/app/public (gambar unggahan user)
php artisan storage:link --force >/dev/null

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# Isi data awal hanya jika database masih kosong (deploy pertama)
USERS=$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n 1)
if [ "$USERS" = "0" ]; then
    echo "Database kosong: menjalankan seeder data awal..."
    php artisan db:seed --force
fi

chown -R www-data:www-data storage bootstrap/cache

php-fpm -D
echo "WinniCode siap di port 80"
exec nginx -g 'daemon off;'
