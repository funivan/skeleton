#!/bin/sh

# Increase maximum opened files (required by box.phar):
ulimit -Sn 2048

# Download composer if it does not exist:
command -v composer || {
  wget -O - https://getcomposer.org/installer | php -d detect_unicode=0
  mv composer.phar composer
}

# Download box if it does not exist:
command -v box.phar || {
  wget -O - http://box-project.org/installer.php | php -d detect_unicode=0
}

box="$(command -v box.phar)"

## Build mcc phar:
composer install || exit 1
php -dphar.readonly=0 box.phar build || exit 1
echo "phar has been created."
