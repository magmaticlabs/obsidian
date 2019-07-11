#!/bin/bash

SLUG=${1}

/scripts/software_versions.sh

set -e

echo "--------------------------------"

/scripts/composer.sh
/scripts/npm.sh

/scripts/staging.sh
/scripts/archive.sh ${SLUG}
/scripts/clean.sh

echo "--------------------------------"
echo -n "Build Complete: "; date
