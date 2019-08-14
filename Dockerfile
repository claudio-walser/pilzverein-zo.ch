FROM debian:buster-slim
ARG PHP_VERSION="7.3"

COPY entrypoint.sh /entrypoint.sh

RUN set -x \
    && apt-get update \
    && apt-get -y upgrade \
    && apt-get install -y apache2 curl bash-completion sudo php${PHP_VERSION} php-pdo-mysql nano composer php${PHP_VERSION}-xml php${PHP_VERSION}-mysql php${PHP_VERSION}-gd php${PHP_VERSION}-gmagick;

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

RUN set -x \
    && a2enmod rewrite;

RUN set -x \
    && sudo -u www-data mkdir -p /var/www/html/neos \
	&& sudo -u www-data /usr/bin/composer create-project neos/neos-base-distribution /var/www/html/neos-orig \
	&& chown -R www-data: /var/www;

#COPY php.ini /etc/php/${PHP_VERSION}/apache2/php.ini

EXPOSE 80
EXPOSE 443

ENTRYPOINT ["/entrypoint.sh"]