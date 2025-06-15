FROM php:8.3.16-apache
RUN apt update
RUN apt update && apt upgrade -y
RUN apt update && \
    apt install -y git pkg-config

RUN apt-get -y --no-install-recommends install cmake make git gcc g++ libbz2-dev libstxxl-dev libstxxl1v5 libxml2-dev \
    libzip-dev unzip libboost-all-dev lua5.2 liblua5.2-dev libtbb-dev libc-client-dev libkrb5-dev \
    -o APT::Install-Suggests=0 -o APT::Install-Recommends=0

RUN apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libxml2-dev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install gd
RUN apt-get install -y zlib1g-dev libicu-dev g++
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl
RUN a2enmod rewrite
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/ --filename=composer

RUN apt-get -y install libxslt-dev
RUN echo "post_max_size=20M" >> /usr/local/etc/php/php.ini
RUN echo "max_execution_time=6000s" >> /usr/local/etc/php/php.ini
RUN echo "upload_max_filesize=20M" >> /usr/local/etc/php/php.ini
RUN cd /usr/local/etc/php/conf.d/ && \
echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini
RUN docker-php-ext-install bcmath
WORKDIR /var/www/html

COPY . /var/www/html
COPY thecnology/QrCodeGenerator.php /var/www/html/QrCodeGenerator.php
COPY thecnology/PaymentRequestFactory.php /var/www/html/PaymentRequestFactory.php
COPY thecnology/PaymentRequest.php /var/www/html/PaymentRequest.php

RUN composer install --ignore-platform-req=ext-http

