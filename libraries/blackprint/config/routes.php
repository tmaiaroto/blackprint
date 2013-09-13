<?php
use lithium\net\http\Router;
use lithium\core\Environment;
use lithium\action\Dispatcher;

Router::connect("/login", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'login'));
Router::connect("/logout", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'logout'));
Router::connect("/register", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register'));

Router::connect("/documentation", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));
Router::connect("/admin/documentation/{:args}", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));

?>