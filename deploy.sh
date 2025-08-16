#!/bin/bash
# Deployment Script for Hosting

echo "🚀 Preparing Laravel Audit System for Hosting..."

# Clear all caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
echo "Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
echo "Creating storage link..."
php artisan storage:link

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod -R 755 public/admin/assets/

# Build assets if using Vite
if [ -f "package.json" ]; then
    echo "Building assets..."
    npm install
    npm run build
fi

echo "✅ Deployment preparation complete!"
echo ""
echo "📋 Post-deployment checklist:"
echo "1. Update .env file with correct APP_URL"
echo "2. Run migrations: php artisan migrate --force"
echo "3. Verify file permissions on hosting server"
echo "4. Check .htaccess file in public folder"
echo "5. Test asset loading: CSS, JS, images"
