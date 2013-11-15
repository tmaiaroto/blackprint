<?php
/**
 * This loads configuration variable used throughout the bootstrap process.
 * We can, of course, cache this. The cache can be cleared using the admin dashboard.
 *
*/
use blackprint\models\Config;

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

// NOTE: The templates.php bootstrap file also gets the Config and makes some of the data available in each Request.
// This allows Google Analytics codes, etc. to be used in layout templates.
?>