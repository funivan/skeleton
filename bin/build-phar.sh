#!/bin/sh

# Increase maximum opened files (required by box.phar):
ulimit -Sn 2048

test -f composer.json || {
  echo "Run this script from the root of project";
  exit 1;
}

# Download box if it does not exist:
command -v "build/box.phar" || {
  test -f "build/box.phar" || {
    curl -LSs http://box-project.github.io/box2/installer.php | php
    mv box.phar "build/box.phar"
  }
}

## Build mcc phar:
composer install || exit 1
php -dphar.readonly=0 build/box.phar build || exit 1
echo "phar has been created."
