FROM php:8.1-apache

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod a+x /usr/local/bin/install-php-extensions \ 
 && apt-get update && apt-get install -y tzdata dnsutils \
 && apt-get clean

RUN install-php-extensions mysqli tidy \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
 && echo "post_max_size = 512M" > $PHP_INI_DIR/conf.d/post_max_size.ini \
 && echo "upload_max_filesize = 512M" > $PHP_INI_DIR/conf.d/upload_max_filesize.ini
