#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
ROOTPATH="$(realpath ${DIR}/../..)"

WORKINGDIR=${1}
STAGINGDIR=${2}
ARCHIVEDIR=${3}
if [ -z "${WORKINGDIR}" ] || [ -z "${STAGINGDIR}" ] || [ -z "${ARCHIVEDIR}" ]; then
    echo "Usage: ${0} <working_dir> <staging_dir> <archive_dir>"
    exit 0
fi

cd "${ROOTPATH}/app/Docker"

echo -n "Building docker image... "
docker build --compress -t buildprocessor:latest . > /dev/null
echo "Done!"

GID=$(id -g)
export UID && export GID

docker run \
  -e "UID=${UID}" \
  -e "GID=${GID}" \
  -v "${WORKINGDIR}":/build: \
  -v "${STAGINGDIR}":/staging: \
  -v "${ARCHIVEDIR}":/archive: \
  -v "${ROOTPATH}/app/Scripts/buildscripts":/scripts: \
  -u "builduser" \
  -w /build buildprocessor \
  /scripts/init.sh
