#
# Composer Dependencies
#
FROM composer:2 as composer
WORKDIR /usr/local/src/
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-scripts --no-autoloader --prefer-dist --no-dev --no-interaction

#
# BASE
#
FROM php:8.1-fpm as base

# ENV PATH
ENV php_conf /usr/local/etc/php-fpm.conf
ENV fpm_conf /usr/local/etc/php-fpm.d/www.conf
ENV php_vars /usr/local/etc/php/conf.d/docker-vars.ini

RUN apt-get update && apt-get install -yq \
  nginx cron git-core jq \
  supervisor unzip vim zip pkg-config \
  libpq-dev libsqlite3-dev libzip-dev libcurl4-openssl-dev libssl-dev  \
  libjpeg-dev libpng-dev libwebp-dev libjpeg62-turbo-dev libfreetype6-dev \
  && rm -rf /var/lib/apt/lists/* \
  && pecl install redis \
  && docker-php-ext-configure gd --with-jpeg=/usr/include/ --with-freetype=/usr/include/ --with-webp=/usr/include/ \
  && docker-php-ext-enable redis \
  && docker-php-ext-install exif gd mysqli opcache pdo_pgsql pdo_mysql zip pcntl fileinfo gettext iconv


RUN { \
    echo 'opcache.memory_consumption=512'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
  } > /usr/local/etc/php/conf.d/docker-oc-opcache.ini

RUN { \
    echo 'log_errors=on'; \
    echo 'display_errors=off'; \
    echo 'upload_max_filesize=128M'; \
    echo 'post_max_size=128M'; \
    echo 'memory_limit=512M'; \
    echo 'expose_php=Off'; \
    echo 'max_execution_time=300'; \
    echo 'set_time_limit=60'; \
  } > /usr/local/etc/php/conf.d/docker-oc-php.ini

RUN sed -i \
        -e "s/pm.max_children = 5/pm.max_children = 50/g" \
        -e "s/pm.start_servers = 2/pm.start_servers = 5/g" \
        -e "s/pm.min_spare_servers = 1/pm.min_spare_servers = 5/g" \
        -e "s/pm.max_spare_servers = 3/pm.max_spare_servers = 50/g" \
        -e "s/;pm.max_requests = 500/pm.max_requests = 256/g" \
        -e "s/;request_terminate_timeout = 0/request_terminate_timeout = 300/g" \
        ${fpm_conf}

# COPY SERVER CONFIGURATION
COPY ./docker-config/nginx/nginx-site.conf /etc/nginx/sites-enabled/default
COPY ./docker-config/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker-config/supervisord.conf /etc/supervisord.conf
COPY ./docker-config/supervisor.d /etc/supervisor.d
COPY ./docker-config/entrypoint.sh /etc/entrypoint.sh

ENV COMPOSER_ALLOW_SUPERUSER=1

#
# RUNTIME
#
FROM base as app

# COPY SOURCE CODE AND CHANGE PERMISSION TO WWW-DATA
COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY --chown=www-data --from=composer /usr/local/src/vendor ./pos/vendor
COPY --chown=www-data ./ ./

RUN cd pos && composer clearcache && composer dumpautoload

# GIT CONFIG GLOBAL
ARG GIT_USERNAME=${GIT_USERNAME}
ARG GIT_TOKEN=${GIT_TOKEN}
ARG GIT_REPO=${GIT_REPO}
ARG GIT_BRANCH=${GIT_BRANCH}

RUN git config --global --add safe.directory /var/www/html
RUN git remote rm origin
RUN git remote add origin  "https://$GIT_USERNAME:$GIT_TOKEN@github.com/$GIT_REPO"
RUN git config --global url."https://$GIT_USERNAME:$GIT_TOKEN@github.com/$GIT_REPO".insteadOf "ssh://git@github.com"

ENTRYPOINT ["sh", "/etc/entrypoint.sh"]