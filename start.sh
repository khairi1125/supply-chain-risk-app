#!/bin/bash

echo "Running migrations..."
php artisan migrate --force
echo "Migration done (or tables already exist, continuing...)"

echo "Starting scheduler in background..."
php artisan schedule:work &

echo "Starting web server..."
php -S 0.0.0.0:$PORT -t public
