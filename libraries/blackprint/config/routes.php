<?php
use lithium\net\http\Router;
use lithium\core\Environment;
use lithium\action\Dispatcher;

// Set the evironment
if($_SERVER['HTTP_HOST'] == 'blackprint.dev.local' || $_SERVER['HTTP_HOST'] == 'blackprint.local' || $_SERVER['HTTP_HOST'] == 'localhost') {
	Environment::set('development');
}

/**
 * Dispatcher rules to rewrite admin actions.
 */
Dispatcher::config(array(
	'rules' => array(
		'admin' => array('action' => 'admin_{:action}')
	)
));

// Convenient short routes for users.
Router::connect("/login/{:args}", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'login'));
Router::connect("/logout", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'logout'));
Router::connect("/register", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register'));
Router::connect("/my-account", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));

// Now, /admin will simply go to the user's personal dashboard instead of a static page. These dashboards can be customized.
Router::connect("/admin", array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'dashboard'));
Router::connect("/admin/my-account", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));
Router::connect("/admin/login/{:args}", array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'login'));
Router::connect("/admin/config", array('library' => 'blackprint', 'admin' => true, 'controller' => 'config', 'action' => 'update'));

// Convenient short routes for the DocumentationController (so the URL doesn't need to include "view" for the action).
Router::connect("/documentation", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));
Router::connect("/admin/documentation/{:args}", array('library' => 'blackprint', 'admin' => true, 'controller' => 'documentation', 'action' => 'view', 'args' => array()), array('persist' => array(
	'controller', 'admin'
)));

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

/**
 * Naturally, we'd like some "public" non-admin routes to match.
 * This will make it easier for libraries written for Blackprint
 * to take advantage of default routing and in some cases require no additional
 * or even duplicate routes to be written in each library's "routes.php" file.
 *
 * NOTE: This of course does not mean a plugin needs to have a public index action.
 * It also does not cover controllers in the base application. Note the "plugin" prefix.
 */
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}/sort-{:sort}/{:args}', array('action' => 'index'), array('persist' => array(
	'controller', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/limit-{:limit:[0-9]+}', array('action' => 'index'), array('persist' => array(
	'controller', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/sort-{:sort}', array('action' => 'index'), array('persist' => array(
	'controller', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}/{:args}', array('action' => 'index'), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect('/plugin/{:library}/{:controller}/{:action}/page-{:page:[0-9]+}', array('action' => 'index'), array('persist' => array(
	'controller', 'admin', 'library'
)));
Router::connect("/plugin/{:library}/{:controller}/{:action}/{:args}", array('action' => 'index', 'args' => array()), array('persist' => array(
	'controller', 'library'
)));

/**
 * Connect the "public" static pages.
 * NOTE: This is something that might very well be overwritten by the main app's routes.
 *
 * Remember, blackprint static pages can always be used with: /plugin/blackprint/pages/view/home
 * So even if the main application wants to repurpose the "/" URL, it can still use core static pages
 * which can have template overrides in the main app's views directory at: /views/_libraries/blackprint/pages/...
 */
Router::connect("/", array('library' => 'blackprint', 'controller' => 'pages', 'action' => 'view', 'args' => array('home'), 'persist' => false));
Router::connect("/page/{:args}", array('library' => 'blackprint', 'controller' => 'pages', 'action' => 'view', 'args' => array('home'), 'persist' => false));

/**
 * Add the testing routes. These routes are only connected in non-production environments, and allow
 * browser-based access to the test suite for running unit and integration tests for the Lithium
 * core, as well as your own application and any other loaded plugins or frameworks. Browse to
 * [http://path/to/app/test](/test) to run tests.
 */
if (!Environment::is('production')) {
	Router::connect('/test/{:args}', array('controller' => 'lithium\test\Controller'));
	Router::connect('/test', array('controller' => 'lithium\test\Controller'));
}
?>