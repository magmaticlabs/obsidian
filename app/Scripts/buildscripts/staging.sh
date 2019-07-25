#!/bin/bash

set -e

if [ -f ".pkgmanifest.json" ]; then
    echo -n "Copying data to staging area via package manifest... "
    jq -r '.paths|@tsv' .pkgmanifest.json | tr "\t" "\n" | \
    while read -r filepath; do
        filepath=$(echo "${filepath}" | tr -d "\n");
        find . -maxdepth 1 -name "${filepath}" -exec cp -r {} /staging \;;
    done
    echo "Done!"
else
    echo -n "Copying data to staging area... "
    mv /build/* /staging
    echo "Done!"
fi

ls -la /staging/
