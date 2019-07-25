#!/bin/bash

set -e

SLUG=${1}

echo -n "Creating file checksum manifest... "
echo "{" > /archive/checksums.json;
find /staging -type f -print | cut -c 10- | while read -r filename; do
    HASH=$(openssl sha1 "/staging/${filename}" | awk '{print $NF}');
    echo -e "\t\"${filename}\": \"${HASH}\"," >> /archive/checksums.json;
done
truncate -s-2 /archive/checksums.json
echo -e "\n}" >> /archive/checksums.json;
echo "Done!"

echo -n "Extracting package header data... "
_header_file=$(grep -lZsiE "(Plugin|Theme) Name:" ./* | xargs -0 grep -lsi "Version:" | head -n 1)
if [[ -z "${_header_file}" ]]; then
    echo "Unable to locate file containing package header!"
    exit 1
fi
# According to WordPress, the header must be in the first 8192 bytes of the file
echo $(head -c 8192 ${_header_file} | php /scripts/header_extract.php) > /archive/header.json
echo "Done!"

echo -n "Rebasing files under new directory... "
mkdir /staging/${SLUG}
find /staging/* -maxdepth 0 ! -name "${SLUG}" -exec mv {} /staging/${SLUG} \;
echo "Done!"

echo -n "Creating zip archive... "
pushd /staging > /dev/null 2>&1
zip -r /archive/${SLUG}.zip "${SLUG}" > /dev/null 2>&1
popd > /dev/null 2>&1
echo "Done!"


