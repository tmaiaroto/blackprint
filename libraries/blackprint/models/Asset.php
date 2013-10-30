<?php
namespace blackprint\models;

use lithium\util\Validator;
use lithium\util\Inflector as Inflector;
use \MongoDate;

class Asset extends \lithium\data\Model {

	// Use the gridfs in MongoDB
	protected $_meta = array(
		'source' => 'fs.files'
	);

	// I get appended to with the plugin's Asset model (a good way to add extra meta data).
	public static $fields = array(
		'_thumbnail' => array('type' => 'boolean'),
		// not technically required, but is common.
		'filename' => array('type' => 'string'),
		// file extension is not needed by Mongo, we use it for working with resizing/generating images.
		'fileExt' => array('type' => 'string'),
		// the mime-type
		'contentType' => array('type' => 'string'),
		// This represents the 'type' of asset, or what it's associated to.
		'ref' => array('type' => 'string'),
		'file' => array('label' => 'Profile Image', 'type' => 'file')
	);

	public static $validate = array();

	public $search_schema = array(
		'filename' => array(
			'weight' => 1
		)
	);

	public static function __init() {
		$class =  __CLASS__;
		self::$fields += static::$fields;
		self::$validate += static::$validate;
		$class::_object()->search_schema = static::_object()->search_schema += $class::_object()->search_schema;
	}

	/**
	 * Gets or sets the search schema for the model.
	 * 
	 * @param array Optional new search schema values
	 * @return array
	*/
	public static function searchSchema($schema=array()) {
		$class =  __CLASS__;
		if(!empty($schema)) {
			$class::_object()->search_schema = $schema;
		}
		return (isset($class::_object()->search_schema) && !empty($class::_object()->search_schema)) ? $class::_object()->search_schema:array();
	}

}

/* FILTERS
 *
*/
Asset::applyFilter('save', function($self, $params, $chain) {
	// Set the mime-type based on file extension.
	// This is used in the Content-Type header later on.
	// Doing this here in a filter saves some work in other places and all
	// that's required is a file extension.
	$ext = isset($params['entity']->fileExt) ? strtolower($params['entity']->fileExt):null;
	switch($ext) {
		default:
			$mimeType = 'text/plain';
		break;
		case 'jpg':
		case 'jpeg':
			$mimeType = 'image/jpeg';
		break;
		case 'png':
			$mimeType = 'image/png';
		break;
		case 'gif':
			$mimeType = 'image/gif';
		break;
	}
	$params['data']['contentType'] = $mimeType;

	return $chain->next($self, $params, $chain);
});

// Second, let's get the validation rules picked up from our $validate property
Asset::applyFilter('validates', function($self, $params, $chain) {
	$params['options']['rules'] = Asset::$validate;
	return $chain->next($self, $params, $chain);
});
?>