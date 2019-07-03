FROM php:7-fpm

RUN apt-get update \
  && apt-get install -y --no-install-recommends \
    gnupg \
    ssh \
    curl \
    git \
    unzip \
    jq \
    libzip-dev \
    libjpeg-dev \
    libz-dev \
    libpq-dev \
    libssl-dev \
    libmcrypt-dev \
    libldap2-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql \
  && docker-php-ext-install zip \
  && docker-php-ext-install ldap

RUN apt-get update && curl --silent --show-error https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN rm -rf /var/lib/apt/lists/* \
  && apt-get clean && apt-get update \
  && curl -sL https://deb.nodesource.com/setup_12.x | bash - \
  && apt-get install -y nodejs

WORKDIR /app

RUN groupadd -g 1000 builduser && useradd -m -r -s /bin/bash -u 1000 -g 1000 -o builduser

COPY entry.sh /entry.sh
RUN chmod +x /entry.sh

ENTRYPOINT ["/entry.sh"]
