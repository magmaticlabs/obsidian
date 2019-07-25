#!/bin/bash

set -e

if [ -f package.json ]; then
  echo "Running npm install..."
  npm install --quiet --progress=false > /dev/null

echo "Running npm production scripts..."
  npm run production
else
  echo "Skipping npm..."
fi
