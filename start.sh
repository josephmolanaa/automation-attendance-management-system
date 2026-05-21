#!/bin/bash
set -e

echo "==> Fixing Apache MPM conflict..."
rm -f /etc/apache2/mods-enabled/mpm_event.conf \
       /etc/apache2/mods-enabled/mpm_event.load \
       /etc/apache2/mods-enabled/mpm_worker.conf \
       /etc/apache2/mods-enabled/mpm_worker.load
a2enmod mpm_prefork 2>/dev/null || true
echo "==> MPM prefork enabled."

APACHE_PORT="${PORT:-80}"
if ! [[ "$APACHE_PORT" =~ ^[0-9]+$ ]] || [ "$APACHE_PORT" -lt 1 ] || [ "$APACHE_PORT" -gt 65535 ]; then
    echo "ERROR: Invalid PORT value: '$APACHE_PORT'"
    exit 1
fi

echo "==> Updating Apache port to ${APACHE_PORT}..."
printf "Listen %s\n" "$APACHE_PORT" > /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost [^>]+>/<VirtualHost *:${APACHE_PORT}>/g" /etc/apache2/sites-available/000-default.conf

echo "==> Running database migrations..."
php artisan migrate --force --no-interaction

echo "==> Running database seeders..."
php artisan db:seed --force --no-interaction

echo "==> Starting Apache..."
exec apache2-foreground
