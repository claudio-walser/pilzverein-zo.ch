ARG DISTRO="jessie"
ARG TAG="latest"

FROM debian:buster-slim

COPY entrypoint.sh /entrypoint.sh

RUN set -x \
    && apt-get update \
    && apt-get -y upgrade \
    && apt-get install -y apache2 curl bash-completion php7.3 php-pdo-mysql nano composer php7.3-xml php7.3-mysql php7.3-gd php7.3-gmagick;

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN set -x \
    && a2enmod rewrite;

RUN set -x \
	&& composer create-project neos/neos-base-distribution /var/www/html/neos \
	&& chown -R www-data: /var/www/html;

COPY php.ini /etc/php/7.3/apache2/php.ini

EXPOSE 80
EXPOSE 443

ENTRYPOINT ["/entrypoint.sh"]