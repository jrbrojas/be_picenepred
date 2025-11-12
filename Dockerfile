FROM php:8.2-fpm

# Instalamos dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    libxml2-dev \
    zlib1g-dev \
    libonig-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype=/usr/include/ \
        --with-jpeg=/usr/include/ \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip xml gd \
    && docker-php-ext-enable sodium \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalamos Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Establecemos directorio de trabajo
WORKDIR /var/www/html

# Copiamos el proyecto Laravel
COPY . /var/www/html

# Ajustamos permisos
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exponemos el puerto 9000 para PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]

