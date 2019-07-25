#!/bin/bash

set -e

if [ -f composer.json ]; then
  echo "Running composer install..."
  composer install --no-ansi --no-interaction --no-progress --optimize-autoloader --quiet --prefer-dist --no-dev --no-suggest
else
  echo "Skipping composer..."
fi
