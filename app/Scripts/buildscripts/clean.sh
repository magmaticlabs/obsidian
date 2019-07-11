#!/bin/bash

echo -n "Cleaning up working directory... "
rm -Rf /build/*
echo "Done!"

echo -n "Cleaning up staging directory... "
rm -Rf /staging/*
echo "Done!"
