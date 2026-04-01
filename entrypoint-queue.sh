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

echo "Découverte des packages Laravel..."
php artisan package:discover --ansi

echo "Attente que les migrations soient prêtes..."
php artisan migrate:status --no-interaction || sleep 5

echo "Démarrage du worker..."
exec php artisan queue:work --sleep=3 --tries=3
