#!/bin/bash 

docker rm -f demo-render
docker run --name demo-render -v $(pwd)/render:/usr/share/nginx/html:ro -p 8090:80 -d nginx

