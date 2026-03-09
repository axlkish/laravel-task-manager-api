FROM php:8.3-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    pkg-config \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Configure extensions
RUN docker-php-ext-configure intl
RUN docker-php-ext-configure zip

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    bcmath \
    intl \
    zip \
    pcntl \
    sockets \
    opcache

# Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Permissions
RUN chown -R www-data:www-data /var/www

USER www-data
