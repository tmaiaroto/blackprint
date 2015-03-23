#!/bin/bash

echo "Blackprint Installer"
echo "----------------------"
echo ""
echo "Setting application cache directories and permissions..."
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
echo "Creating a few symlinks..."
(cd webroot && ln -s ../libraries/li3b_core/webroot li3b_core)
(cd webroot && ln -s ../libraries/blackprint/webroot blackprint)
echo ""

echo ""
echo "Creating a symlink to li3 for you..."
chmod +x libraries/unionofrad/lithium/lithium/console/li3
ln -s libraries/unionofrad/lithium/lithium/console/li3 li3
alias li3='./li3'
echo ""

echo ""
echo "Installation complete."
echo "------------------------"
echo ""
echo "Attempting to install front-end dependencies..."
echo ""
(cd webroot && bower install)