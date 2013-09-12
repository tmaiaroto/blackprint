#!/bin/bash

exec git clone git://github.com/tmaiaroto/blackprint.git .
clear;

echo "Blackprint Installer"
echo "----------------------"
echo ""
echo "Setting application cache directories and permissions..."
mkdir -R resources/tmp/cache/templates
mkdir -R resources/g11n
chmod -R 777 resources
chmod -R 775 config/bootstrap/libraries
chmod -R 775 config/bootstrap/connections
echo ""

if [ -f "composer.phar" ];
then
	php composer.phar install
else
	echo ""
	echo "Getting Composer for you..."
	curl -sS https://getcomposer.org/installer | php
	php composer.phar install
fi

echo ""
echo "Creating a symlink for li3b_core assets..."
(cd webroot && ln -s ../libraries/li3b_core/webroot li3b_core)
echo ""

echo ""
echo "Creating a symlink to li3 for you..."
chmod +x libraries/unionofrad/lithium/lithium/console/li3
ln -s libraries/unionofrad/lithium/lithium/console/li3 li3
alias li3='./li3'
echo ""

echo ""
echo "Installation complete."
echo ""