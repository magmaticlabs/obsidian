#!/bin/bash

/scripts/software_versions.sh

set -e

echo "--------------------------------"
echo "     Starting Build Process     "
echo "--------------------------------"

/scripts/composer.sh
/scripts/npm.sh

ls -la

/scripts/staging.sh
/scripts/archive.sh
/scripts/clean.sh
