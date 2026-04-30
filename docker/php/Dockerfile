FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libzip-dev libonig-dev

RUN docker-php-ext-install \
    pdo pdo_mysql mbstring zip

# 安装 composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www