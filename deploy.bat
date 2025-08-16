@echo off
echo 🚀 Preparing Laravel Audit System for Hosting...

REM Clear all caches
echo Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

REM Optimize for production
echo Optimizing for production...
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM Create storage link
echo Creating storage link...
php artisan storage:link

REM Build assets if using Vite
if exist "package.json" (
    echo Building assets...
    npm install
    npm run build
)

echo ✅ Deployment preparation complete!
echo.
echo 📋 Post-deployment checklist:
echo 1. Update .env file with correct APP_URL
echo 2. Run migrations: php artisan migrate --force
echo 3. Verify file permissions on hosting server
echo 4. Check .htaccess file in public folder
echo 5. Test asset loading: CSS, JS, images

pause
