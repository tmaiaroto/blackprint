<?php
/**
 * The following filter allows any library's templates to be overridden.
 * This allows for greater control over the look of your application when
 * using 3rd party libraries without mucking around in the library code
 * which can, of course, lead to all sort of maintenance issues.
 *
 * Ultimately, we need to allow the following to happen:
 * 1. If there are no override templates, then the layout and view template will be used from the library (default behavior).
 * 2. If there are templates placed in `/views/_libraries/library_name/`, use those.
 * 3. Try the main application, because we don't want to change how Lithium works by default.
 * 4. If Libraries::add() passes a config that specifically says to use Blackprint layouts, do so (forced situation, but there's still a fallback).
 *
 * Also, we need to consider elements and how they may need to work.
 * A similar system is provided for elements as well.
 *
 * Note: That this is all assuming a library is being used.
 * The main application will be completely unaffected.
 * This means that if you wish to use Blackprint layouts, elements, etc.
 * in your main application, you will need to pass a library key of `blackprint`
 * in many cases. For example, elements.
 *
 * Example:
 * $this->_render('element', 'navbar', array('user' => $this->request()->user), array('library' => 'blackprint'));
 *
 * This would render the `nvarbar` element from Blackprint. If the
 * main application also has a `navbar` element, it won't conflict. This means
 * all other libraries wishing to use this element should specify the library.
 * This is how you normally need to do it. Blackprint isn't trying to
 * take over your Lithium application. However, when it can, it will try to
 * be flexible and fall back to a default in case you forget or don't want
 * to specify a library.
 *
 * For further example, libraries by default will render their own
 * templates, layouts, and elements because the request will have the
 * library set and the rendering system will use that by default.
 *
 * -- That's how Lithium works by default. We aren't changing that. --
 *
 * However, since we want to allow libraries to be built for and use
 * Blackprint, we have the fallback to templates under `blackprint`
 * when possible. That means if you somehow installed a library like that
 * without Blackprint, you'd have missing templates.
 *
 * Moral of the story here is that if you are making a library for use
 * with Blackprint, you should let people know...OR you should provide
 * all of the templates you need so that it can render pages on its own.
 * That means copying all of the layouts, CSS, JavaScript, etc.
 * Yea...A lot of duplication. That's why this template system filter
 * exists on the Dispatcher.
 */
