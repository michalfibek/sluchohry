FROM php:8.4-apache

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
      libpng-dev libjpeg62-turbo-dev libfreetype6-dev libicu-dev libzip-dev libonig-dev unzip git; \
    rm -rf /var/lib/apt/lists/*; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install pdo_mysql mysqli mbstring gd intl zip

RUN { \
      echo 'memory_limit = 256M'; \
      echo 'upload_max_filesize = 20M'; \
      echo 'post_max_size = 20M'; \
    } > /usr/local/etc/php/conf.d/zz-sluchohry.ini

# App's docroot is www/, not the repo root
RUN sed -ri -e 's!/var/www/html!/var/www/html/www!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
  && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
  && a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader \
  && rm -rf /root/.composer

# Runtime-writable data that isn't part of the image: uploads, sessions, logs.
# Coolify mounts persistent volumes over these (see docker-compose.coolify.yml).
RUN mkdir -p uploads sessions log temp \
  && chown -R www-data:www-data uploads sessions log temp

COPY docker/prod-entrypoint.sh /usr/local/bin/prod-entrypoint.sh
RUN chmod +x /usr/local/bin/prod-entrypoint.sh

ENTRYPOINT ["prod-entrypoint.sh"]
CMD ["apache2-foreground"]
