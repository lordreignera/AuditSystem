# Stage 1: Composer dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-progress --prefer-dist
COPY . .
RUN composer dump-autoload --optimize
# Stage 2: PHP + Nginx + Supervisor
FROM php:8.2-fpm-alpine
# Install dependencies
RUN apk add --no-cache \
    bash \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    icu-dev \
    libzip-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip
WORKDIR /var/www/html
# Copy application
COPY --from=vendor /app ./
# ...existing code...

# After copying application files and before setting permissions, add:
RUN php artisan storage:link || true

# ...existing code...
# Copy configs
COPY nginx.conf /etc/nginx/nginx.conf
COPY ./docker/supervisord.conf /etc/supervisord.conf
# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
# :white_check_mark: Laravel environment variables
ENV APP_NAME="Laravel" \
    APP_ENV="local" \
    APP_KEY="base64:JAOUBPOWSvzKKn1d0GY19toPiG46o7wmxXaBB7lae70=" \
    APP_DEBUG="true" \
    APP_URL="http://localhost:83" \
    LOG_CHANNEL="stack" \
    LOG_DEPRECATIONS_CHANNEL="null" \
    LOG_LEVEL="debug" \
    DB_CONNECTION="mysql" \
    DB_HOST="127.0.0.1" \
    DB_PORT="3306" \
    DB_DATABASE="auditsystem" \
    DB_USERNAME="root" \
    DB_PASSWORD="" \
    BROADCAST_DRIVER="log" \
    CACHE_DRIVER="file" \
    FILESYSTEM_DISK="local" \
    QUEUE_CONNECTION="sync" \
    SESSION_DRIVER="database" \
    SESSION_LIFETIME="120" \
    MEMCACHED_HOST="127.0.0.1" \
    REDIS_HOST="127.0.0.1" \
    REDIS_PASSWORD="null" \
    REDIS_PORT="6379" \
    MAIL_MAILER="smtp" \
    MAIL_HOST="mailpit" \
    MAIL_PORT="1025" \
    MAIL_USERNAME="null" \
    MAIL_PASSWORD="null" \
    MAIL_ENCRYPTION="null" \
    MAIL_FROM_ADDRESS="hello@example.com" \
    MAIL_FROM_NAME="Laravel" \
    AWS_ACCESS_KEY_ID="" \
    AWS_SECRET_ACCESS_KEY="" \
    AWS_DEFAULT_REGION="us-east-1" \
    AWS_BUCKET="" \
    AWS_USE_PATH_STYLE_ENDPOINT="false" \
    PUSHER_APP_ID="" \
    PUSHER_APP_KEY="" \
    PUSHER_APP_SECRET="" \
    PUSHER_HOST="" \
    PUSHER_PORT="443" \
    PUSHER_SCHEME="https" \
    PUSHER_APP_CLUSTER="mt1" \
    VITE_APP_NAME="Laravel" \
    VITE_PUSHER_APP_KEY="" \
    VITE_PUSHER_HOST="" \
    VITE_PUSHER_PORT="443" \
    VITE_PUSHER_SCHEME="https" \
    VITE_PUSHER_APP_CLUSTER="mt1" \
    DEEPSEEK_API_KEY="sk-3dae6cab95dd45b0bf681ec87d1c28ea" \
    DEEPSEEK_BASE_URL="https://api.deepseek.com/v1" \
    DEEPSEEK_MODEL="deepseek-chat" \
    DEEPSEEK_VERIFY_SSL="false"
EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

