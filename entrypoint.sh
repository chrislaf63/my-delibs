#!/bin/bash
set -e

echo "Création des dossiers storage/ nécessaires..."
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

echo "Création du lien symbolique storage..."
php artisan storage:link || true

echo "Installation des dépendances npm..."
npm install

echo "Compilation des assets..."
npm run build

echo "Exécution des migrations..."
php artisan migrate --force

echo "Exécution des seeders..."
php artisan db:seed --force || true

echo "Mise en cache de la configuration..."
php artisan config:cache || true

echo "Démarrage de PHP-FPM..."
exec php-fpm
