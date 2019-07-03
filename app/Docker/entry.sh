#!/usr/bin/env bash

set -e

if [ "$(id -u builduser)" != "${UID}" ]; then
  echo -n "Updating build user UID... "
  usermod -u "${UID}" builduser
  echo "Done!"
fi

if [ "$(getent group builduser | awk -F ':' '{print $3}')" != "${GID}" ]; then
  echo -n "Updating build user GID... "
  groupmod -g "${GID}" builduser
  usermod -g "${GID}" builduser
  echo "Done!"
fi

exec "$@"
