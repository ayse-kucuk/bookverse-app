#!/bin/sh
set -e

php artisan storage:link 2>/dev/null || true
php artisan migrate --force
exec apache2-foreground
