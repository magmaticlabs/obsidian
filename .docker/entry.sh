#!/usr/bin/env bash
set -e

UID_NGINX=$(id -u www-data)
if [ "${UID}" != "0" ] && [ "${UID}" != "${UID_NGINX}" ]; then
    echo -n "Changing UID/GID for www-data user... "
    usermod  -u $UID www-data
    groupmod -g $GID www-data
    echo "Done!"
fi

NUM_FILES=$(find . -user ${UID} -printf '.' | wc -c)
if [ "${NUM_FILES}" != "0" ]; then
    echo -n "Changing file ownership... "
    chown -R www-data:www-data /var/www/docker
    echo "Done!"
fi

if ! id -u $USER > /dev/null 2>&1; then
    echo -n "Creating user matching host user... "
    useradd -u $UID -g $GID -o -m -r $USER
    echo "Done!"
fi

usermod -d /home/$USER www-data
usermod -s /bin/bash www-data

exec "$@"