<?php
/**
 * This file can be overwritten and configured to change that
 * database connection, or the settings can be changed by using the
 * `config.ini` file. Of course, additional database connections can
 * be added here or under any other file in the `connections` directory.
 * 
 * In the optional `config.ini` file under the `config` directory (create one
 * if it doesn't exist), ensure you have a [mongodb] section. Under that section
 * use the following keys:
 * database= (required)
 * host= (required)
 * timeout=
 * login=
 * password=
 * port=
 * devDatabase=
 * devHosts=
 * replicaSet=
 * readPreference=
 * 
 * devHosts is a comma separated string, for ex: localhost,www.mydevsite.com
 * Same goes for host. That key will be used for the MongoDB connection, so
 * when you are using replica sets, it is a comma separate string with optional
 * port numbers after each host name. See Mongo docs for more on that one.
 *
 * NOTE: When using replica sets, these host names must match EXACTLY.
 * If you're unsure how Mongo sees those hostnames, simply use the mongo shell
 * and issue the following command: `rs.conf()` and you'll see all the members
 * with their hostname:port exactly as you need to specify.
 * Even IF you have host aliases under /etc/hosts and can connect using those
 * from command line, it still has to be exactly how MongoDB sees things.
 */
use lithium\data\Connections;
use lithium\core\Environment;
use \MongoClient;

// For CLI
$env = 'production';
if(isset($_SERVER['argv'][1])) {
	if(substr($_SERVER['argv'][1], 0, 6) == '--env=') {
		$env = substr($_SERVER['argv'][1], 6);
		Environment::set($env);
	}
}

$defaults = array(
	'database' => 'blackprint',
	'devDatabase' => 'blackprint_dev',
	'host' => 'localhost',
	// this is a good value for remote MongoDB services such as MongoLab or MongoHQ or Object Rocket, etc.
	// on a local server, this is obviously quite generous and could be lowered...
	'timeout' => 81000,
	'devHosts' => array('localhost', 'blackprint.dev.local', 'blackprint.localdev.com')
);
$options = $defaults;

$li3Options = false;
// Optional config.ini file sets some options.
if(file_exists(dirname(dirname(__DIR__)) . '/config.ini')) {
	$li3Options = parse_ini_file(dirname(dirname(__DIR__)) . '/config.ini', true);
	$li3Options = isset($li3Options['mongodb']) ? $li3Options['mongodb']:$li3Options;
}
if($li3Options) {
	$options = $li3Options += $defaults;
	if(is_string($options['devHosts'])) {
		$options['devHosts'] = explode(',', $options['devHosts']);
	}
}

// See if we should use the dev database (this is based on hostname).
$httpHost = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']:'localhost';
if(in_array($httpHost, $options['devHosts']) || $env == 'development') {
	$options['database'] = $options['devDatabase'];
	$env == 'development';
}

// Set the environment if it hasn't been set yet (which would be be set by CLI at this point).
$environment = Environment::get();
if(empty($environment)) {
	Environment::set($env);
}

$dbOptions = array(
	'type' => 'database',
	'adapter' => 'MongoDb',
	'database' => $options['database'],
	'host' => $options['host'],
	'timeout' => $options['timeout']
);

// For replica sets. This value should be the name of the replica set.
if(isset($options['replicaSet']) && !empty($options['replicaSet'])) {
	$dbOptions['replicaSet'] = $options['replicaSet'];
}

// For read preference (NOTE: All read preferences other than RP_PRIMARY may return stale data).
if(isset($options['readPreference']) && !empty($options['readPreference'])) {
	// Cover all our bases here and allow values that are similar in nature.
	switch($options['readPreference']) {
		case 'primary':
		case 'RP_PRIMARY':
		case 'rp_primary':
		case 'MongoClient::RP_PRIMARY':
		default:
			$dbOptions['readPreference'] = MongoClient::RP_PRIMARY;
			break;
		case 'primary_perferred':
		case 'RP_PRIMARY_PREFERRED':
		case 'rp_primary_preferred':
		case 'MongoClient::RP_PRIMARY_PREFERRED':
			$dbOptions['readPreference'] = MongoClient::RP_PRIMARY_PREFERRED;
			break;
		case 'secondary':
		case 'RP_SECONDARY':
		case 'rp_secondary':
		case 'MongoClient::RP_SECONDARY':
			$dbOptions['readPreference'] = MongoClient::RP_SECONDARY;
			break;
		case 'secondary_preferred':
		case 'RP_SECONDARY_PREFERRED':
		case 'rp_secondary_preferred':
		case 'MongoClient::RP_SECONDARY_PREFERRED':
			$dbOptions['readPreference'] = MongoClient::RP_SECONDARY_PREFERRED;
			break;
		case 'nearest':
		case 'RP_NEAREST':
		case 'rp_nearest':
		case 'MongoClient::RP_NEAREST':
			$dbOptions['readPreference'] = MongoClient::RP_NEAREST;
			break;
	}
}

// Typically we firewall our MongoDB, but for those of you using a service or who just
// like to have a username and password anyway....
if(isset($options['login'])) {
	$dbOptions['login'] = $options['login'];
}
if(isset($options['password'])) {
	$dbOptions['password'] = $options['password'];
}

// This overrides the library's configuration using the options above.
// By default, the settings are the same the and the database used will be called "blackprint"
Connections::add('blackprint_mongodb', $dbOptions);

// Of course set the default connection to use this MongoDB connection as well.
// This will make it a little easier for your main application. You won't need
// to specify the connection name in each model class.
Connections::add('default', $dbOptions);


// Add your own connections here or in another file within the `connections`
// directory. You may want to consider adding another file if you wisht to
// keep up to date with the li3_bootstrap repository. Though the li3_bootstrap
// repository is meant to be forked (it is the li3_core library along with some
// others that power Lithium Bootstrap).
?>