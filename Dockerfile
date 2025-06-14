FROM php:8.3-fpm-alpine

ARG DB_HOST="localhost"
ARG DB_PORT="3306"
ARG DB_DATABASE="arbolado"
ARG DB_PASSWORD=""
ARG DB_USERNAME="root"

# Update system and install dependencies
RUN apk update && apk add --no-cache \
    git \
    curl \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose port
EXPOSE 8000

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]