#!/bin/bash

echo "Blackprint Front-end Assets Installer"
echo "---------------------------------------"
echo ""

(cd webroot && bower install bootstrap)
(cd webroot && bower install wysihtml5)
# (cd webroot && bower install bootstrap-wysihtml5)
(cd webroot && bower install https://github.com/artillery/bootstrap-wysihtml5.git)
(cd webroot && bower install font-awesome)
(cd webroot && bower install holderjs)