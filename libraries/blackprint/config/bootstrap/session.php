<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

/**
 * This configures your session storage. The Cookie storage adapter must be connected first, since
 * it intercepts any writes where the `'expires'` key is set in the options array.
 */
use lithium\storage\Session;
use lithium\core\Libraries;

/** 
 * This ensures any other library (loaded first) applies its config
 * and it remains. Running Session::config(array(...)) again will
 * overwrite the original configuration.
 * Therefore, we get the current configuration and append it to
 * Blackprint's default, which is a very common and basic config.
 * This prevents each library from needing to set a session
 * configuration and gives a default that each library can assume
 * will be available.
 * 
 * Yes, it is a good idea for your libraries with special session
 * needs to use uniquely named configurations. It is also wise
 * to run the same type of append method as you see below in order
 * to ensure you are not overwriting any session configurations
 * set by other libraries that may have been loaded before yours.
 */ 
$config = Session::config();
$config += array(
	'cookie' => array('adapter' => 'Cookie'),
	'default' => array('adapter' => 'Php'),
	'blackprint' => array('adapter' => 'Model', 'model' => 'blackprint\models\Session')
);

// Allow the session configuration to be set in the Libraries::add() call for blackprint.
$blackprintCoreConfig = Libraries::get('blackprint');
if(isset($blackprintCoreConfig['session'])) {
	$config = $blackprintCoreConfig['session'];
}

Session::config($config);
?>