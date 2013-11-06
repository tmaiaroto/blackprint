<?php
/**
 * This loads configuration variable used throughout the bootstrap process.
 * We can, of course, cache this. The cache can be cleared using the admin dashboard.
 *
*/
use blackprint\models\Config;
use lithium\action\Dispatcher;

/*
use lithium\storage\Cache;

$blackprintConfig = false;
if($cache = Cache::read('blackprint', 'blackprintConfig')) {
	$blackprintConfig = $cache;
} else {
	$blackprintConfig = Config::find('first', array('conditions' => array('name' => 'default')));
	if(!empty($blackprintConfig)) {
		$blackprintConfig = $blackprintConfig->data();
		Cache::write('blackprint', 'blackprintConfig', $blackprintConfig, '+1 day');
	}
}
*/

// Don't cache for now. The cache didn't seem to update properly and it would be kept on one server anyway.
// When things end in a load balanced situation, we'd have potentially different configurations on each server.
// MongoDB is a pretty good "cahce" to be frank, it's fast, so just leave it as a query on each request.
$blackprintConfig = Config::find('first', array('conditions' => array('name' => 'default')));
if(!empty($blackprintConfig)) {
	$blackprintConfig = $blackprintConfig->data();
} else {
	$blackprintConfig = false;
}

// Pass along certain configuration data to each Request. Yes, I know this is a second database query...For now. TODO: Make more efficient.
Dispatcher::applyFilter('_callable', function($self, $params, $chain) {

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

	return $chain->next($self, $params, $chain);
});

?>