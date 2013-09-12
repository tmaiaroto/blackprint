#!/bin/bash

echo "Getting Blackprint from GitHub"
echo ""
exec git clone git://github.com/tmaiaroto/blackprint.git .; exec setup.sh
