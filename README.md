# Blackprint CMS

#### Installation
You could clone this repository and then run ```install.sh``` to get going...Or, if you're on Linux or OS X, you could simply create 
a new directory for your site, go to the root of it (ensure it's empty) and run    
```bash <(curl -s https://raw.github.com/tmaiaroto/blackprint/master/clone.sh) && bash install.sh```

Alternatively, you could clone the repository, setup Composer yourself and then run ```composer install``` 
(or run composer however you have it setup). Then you'd need to ```chmod 777``` the ```resources``` directory recursively. 
Then you'd need to setup symlinks in ```webroot``` for the ```li3b_core``` library.

Note that everything in Blackprint works as a library and so everything you add to it will be located under the ```libraries``` 
directory and if you have assets (CSS, JavaScript, images, etc.) you wish to expose to the webroot, you will need to symlink 
them as you go along.

Note to Windows users: I'm so sorry for you. Things will work for you, but I rely on symlinks so you're going to have to figure out 
how to get around that on your own. See the ```config/media.php``` file and ensure it's included in the ```config/bootstrap.php``` file. 
You're essentially going to be serving assets through PHP instead of directly from the webroot via your web server. Unless you can 
get symlinks of some sort working, then great.
