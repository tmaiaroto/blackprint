<?php
/**
 * Handles file assets for the system stored in MongoDB's GridFS.
*/
namespace blackprint\models;

use lithium\util\Validator;
use lithium\util\Inflector as Inflector;
use \MongoDate;

class Asset extends \lithium\data\Model {

	// Use GridFS in MongoDB
	protected $_meta = array(
		'locked' => true,
		'source' => 'fs.files'
	);

	/**
	 * There is only some very basic information stored on this document.
	 * That is basically the file itself and any exif data specific to the file.
	 * 
	 * The files stored in GridFS should contain as little extra data possible.
	 * This allows the file assets to be re-used more easily within the system.
	 *
	 * For example: Maybe there's an image that belongs to multiple galleries,
	 * but in each gallery the image has a different name and description.
	*/
	public static $fields = array(
		// non-unique filename, this is the name of the file that was sent and may be better suited for display to users
		'originalFilename' => array('type' => 'string'),
		'exif' => array('type' => 'object'),
		'_thumbnail' => array('type' => 'boolean'),
		// not technically required, but is common.
		'filename' => array('type' => 'string'),
		// file extension is not needed by Mongo, we use it for working with resizing/generating images.
		'fileExt' => array('type' => 'string'),
		// the mime-type
		'contentType' => array('type' => 'string'),
		// This represents the 'type' of asset, or what it's associated to.
		// NOTE: These fields may no longer be used?
		'ref' => array('type' => 'string'),
		'file' => array('label' => 'Profile Image', 'type' => 'file')
	);

	public static $validate = array();

	static $searchSchema = array(
		'originalFilename' => array(
			'weight' => 1
		)
	);

	public function __construct() {
		$class =  __CLASS__;
		self::$fields += static::$fields;
		self::$validate += static::$validate;
	}

	/**
	 * Stores in GridFS.
	 * Call this insetad of save()
	 *
	 * @return mixed False on fail, ObjectId on success
	*/
	public static function store($filename=false, $metadata=array()) {
		if(!$filename) {
			return false;
		}

		$ext = isset($metadata['fileExt']) ? strtolower($metadata['fileExt']):null;
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
		$metadata['contentType'] = $mimeType;

		if(file_exists($filename)) {
			// Get the database connection
			$db = self::connection();
			// Connect to it
			$db->connect();
			// ...And then get the GridFS
			$grid = $db->connection->getGridFS();
			// ...To then store the file
			return $grid->storeFile($filename, $metadata);
		}
		return false;
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

// Filter when deleting, also remove thumbnails
Asset::applyFilter('delete', function($self, $params, $chain) {
	if(!isset($params['entity']->_thumbnail) || empty($params['entity']->_thumbnail)) {
		$thumbnailRef = hash('md5', (string)$params['entity']->_id);
		Asset::remove(array('ref' => $thumbnailRef));
	}
	
	return $chain->next($self, $params, $chain);
});
?>