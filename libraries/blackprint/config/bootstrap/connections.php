<?php
use lithium\core\Libraries;
use lithium\data\Connections;

/**
 * This includes all connection configurations added in the `config/connections`
 * directory of the main application.
 * 
 * This allows for this file to remain coflict free and allows the li3 console
 * command to create new files in that directory rather than trying to modify
 * this one, which could also lead to conflicts and other issues.
 * 
 * These connection configuration files are included in alphabetical order.
 * 
 * Blackprint will put conventional emphasis on all configuration names,
 * including connections, for example:
 * blackprint
 * blackprint_mongodb
 * blackprint_xxxxxx
 * 
 * and so on...
 * 
 * So add-on libraries wishing to use Blackprint, should consider using
 * these connection names in their models if they wish to extend things.
 * They should also consider prefixing their model $_meta['source'] values
 * to use prefixed names for collections to avoid conflicts since using
 * a conventional/default connection will mean multiple libraries using
 * the same database.
 *
 * For example, if two libraries have a `User` model, they would conflict
 * if both used the default `users` collection. A better idea would
 * be to set $_meta['source'] = 'libName.users' or 'libName_users' etc.
 * Not only does this avoid conflict, but it also immediately clues
 * a developer into which collections are used by which library when
 * looking at the database.
 * 
*/

/**
 * MongoDB is the database of choice for Blackprint because it is schemaless.
 * This allows the CMS to add new fields to documents without always having to
 * update schema in the database. This is a critical part of the design.
 *
 * Second, it's not prone to SQL injection, so there will be less chance for
 * security issues. While the Lithium Framework has some great santizing classes,
 * we still can't be certain what 3rd party add-ons will be using (since one does
 * not need to use Lithium to build an add-on). So this removes a burden for the
 * developer, apparently (given how common SQL injection is), and reduces risk. 
 */
Connections::add(
	'blackprint_mongodb', array(
		'production' => array(
			'type' => 'MongoDb',
			'host' => 'localhost',
			'database' => 'blackprint'
		),
		'development' => array(
			'type' => 'MongoDb',
			'host' => 'localhost',
			'database' => 'blackprint_dev'
		),
		'test' => array(
			'type' => 'database', 
			'adapter' => 'MongoDb', 
			'database' => 'blackprint_test', 
			'host' => 'localhost'
		)
	)
);

$appConfig =  Libraries::get(true);
$connd = $appConfig['path'] . '/config/bootstrap/connections/*.php';
$conndFiles = glob($connd);
if(!empty($conndFiles)) {
	asort($conndFiles);
}

foreach ($conndFiles as $filename) {
	include $filename;
}
?>