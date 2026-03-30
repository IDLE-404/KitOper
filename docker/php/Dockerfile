FROM php:8.2-fpm


RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd


COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www


RUN mkdir -p /var/www/storage /var/www/bootstrap/cache

# PHP-FPM должен слушать на всех интерфейсах, чтобы nginx из другого контейнера мог подключиться
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

COPY docker/php/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
