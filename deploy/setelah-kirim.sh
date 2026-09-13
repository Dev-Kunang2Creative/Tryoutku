#!/usr/bin/env bash
#
# Dijalankan lewat SSH di hosting bersama Hostinger setelah berkas selesai
# disalin. Tugasnya hanya menyegarkan keadaan aplikasi; pemasangan dependensi
# dan pembangunan aset sudah selesai di GitHub Actions.

set -euo pipefail

# Hostinger memakai CloudLinux, yang menaruh tiap versi PHP di jalurnya
# sendiri. Urutan pencarian di bawah memastikan yang terpakai PHP 8.3,
# bukan versi bawaan shell yang bisa saja lebih tua.
for kandidat in \
    "${PHP_BIN:-}" \
    /opt/alt/php83/usr/bin/php \
    /usr/bin/php8.3 \
    "$(command -v php || true)"
do
    if [ -n "$kandidat" ] && [ -x "$kandidat" ]; then
        PHP="$kandidat"
        break
    fi
done

if [ -z "${PHP:-}" ]; then
    echo "GAGAL: tidak menemukan PHP yang bisa dijalankan." >&2
    exit 1
fi

echo "PHP yang dipakai: $("$PHP" -v | head -1)"

if [ ! -f .env ]; then
    echo "GAGAL: berkas .env tidak ada di server." >&2
    echo "Salin .env.production.example menjadi .env, isi kredensialnya," >&2
    echo "lalu jalankan php artisan key:generate." >&2
    exit 1
fi

# Folder kerja ini sengaja tidak ikut dikirim agar berkas unggahan dan catatan
# log di server tidak pernah tersentuh, jadi keberadaannya dipastikan di sini.
mkdir -p storage/app/public \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chmod -R ug+rwx storage bootstrap/cache

# PHP di hosting ini memuat symlink() dalam disable_functions, sehingga
# `artisan storage:link` selalu gagal. Tautannya dibuat langsung oleh shell,
# yang tidak terkena pembatasan itu. Dibuat ulang setiap deploy karena
# rsync --delete menghapusnya.
ln -sfn ../storage/app/public public/storage

# Akar dokumen milik Hostinger adalah public_html, sedangkan Laravel harus
# melayani dari folder public. Tautan ini menyambungkan keduanya, dan hanya
# perlu dibuat sekali.
AKAR_DOMAIN="$(dirname "$PWD")"
if [ ! -L "$AKAR_DOMAIN/public_html" ]; then
    rm -rf "$AKAR_DOMAIN/public_html"
    ln -s "$PWD/public" "$AKAR_DOMAIN/public_html"
    echo "public_html disambungkan ke $PWD/public"
fi

# Berhentikan permintaan masuk selama basis data berubah, lalu pastikan
# aplikasi selalu keluar dari mode perawatan walau ada langkah yang gagal.
"$PHP" artisan down --render="errors::503" --retry=15 || true
trap '"$PHP" artisan up || true' EXIT

"$PHP" artisan migrate --force

"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

echo "Selesai."
