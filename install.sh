#!/bin/bash

echo "Getting Blackprint from GitHub"
echo ""
exec (git clone git://github.com/tmaiaroto/blackprint.git . 2>&1);
exec setup.sh
