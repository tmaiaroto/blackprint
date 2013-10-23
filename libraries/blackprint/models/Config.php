<?php
/**
 * The Config model stores all CMS settings in the database,
 * but also contains some default values to get started with.
 *
 * Configurations can also be imported/exported in JSON.
 *
 * There's really only need for one document at this point though.
 * Maybe in the future there could be "multi-site" or something.
 *
 * NOTE: The database name/connection obviously can't be stored
 * in the database itself =) So this model has the ability to
 * make some configurations to go in files on disk.
*/
namespace blackprint\models;

class Config extends \lithium\data\Model {

	protected $_meta = array(
		'locked' => true,
		'source' => 'blackprint.config'
	);

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		// Config name...For multiple configurations in the future.
		'name' => array('type' => 'string'),
		'siteName' => array('type' => 'string'),
		'adminEmail' => array('type' => 'string'),
		'privacyPolicyUrl' => array('type' => 'string'),
		'termsOfServiceUrl' => array('type' => 'string'),
		'externalAuthServices' => array('type' => 'object'),

		'modified' => array('type' => 'date'),
		'created' => array('type' => 'date')
	);
	
}
?>