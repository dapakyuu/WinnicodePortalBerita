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

# Database masih kosong: pindahkan data dari SQLite lama bila ada, kalau tidak isi data awal
# (Jika penyalinan gagal, container berhenti dengan error agar data lama tidak tertimpa seeder.)
USERS=$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n 1)
OLD_SQLITE=/var/www/html/storage/database/database.sqlite
OLD_USERS=0
if [ "${DB_CONNECTION:-sqlite}" = "pgsql" ] && [ -s "$OLD_SQLITE" ]; then
    OLD_USERS=$(php -r 'try { echo (new PDO("sqlite:".$argv[1]))->query("SELECT COUNT(*) FROM users")->fetchColumn(); } catch (Throwable $e) { echo 0; }' "$OLD_SQLITE")
fi
if [ -z "$USERS" ]; then
    echo "PERINGATAN: jumlah user tidak bisa dicek, seeder/penyalinan dilewati." >&2
elif [ "$USERS" = "0" ]; then
    if [ "$OLD_USERS" != "0" ]; then
        echo "Database kosong: menyalin data dari SQLite lama ($OLD_USERS user)..."
        php artisan db:salin-dari-sqlite --path="$OLD_SQLITE"
    else
        echo "Database kosong: menjalankan seeder data awal..."
        php artisan db:seed --force
    fi
fi

chown -R www-data:www-data storage bootstrap/cache

php-fpm -D
echo "WinniCode siap di port 80"
exec nginx -g 'daemon off;'
