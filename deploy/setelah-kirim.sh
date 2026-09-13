#!/usr/bin/env bash
#
# Dijalankan di server Hostinger setelah berkas selesai disalin.
# Tugasnya hanya menyegarkan keadaan aplikasi; pembangunan aset dan
# pemasangan dependensi sudah selesai di GitHub Actions.

set -euo pipefail

# Hostinger menyediakan beberapa versi PHP; pastikan yang dipakai 8.3.
PHP="${PHP_BIN:-/usr/bin/php8.3}"

if [ ! -x "$PHP" ]; then
    PHP="$(command -v php)"
fi

echo "PHP yang dipakai: $("$PHP" -v | head -1)"

if [ ! -f .env ]; then
    echo "GAGAL: berkas .env tidak ada di server." >&2
    echo "Salin .env.example menjadi .env, isi kredensialnya, lalu jalankan php artisan key:generate." >&2
    exit 1
fi

# Berhentikan permintaan masuk selama basis data berubah, lalu pastikan
# aplikasi selalu keluar dari mode perawatan walau ada langkah yang gagal.
"$PHP" artisan down --render="errors::503" --retry=15 || true
trap '"$PHP" artisan up || true' EXIT

"$PHP" artisan migrate --force

# Tautan storage dibuat ulang bila hilang, misalnya setelah rsync --delete.
if [ ! -L public/storage ]; then
    "$PHP" artisan storage:link
fi

"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

echo "Selesai."
