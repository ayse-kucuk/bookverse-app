FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Build sırasında artisan komutları için geçici key
ENV APP_KEY=base64:dGVtcG9yYXJ5a2V5Zm9yZG9ja2VyYnVpbGRvbmx5MTIzNDU2Nzg=

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
CMD ["/usr/local/bin/entrypoint.sh"]
