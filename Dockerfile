FROM php:8.4-fpm

# System deps
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev nginx \
 && docker-php-ext-install pdo_pgsql zip \
 && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app
COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY ./render/nginx.conf /etc/nginx/conf.d/default.conf

# Permissions (storage + cache)
RUN chown -R www-data:www-data storage bootstrap/cache

COPY ./render/nginx.conf /etc/nginx/conf.d/default.conf
COPY ./render/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 8080

CMD bash -lc "\
  php artisan config:cache && \
  php artisan route:cache || true && \
  php artisan view:cache || true && \
  php artisan migrate --force && \
  php-fpm -D && \
  nginx -g 'daemon off;' \
"
