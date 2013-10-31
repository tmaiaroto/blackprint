<?php
use lithium\core\Libraries;

/**
 * This includes all libraries added in the `config/libraries` directory
 * of the main application.
 * 
 * This allows for this file to remain coflict free and allows the li3 console
 * command to create new files in that directory rather than trying to modify
 * this one, which could also lead to conflicts and other issues.
*/

Libraries::add('li3_access');
Libraries::add('lusitanian', array('bootstrap' => 'oauth/src/OAuth/bootstrap.php'));
Libraries::add('swiftmailer', array('bootstrap' => 'swiftmailer/lib/swift_required.php'));
Libraries::add('li3_swiftmailer');

$appConfig =  Libraries::get(true);
$libd = $appConfig['path'] . '/config/bootstrap/libraries/*.php';

foreach (glob($libd) as $filename) {
	include $filename;
}
?>