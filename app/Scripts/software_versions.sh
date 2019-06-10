#!/bin/bash

php --version | head -n 1
{ hash docker 2> /dev/null && docker --version; } || echo "docker: --"
{ hash docker-compose 2> /dev/null && docker-compose --version; } || echo "docker-compose: --"
{ hash composer 2> /dev/null && composer --version; } || echo "composer: --"
echo -n "node: "; { hash node 2> /dev/null && node --version; } || echo "--"
echo -n "npm: "; { hash npm 2> /dev/null && npm --version; } || echo "--"
