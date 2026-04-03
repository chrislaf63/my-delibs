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

echo "Découverte des packages Laravel..."
php artisan package:discover --ansi

echo "Création du lien symbolique storage..."
php artisan storage:link || true

echo "⏳ Attente de la base de données..."
until php artisan db:monitor --databases=mysql > /dev/null 2>&1; do
    echo "Base de données non disponible, nouvelle tentative dans 3s..."
    sleep 3
done
echo "Base de données disponible !"

echo "Exécution des migrations..."
php artisan migrate --force

echo "Exécution des seeders..."
php artisan db:seed --force || true

echo "Optimisation pour la production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Démarrage de PHP-FPM..."
exec php-fpm
