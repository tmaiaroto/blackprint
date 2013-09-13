<?php
use lithium\net\http\Router;
use lithium\core\Environment;
use lithium\action\Dispatcher;

Router::connect("/documentation", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));
Router::connect("/admin/documentation/{:args}", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));

?>