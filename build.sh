#!/bin/bash

# Exit on any error
set -e

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing Node dependencies..."
npm install

echo "Building assets..."
npm run build

echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache

echo "Generating APP_KEY..."
php artisan key:generate

echo "Running migrations..."
php artisan migrate --force

echo "Build complete!"
