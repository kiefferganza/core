FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libmagickwand-dev \
    libwebp-dev \
    libavif-dev \
    zip \
    unzip \
    git \
    curl \
    supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions for Laravel 12
RUN pecl install imagick redis \
    && docker-php-ext-enable imagick redis \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl \
    && docker-php-ext-configure exif \
    && docker-php-ext-install exif \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif \
    && docker-php-ext-install gd \
    && docker-php-ext-install mbstring \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install ctype \
    && docker-php-ext-install zip \
    && docker-php-ext-install fileinfo \
    && docker-php-ext-install opcache \
    && docker-php-ext-enable intl mbstring fileinfo opcache

# Enable Apache modules for Laravel
RUN a2enmod rewrite headers expires deflate

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# PHP configuration for Laravel 12
RUN echo "upload_max_filesize=128M\n\
post_max_size=128M\n\
max_execution_time=300\n\
memory_limit=512M\n\
max_input_vars=5000\n\
expose_php=Off" > /usr/local/etc/php/conf.d/laravel.ini

# OPcache configuration for production
RUN echo "opcache.enable=1\n\
opcache.memory_consumption=256\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=20000\n\
opcache.revalidate_freq=2\n\
opcache.fast_shutdown=1\n\
opcache.enable_cli=1\n\
opcache.jit_buffer_size=100M\n\
opcache.jit=tracing" > /usr/local/etc/php/conf.d/opcache.ini

# Set the working directory
WORKDIR /var/www/html

# Install Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy the entire application code first
COPY . .

# Install Composer dependencies
# Note: Includes --ignore-platform-reqs for wikimedia/composer-merge-plugin compatibility
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Run Laravel optimization commands
RUN composer dump-autoload --optimize

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/{cache,sessions,views} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Set proper ownership
RUN chown -R www-data:www-data /var/www/html

# Switch to www-data user for security
USER www-data

# Expose port 80
EXPOSE 80

# Health check for Laravel 12 (uses /up endpoint)
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/up || exit 1
