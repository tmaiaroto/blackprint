<?php
use lithium\net\http\Router;
use lithium\core\Environment;
use lithium\action\Dispatcher;

// Convenient short routes for users.
Router::connect("/login", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'login'));
Router::connect("/logout", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'logout'));
Router::connect("/register", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register'));
Router::connect("/settings", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));

// Now, /admin will simply go to the user's personal dashboard instead of a static page. These dashboards can be customized.
Router::connect("/admin", array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'dashboard'));

// Convenient short routes for the DocumentationController (so the URL doesn't need to include "view" for the action).
Router::connect("/documentation", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));
Router::connect("/admin/documentation/{:args}", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));

// Redefine these routes from li3b_core.
// NOTE: We still will use the non admin routes from li3b_core, they're ok. We just want all admin routes to use the blackprint library instead of li3b_core (by default).
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}/sort-{:sort}/{:args}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/sort-{:sort}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/{:args}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/admin/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}', array('admin' => true), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect("/admin/plugin/{:library}/{:controller}/{:action}/{:args}", array('admin' => true, 'action' => 'index', 'args' => array()), array('persist' => array(
	'controller', 'admin', 'library'
)));

Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}/sort-{:sort}/{:args}', array('library' => 'blackprint', 'admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}', array('library' => 'blackprint', 'admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/sort-{:sort}', array('library' => 'blackprint', 'admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}/{:args}', array('library' => 'blackprint', 'admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect('/admin/{:controller}/{:action}/page-{:page:[0-9]+}', array('library' => 'blackprint', 'admin' => true), array('persist' => array(
	'controller', 'admin'
)));
Router::connect("/admin/{:controller}/{:action}/{:args}", array('library' => 'blackprint', 'admin' => true, 'action' => 'index', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));

?>