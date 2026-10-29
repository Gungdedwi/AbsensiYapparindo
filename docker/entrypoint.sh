#!/bin/sh
set -e

# Replace PORT_PLACEHOLDER in nginx.conf with actual PORT from Render (defaults to 10000)
PORT=${PORT:-10000}
sed -i "s/PORT_PLACEHOLDER/$PORT/g" /etc/nginx/http.d/default.conf

# Ensure storage and bootstrap/cache permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Note: As per AGENTS.md and RENDER_DEPLOYMENT.md, database migrations are NEVER run automatically here.

exec /usr/bin/supervisord -c /etc/supervisord.conf

