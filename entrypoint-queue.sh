#!/bin/bash

set -e

echo "Création des dossiers storage/..."
mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    bootstrap/cache

echo "Correction des permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Installation des dépendances Composer..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

echo "Découverte des packages Laravel..."
php artisan package:discover --ansi

echo "Démarrage du worker..."
exec php artisan queue:work --sleep=3 --tries=3
