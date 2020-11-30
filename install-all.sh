#!/bin/sh

# Install each child directory as a wp-cli package.
for d in */ ; do
    echo "$d"
    wp package install $d
done