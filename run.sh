#!/bin/bash 

docker rm -f neos-test;
docker rm -f neos-db;

docker run -itd \
    -p 3306:3306 \
    -e MYSQL_ROOT_PASSWORD=my-secret-pw \
    -v $(pwd)/docker/data/mysql:/var/lib/mysql \
    --name neos-db \
    mysql:5.7;

docker run -itd \
    -p 80:80 \
    -e DOCUMENT_ROOT=/var/www/html/neos/Web \
    -v $(pwd)/docker/data/app:/var/www/html/neos \
    --name neos-test \
    --link neos-db:neos-db \
    neos-test:latest;

