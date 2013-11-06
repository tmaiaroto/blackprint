<?php
use blackprint\models\Asset;
use blackprint\extensions\Thumbnail;

use lithium\net\http\Router;
use lithium\core\Environment;
use lithium\action\Dispatcher;
use \lithium\action\Response;

// Set the evironment
if($_SERVER['HTTP_HOST'] == 'blackprint.dev.local' || $_SERVER['HTTP_HOST'] == 'blackprint.local' || $_SERVER['HTTP_HOST'] == 'localhost') {
	Environment::set('development');
}

/**
 * Asset routes
 *
 * A file can be loaded from GridFS with the following URLs:
 * /asset/527155186432660d1783cf9f.xxx <-- the MongoId with extension (extension could actually be anything)
 * /asset/Thomass-MacBook-Pro.local.52715518c8b39.jpg <-- the actual filename in the document (extension must match)
 *
 * The {:ext} is actually not currently used. However it is captured in the request for use if needed.
 *
 * A file can be loaded without an extension in most web browsers, but it would be a good idea to use the proper extension,
 * especially if visitors need to download the files.
*/
Router::connect('/asset/{:args}.{:ext}', array(), function($request) {
	$document = false;
	if(isset($request->params['args'][0])) {
		if(preg_match('/^[0-9a-fA-F]{24}$/i', $request->params['args'][0])) {
			$document = Asset::find('first', array('conditions' => array('_id' => $request->params['args'][0]), 'fields' => array('contentType', 'file', 'length', 'fileExt', 'uploadDate')));	
		} else {
			$filename = $request->params['args'][0] . '.' . $request->params['ext'];
			$document = Asset::find('first', array('conditions' => array('filename' => $filename, 'fileExt' => $request->params['ext']), 'fields' => array('contentType', 'file', 'length', 'fileExt', 'uploadDate')));
		}
	}
	
	if(!$document || !$document->file){
		header("Status: 404 Not Found");
		header("HTTP/1.0 404 Not Found");
		die;
	}

	return new Response(array(
		'headers' => array('Content-type' => $document->contentType),
		'body' => $document->file->getBytes()
	));
});

// Allow thumbnails to be generated (just for images of course).
Router::connect('/thumbnail/{:width:[0-9]+}/{:height:[0-9]+}/{:args}.{:ext}', array(), function($request) {
	$document = false;
	if(isset($request->params['args'][0])) {
		if(preg_match('/^[0-9a-fA-F]{24}$/i', $request->params['args'][0])) {
			$document = Asset::find('first', array('conditions' => array('_id' => $request->params['args'][0]), 'fields' => array('contentType', 'filename', 'originalFilename', 'file', 'length', 'fileExt', 'uploadDate')));	
		} else {
			$filename = $request->params['args'][0] . '.' . $request->params['ext'];
			$document = Asset::find('first', array('conditions' => array('filename' => $filename, 'fileExt' => $request->params['ext']), 'fields' => array('contentType', 'filename', 'originalFilename', 'file', 'length', 'fileExt', 'uploadDate')));
		}
	}

	$width = isset($request->params['width']) ? (int)$request->params['width']:100;
	$height = isset($request->params['height']) ? (int)$request->params['height']:100;
	$requestedExtension = isset($request->params['ext']) ? $request->params['ext']:null;
	$resizeableExtensions = array('jpg', 'jpeg', 'gif', 'png');

	if(!$document || !$document->file || !in_array($requestedExtension, $resizeableExtensions)) {
		header("Status: 404 Not Found");
		header("HTTP/1.0 404 Not Found");
		die;
	}

	$options = array(
		'size' => array($width, $height),
		'ext' => $document->fileExt
	);

	$options['letterbox'] = isset($request->query['letterbox']) ? $request->query['letterbox']:null;
	$options['forceLetterboxColor'] = isset($request->query['forceLetterboxColor']) ? (bool)$request->query['forceLetterboxColor']:false;
	$options['crop'] = isset($request->query['crop']) ? (bool)$request->query['crop']:false;
	$options['sharpen'] = isset($request->query['sharpen']) ? (bool)$request->query['sharpen']:false;
	$options['quality'] = isset($request->query['quality']) ? (int)$request->query['quality']:85;

	// EXAMPLE REMOTE IMAGE with local disk cache and database cache.
	//$document = 'http://oddanimals.com/images/lime-cat.jpg';
	//$file = Thumbnail::create($document, LITHIUM_APP_PATH . '/webroot/img/_thumbnails', $options);
	//$file = Thumbnail::create($document, 'grid.fs', $options);

	// EXAMPLE IMAGE FROM DISK with local disk cache and database cache.
	//$file = Thumbnail::create(LITHIUM_APP_PATH . '/webroot/img/glyphicons-halflings.png', LITHIUM_APP_PATH . '/webroot/img/_thumbnails', $options);
	//$file = Thumbnail::create(LITHIUM_APP_PATH . '/webroot/img/glyphicons-halflings.png', 'grid.fs', $options);

	// EXAMPLE IMAGE FROM MONGODB with local disk cache and database cache.
	//$file = Thumbnail::create($document->file, LITHIUM_APP_PATH . '/webroot/img/_thumbnails', $options);

	$file = Thumbnail::create($document->file, 'grid.fs', $options);
	// The path will be a path on disk for a route if the destination was a cache in MonoDB.
	// Handle both.
	if(file_exists($file['path'])) {
		return new Response(array(
			'headers' => array('Content-type' => $file['mimeType']),
			'body' => file_get_contents($file['path'])
		));
	}

	// Technically, a redirect.
	return new Response(array(
		'location' => $file['path']
	));
});

Router::connect('/admin/clear-thumbnail-cache', array('library' => 'blackprint', 'controller' => 'assets', 'action' => 'clear_thumbnail_cache', 'admin' => true));

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
Router::connect("/forgot-password/{:args}", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'reset_password'));
Router::connect("/my-account", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));
Router::connect("/set-profile-picture-from-url.json", array('library' => 'blackprint', 'controller' => 'users', 'action' => 'set_profile_picture_from_url', 'type' => 'json'));

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

/**
 * Default routes for dynamic pages/content.
*/
Router::connect("/admin/content/{:action}/{:args}", array('library' => 'blackprint', 'controller' => 'content', 'action' => 'index', 'args' => array(), 'admin' => true));
Router::connect("/content/{:args}", array('library' => 'blackprint', 'controller' => 'content', 'action' => 'read', 'args' => array(), 'persist' => false));
Router::connect("/{:contentType}/content/{:args}", array('library' => 'blackprint', 'controller' => 'content', 'action' => 'read', 'args' => array(), 'persist' => false));


// Plugin Routes (for add-ons without their own custom routes)
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