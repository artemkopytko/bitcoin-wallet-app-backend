# Use the official PHP image as base
FROM php:8.2-fpm AS base

# Set working directory inside the container
WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER 1

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd zip pdo_mysql pcntl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install openswoole \
    && docker-php-ext-enable openswoole

RUN pecl install excimer

# Copy Composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-dev --optimize-autoloader

# Copy the rest of the application code
COPY . /var/www/html/

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN ls /var/www/html

# Generate autoload files and optimize Laravel
RUN composer dump-autoload --optimize

RUN php artisan optimize
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan event:cache

#EXPOSE 9000
#CMD ["php-fpm"]
