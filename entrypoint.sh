#!/bin/bash

mkdir -p /var/www/html/neos/

if [ `ls -A /var/www/html/neos/ | wc -m` == "0" ]; then
    echo "Moving initial application code."
    mv /var/www/html/neos-orig/* /var/www/html/neos/;
fi

echo "Starting apache"
/usr/sbin/apache2ctl -DFOREGROUND

echo "Executing passed command"
exec "$@"