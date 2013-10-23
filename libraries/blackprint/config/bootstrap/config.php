<?php
/**
 * This loads configuration variable used throughout the bootstrap process.
 * We can, of course, cache this. The cache can be cleared using the admin dashboard.
 *
*/
use blackprint\models\Config;
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
?>