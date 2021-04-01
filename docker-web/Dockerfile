FROM php:7.3-apache

RUN a2enmod rewrite

ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli.so
RUN a2enmod rewrite
RUN a2enmod ssl
RUN rm -f /etc/apache2/sites-available/default-ssl.conf

RUN apt-get update
RUN apt-get install -y git
RUN apt install zip unzip

COPY php-extra.ini /usr/local/etc/php/conf.d
COPY composer.json /var/www/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN cd /var/www/ && composer update --no-dev

COPY start.sh /root/
RUN chmod a+x /root/start.sh


EXPOSE 80
