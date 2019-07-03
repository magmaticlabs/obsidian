#!/bin/bash

set -e

echo -n "Creating file checksums... "
echo "{" > /archive/checksums.json;
find /staging -type f -print | cut -c 10- | while read -r filename; do
    HASH=$(openssl sha1 "/staging/${filename}" | awk '{print $NF}');
    echo -e "\t\"${filename}\": \"${HASH}\"," >> /archive/checksums.json;
done
truncate -s-2 /archive/checksums.json
echo -e "\n}" >> /archive/checksums.json;
echo "Done!"