use blackprint\models\Config;
use lithium\action\Dispatcher;
use lithium\core\Libraries;

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {

	// Pass along certain configuration data to each Request. Yes, I know this is a second database query...For now. TODO: Make more efficient.
	$blackprintConfig = Config::find('first', array('conditions' => array('name' => 'default')));
	if(!empty($blackprintConfig)) {
		$blackprintConfig = $blackprintConfig->data();
	} else {
		$blackprintConfig = false;
	}

	$params['request']->blackprintConfig = array();
	if($blackprintConfig) {
		// Site title
		if(isset($blackprintConfig['siteName'])) {
			$params['request']->blackprintConfig['siteName'] = $blackprintConfig['siteName'];
		}

		// Meta data
		if(isset($blackprintConfig['meta'])) {
			$params['request']->blackprintConfig['meta'] = $blackprintConfig['meta'];
		}

		// OpenGraph tags
		if(isset($blackprintConfig['og'])) {
			$params['request']->blackprintConfig['og'] = $blackprintConfig['og'];
		}

		// Social apps
		if(isset($blackprintConfig['socialApps'])) {
			$params['request']->blackprintConfig['socialApps'] = $blackprintConfig['socialApps'];
		}

		// Google Analytics
		if(isset($blackprintConfig['googleAnalytics'])) {
			$params['request']->blackprintConfig['googleAnalytics'] = $blackprintConfig['googleAnalytics'];
		}
	}

	//var_dump($params['params']);
	//exit();

	if(isset($params['params']['library'])) {
		// Instead of using LITHIUM_APP_PATH,for future compatibility.
		$defaultAppConfig = Libraries::get(true);
		$appPath = $defaultAppConfig['path'];

		$libConfig = Libraries::get($params['params']['library']);

		/**
		 * LAYOUTS AND TEMPLATES
		 * Note the path ordering for how templates override others.
		 * First, your overrides and then the default render paths for a library.
		 * Second to last, it tries to grab what it can from the main application.
		 * Last (worst case) it tries to use what's in Blackprint.
		 *
		 * The last scenario is rare, if using a "default" layout, for example,
		 * it likely exists in the main application already. If a library is
		 * specifcially designed for Blackprint and wishes to use templates
		 * within the `blackprint` library before looking in the main application,
		 * they should be added with the proper configuration settings.
		 */
		$paths['layout'] = array(
			$appPath . '/views/_libraries/' . $params['params']['library'] . '/layouts/{:layout}.{:type}.php',
			'{:library}/views/layouts/{:layout}.{:type}.php',
			$appPath . '/views/layouts/{:layout}.{:type}.php',
			// Last, look in the blackprint library...
			$appPath . '/libraries/blackprint/views/layouts/{:layout}.{:type}.php'
		);
		$paths['template'] = array(
			$appPath . '/views/_libraries/' . $params['params']['library'] . '/{:controller}/{:template}.{:type}.php',
			'{:library}/views/{:controller}/{:template}.{:type}.php',
			$appPath . '/views/{:controller}/{:template}.{:type}.php',
			// Last ditch effort to find the template...Note: Blackprint takes a back seat to the main app.
			$appPath . '/libraries/blackprint/views/{:controller}/{:layout}.{:type}.php'
		);

		/*
		 * Condition #4 here. This will prefer Blackprint's core layouts.
		 * Libraries added with this configuration option were designed specifically
		 * for use with Blackprint and wish to use it's default design.
		 *
		 * Of course, there is still template fallback support in case the user
		 * has changed up their copy of Blackprint...But the library is
		 * now putting the priority on the Blackprint layouts, unless
		 * overridden by templates in the _libraries directory of the main app.
		 *
		 * There is currently no need to do the same with templates since the
		 * blackprint library has so few view templates...And they don't even make
		 * sense to share for any other purpose whereas layouts are definitely
		 * something another action can take advantage of.
		 */
		if(isset($libConfig['useBlackprintLayout']) && (bool)$libConfig['useBlackprintLayout'] === true) {
			$paths['layout'] = array(
				$appPath . '/views/_libraries/' . $params['params']['library'] . '/layouts/{:layout}.{:type}.php',
				$appPath . '/libraries/blackprint/views/layouts/{:layout}.{:type}.php',
				'{:library}/views/layouts/{:layout}.{:type}.php',
				$appPath . '/views/layouts/{:layout}.{:type}.php'
			);
		}

		/**
		 * ELEMENTS
		 * This will allow the main application to still render it's elements
		 * even though the View() class may be dealing with one of this library's
		 * controllers, which would normally suggest the element comes from the library
		 * Again, note the ordering here for how things override others.
		 * 1. Your overrides are considered first.
		 * 2. Elements that may come with the library are used when a library key is used.
		 * 3. The main application is checked for the element templates (this functions as normal out of the box Lithium).
		 * 4. Blackprint elements. Last ditch effort to find the element.
		 *    Note: When you wish to use an element from Blackprint, you should
		 *    pass a library key to be certain it is used. Otherwise, if you have an
		 *    element in your main application by the same name as one from Blackprint,
		 *	  you could be using that instead when you did not intend to.
		 *    All of the elements rendered from blackprint pass a library key and
		 *    your plugins, wishing to use core blackprint elements, should do the same.
		 */
		$paths['element'] = array(
			$appPath . '/views/_libraries/' . $params['params']['library'] . '/elements/{:template}.{:type}.php',
			'{:library}/views/elements/{:template}.{:type}.php',
			$appPath . '/views/elements/{:template}.{:type}.php',
			$appPath . '/libraries/blackprint/views/elements/{:template}.{:type}.php'
		);

		/**
		 * THEMES
		 *
		 * The configuration can also hold information about the template theme to use.
		 * Themes are installed using Bower. It's very easy to manage front-end dependencies that way.
		 * Every theme looking to use jQuery, for example, won't need its own copy.
		 * It also provides the theme author with a versioning system and it keeps everything organized.
		 * Plus, Blackprint is already using Bower so it's a very natural fit.
		*/
		// $blackprintConfig['theme'] = array('directory' => 'test');
		if($blackprintConfig) {
			if(isset($blackprintConfig['theme']) && isset($blackprintConfig['theme']['directory'])) {
				$themePath = $appPath . '/webroot/bower_components' . '/' . $blackprintConfig['theme']['directory'];
				if(file_exists($themePath)) {
					array_unshift($paths['layout'], 
						$themePath . '/views/_libraries/' . $params['params']['library'] . '/layouts/{:layout}.{:type}.php',
						$themePath . '/views/layouts/{:layout}.{:type}.php'
					);
					array_unshift($paths['template'], 
						$themePath . '/views/_libraries/' . $params['params']['library'] . '/{:controller}/{:template}.{:type}.php',
						$themePath . '/views/{:controller}/{:template}.{:type}.php'
					);
					array_unshift($paths['element'], 
						$themePath . '/views/_libraries/' . $params['params']['library'] . '/elements/{:template}.{:type}.php',
						$themePath . '/views/elements/{:template}.{:type}.php'
					);
				}
			}
		}

		$params['options']['render']['paths'] = $paths;

	}

	/**
	 * Allow the main application to use Blackprint's admin layout template and elements.
	 * This helps to speed up development without the need to always create libraries for everything.
	 */
	if(isset($params['params']['admin']) && $params['params']['admin'] === true && !isset($params['params']['library'])) {
		$defaultAppConfig = Libraries::get(true);
		$appPath = $defaultAppConfig['path'];

		$paths['layout'] = array(
			$appPath . '/views/layouts/{:layout}.{:type}.php',
			// Last, look in the blackprint library...
			$appPath . '/libraries/blackprint/views/layouts/{:layout}.{:type}.php'
		);

		$paths['template'] = array(
			$appPath . '/views/{:controller}/{:template}.{:type}.php',
			// blackprint has no controller other than pages. This basically ensures "/admin" still works.
			$appPath . '/libraries/blackprint/views/{:controller}/{:template}.{:type}.php'
		);

		// Allow admin elements to be overridden for the main app looking to use the admin templates.
		// There is the top nav element as well as the footer...But if they aren't being overwritten,
		// simply use the templates that exist in blackprint.
		$paths['element'] = array(
			$appPath . '/views/elements/{:template}.{:type}.php',
			$appPath . '/libraries/blackprint/views/elements/{:template}.{:type}.php'
		);

		$params['options']['render']['paths'] = $paths;
	}

	return $chain->next($self, $params, $chain);
});
?>