#!/bin/bash

# Create the root key
openssl genrsa -out nginx/rootCA.key 2048

# Create the root cert
openssl req -x509 -new -nodes -key nginx/rootCA.key -sha256 -days 1024 -subj "/C=US/ST=NC/O=localhost/OU=localhost/CN=localhost" -out nginx/rootCA.pem

# Create the CSR
openssl req -new -nodes -out nginx/server.csr -newkey rsa:2048 -keyout nginx/server.key -subj "/C=US/ST=NC/O=localhost/OU=localhost/CN=localhost"

# Create the cert
openssl x509 -req -in nginx/server.csr -CA nginx/rootCA.pem -CAkey nginx/rootCA.key -CAcreateserial -out nginx/server.crt -days 500 -sha256 -extfile v3.ext
