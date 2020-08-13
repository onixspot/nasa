FROM php:7.4-fpm

ARG user
ARG uid

# Download script to install PHP extensions and dependencies
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions \
 && sync

RUN DEBIAN_FRONTEND=noninteractive apt-get update -q \
 && DEBIAN_FRONTEND=noninteractive apt-get install -qq -y \
      curl \
      git \
      zip \
      unzip \
 && install-php-extensions \
      bcmath \
      bz2 \
      calendar \
      exif \
      gd \
      http \
      intl \
      opcache \
      pdo_mysql \
      zip

RUN useradd -G www-data,root -u ${uid} -d /home/${user} ${user} \
 && mkdir -p /home/${user}/.composer /app \
 && chown -R ${user}:${user} /home/${user}/.composer /app

# Install Composer.
RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin --filename=composer

USER ${user}

WORKDIR /app

