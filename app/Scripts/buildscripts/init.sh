#!/bin/bash

SLUG=${1}

/scripts/software_versions.sh

function _err() {
  echo '[ERROR] An error occurred during the build process!'

  /scripts/clean.sh
  echo -n "Cleaning up archive directory... "
  rm -Rf /archive/*
  echo "Done!"

  echo "--------------------------------"
  echo -n "Build Failed: "; date
  exit 1
}

set -eE
trap _err ERR

echo "--------------------------------"

if [ -f Makefile ]; then
  echo "Found Makefile! Running make..."
  make
else
  /scripts/composer.sh
  /scripts/npm.sh
fi

/scripts/staging.sh
/scripts/archive.sh ${SLUG}
/scripts/clean.sh

echo "--------------------------------"
echo -n "Build Complete: "; date
