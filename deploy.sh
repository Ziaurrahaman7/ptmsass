#!/bin/bash

echo "🚀 Starting deployment..."

# Enable maintenance mode
php artisan down --retry=60 --secret="deployment-secret-key"

echo "📦 Pulling latest changes from git..."
git pull origin main

echo "📚 Installing/Updating dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "🔄 Refreshing caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "📊 Running migrations..."
php artisan migrate --force

echo "🔗 Creating storage link..."
php artisan storage:link

echo "🧹 Clearing all caches..."
php artisan cache:clear
php artisan config:clear

echo "⚡ Optimizing application..."
php artisan optimize

# Disable maintenance mode
php artisan up

echo "✅ Deployment completed successfully!"
