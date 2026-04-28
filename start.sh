#!/bin/bash
set -e

echo "==> Fixing Apache MPM conflict..."
rm -f /etc/apache2/mods-enabled/mpm_event.conf \
       /etc/apache2/mods-enabled/mpm_event.load \
       /etc/apache2/mods-enabled/mpm_worker.conf \
       /etc/apache2/mods-enabled/mpm_worker.load
a2enmod mpm_prefork 2>/dev/null || true
echo "==> MPM prefork enabled."

echo "==> Updating Apache port to ${PORT:-80}..."
sed -i "s/Listen 80/Listen ${PORT:-80}/g" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT:-80}>/g" /etc/apache2/sites-available/000-default.conf

echo "==> Running database migrations..."
php artisan migrate --force --no-interaction

echo "==> Starting Apache..."
exec apache2-foreground
