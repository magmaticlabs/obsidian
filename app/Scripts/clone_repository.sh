#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
ROOTPATH="$(realpath ${DIR}/../..)"

set -e

GITURL="${1}"
TARGET="${2}"

if [ -z "${GITURL}" ] || [ -z "${TARGET}" ]; then
    echo "Usage: ${0} <giturl> <target>"
    exit 0
fi

export GIT_SSH_COMMAND="ssh -i '${ROOTPATH}/storage/app/obsidian-build.key'"
git clone --depth 1 "${GITURL}" "${TARGET}" --verbose
