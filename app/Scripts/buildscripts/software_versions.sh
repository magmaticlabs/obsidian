#!/bin/bash

echo "Available build tools:"
{ hash php 2>/dev/null && php --version | head -n 1; } || echo "PHP: Unavailable"
{ hash composer 2>/dev/null && composer --allow-root --version; } || echo "Composer: Unavailable"
{ hash nodejs 2>/dev/null && echo "NodeJS $(nodejs --version)"; } || echo "NodeJS: Unavailable"
{ hash npm 2>/dev/null && echo "NPM $(npm --version)"; } || echo "NPM: Unavailable"
