# Blackprint CMS

#### Installation
You could clone this repository and then run ```install.sh``` to get going...Or you could simply run    
```bash <(curl -s https://raw.github.com/tmaiaroto/blackprint/master/clone.sh) && bash install.sh```

Alternatively, you could clone the repository, setup Composer yourself and then run ```composer install``` 
(or run composer however you have it setup). Then you'd need to ```chmod 777``` the ```resources``` directory recursively. 
Then you'd need to setup symlinks in ```webroot``` for the ```li3b_core``` library.

Note that everything in Blackprint works as a library and so everything you add to it will be located under the ```libraries``` 
directory and if you have assets (CSS, JavaScript, images, etc.) you wish to expose to the webroot, you will need to symlink 
them as you go along.

