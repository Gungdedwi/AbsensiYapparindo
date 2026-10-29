# Stage 1: Build frontend assets
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci --no-audit
COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# Stage 2: PHP Application Runtime (Pinned to verified security patch release)
FROM php:8.3.36-fpm-alpine3.24

# Install system dependencies & PostgreSQL driver
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev

# Install PHP extensions required for Laravel & PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql mbstring pcntl bcmath zip opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application source code (excluding files in .dockerignore)
COPY . /var/www/html

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build /var/www/html/public/build

# Install production PHP dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Setup Nginx and Supervisor configs
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set directory permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Listen on 0.0.0.0
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

