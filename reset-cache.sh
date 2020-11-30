#!/bin/sh

#! This script will nuke the composer cache used by wp-cli.

cd ~/.wp-cli/packages

# Delete cached loaders.
rm -r vendor

# Run the internal dependency updater to regenerate all links.
wp --allow-root package update
