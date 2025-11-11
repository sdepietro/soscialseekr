#!/bin/bash
set -euo pipefail

# Instalar composer si no está
if ! command -v composer >/dev/null 2>&1; then
  echo "[entrypoint] Composer no encontrado. Instalando..."
  EXPECTED_SIGNATURE="$(php -r 'copy("https://composer.github.io/installer.sig", "php://stdout");')"
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  ACTUAL_SIGNATURE="$(php -r 'echo hash_file("sha384", "composer-setup.php");')"
  if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
    echo "ERROR: firma inválida del instalador de Composer"; rm -f composer-setup.php; exit 1
  fi
  php composer-setup.php --install-dir=/usr/local/bin --filename=composer --2
  rm -f composer-setup.php
fi

# Asegurar directorios Laravel
mkdir -p \
  /var/www/html/storage/app/public \
  /var/www/html/storage/framework/cache \
  /var/www/html/storage/framework/sessions \
  /var/www/html/storage/framework/views \
  /var/www/html/storage/logs \
  /var/www/html/bootstrap/cache

# Permisos
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Instalar dependencias (nota: usar 'composer', no 'php composer')
cd /var/www/html
composer install --no-interaction --prefer-dist --optimize-autoloader || true

# Tareas Laravel (no romper si aún no hay DB, etc.)
php artisan migrate --force || true
if [ ! -L /var/www/html/public/storage ]; then
  php artisan storage:link || true
else
  echo "[entrypoint] storage link ya existe, omitido."
fi

# Iniciar Apache
exec apache2-foreground
