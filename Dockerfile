FROM php:7.2-fpm-alpine
Maintainer Michalski Luc <michalski.luc@gmail.com>

RUN apk --no-cache add \
    bash \
    git \
    ca-certificates \
    mariadb-client \
    php7 \
    php7-fpm \
    php7-gd \
    php7-intl \
    php7-opcache \
    php7-pdo \
    php7-pdo_mysql \
    php7-mbstring \
    php7-mysqli \
    php7-curl \
    php7-dom \
    php7-fileinfo \
    php7-zip \
    php7-simplexml \
    php7-json \
    php7-session \
    php7-tokenizer \
    php7-pdo \
    php7-pdo_mysql \
    php7-iconv \
    php7-posix \
    php7-pecl-mcrypt \
    php7-pecl-imagick \
    icu

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

WORKDIR /opt/app

RUN git clone --depth=1 https://github.com/sas-adilis/ps_console.git console && \ 
    cd console && \
    composer install

VOLUME ["/opt/app/console", "/opt/app"] 

WORKDIR /opt/app/console
ENTRYPOINT ["/usr/bin/php", "psc.php"]
CMD ["module:list"]
