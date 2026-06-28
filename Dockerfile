FROM php:8.4-apache

# Installs prebuilt extension binaries instead of compiling from source
# (docker-php-ext-install always compiles, which was taking minutes per
# extension and timing out Coolify's build step).
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql mysqli mbstring gd intl zip

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

# Runtime-writable data that isn't part of the image. uploads/ is just
# temp staging for in-progress uploads (Song::save() moves the finished
# file out and deletes it, so it doesn't need to survive a redeploy) —
# www/assets/sounds/songs is the actual permanent song library, sessions
# and log persist for continuity. Coolify mounts persistent volumes over
# the latter three (see docker-compose.coolify.yml).
RUN mkdir -p uploads sessions log temp www/assets/sounds/songs \
  && chown -R www-data:www-data uploads sessions log temp www/assets/sounds/songs

COPY docker/prod-entrypoint.sh /usr/local/bin/prod-entrypoint.sh
RUN chmod +x /usr/local/bin/prod-entrypoint.sh

ENTRYPOINT ["prod-entrypoint.sh"]
CMD ["apache2-foreground"]
