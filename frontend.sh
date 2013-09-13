#!/bin/bash

echo "Blackprint Front-end Assets Installer"
echo "---------------------------------------"
echo ""

(cd webroot && bower install bootstrap)
(cd webroot && bower install bootstrap-wysihtml5)
(cd webroot && bower install font-awesome)