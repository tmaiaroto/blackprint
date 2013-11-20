<?php
namespace blackprint\models;

use lithium\util\Validator;
use lithium\core\Libraries;
use \MongoId;

class Content extends \blackprint\models\BaseModel {

	protected $_meta = array(
		// not locked. this needs to embrace the schemaless nature of MongoDB.
		// ...this also means validation is going to be loose and depend on JavaScript...which means "trusted" users should be creating content.
		// TODO: Think about possibly changing this back and using models that extend this model... though that leaves a lot more work.
		// It's easier to just keep adding different view templates for different content types.
		'locked' => false,
		'source' => 'blackprint.content'
	);

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		// I think content types will hold extended schema and validation rules...???
		'_type' => array('type' => 'string'),
		'title' => array('type' => 'string'),
		'_ownerId' => array('type' => 'object'),
		// this field holds references to files stored in GridFS...
		// in here will be not just the _id of the GridFS file, but also meta data specific to this piece of content...what the file is for, etc.
		// ex. "download" or "promoImage" etc. up to the content type to dictate?
		'_files' => array('type' => 'object'),
		'summary' => array('type' => 'string'),
		'body' => array('type' => 'string'),
		'url' => array('type' => 'string'),
		'options' => array('type' => 'object'),
		'published' => array('type' => 'boolean'),
		'modified' => array('type' => 'date'),
		'created' => array('type' => 'date')
	);

	static $urlField = 'title';

	static $urlSeparator = '-';

	static $searchSchema = array(
		'title' => array(
			'weight' => 1
		),
		'body' => array(
			'weight' => 1
		)
	);

	public $validates = array(
		'title' => array(
			array('notEmpty', 'message' => 'Title cannot be empty.')
		)
	);

	static $defaultOptions = array(		
	);
	
	/**
	 * Returns a list of all content types, which is based
	 * on the admin_create_xxxx templates found in the content
	 * views directory.
	 * 
	 * @return array
	 */
	public static function contentTypes() {
		// Figure out all the types of content so the user can be presented with options to choose from.
		$defaultAppConfig = Libraries::get(true);
		$appPath = $defaultAppConfig['path'];
		$viewTemplatePaths = array($appPath . '/libraries/blackprint/views/content', $appPath . '/views/_libraries/blackprint/content');
		$contentTypes = array();
		foreach($viewTemplatePaths as $path) {
			if(file_exists($path)) {
				$handle = opendir($path);
				if($handle) {
					while (false !== ($entry = readdir($handle))) {
						if ($entry != "." && $entry != "..") {
							if(substr($entry, 0, 13) == 'admin_create_') {
								$currentType = array('name' => str_replace('.html.php', '', substr($entry, 13)));
								$configFile = $path . '/' . $currentType['name'] . '.ini';
								$currentType['options'] = array();
								if(file_exists($configFile)) {
									$currentType['options'] = parse_ini_file($configFile, true);
								}
								// md5 prevents dupes, array_unique() would be used but the array values are multi-dimensional
								$contentTypes[md5($currentType['name'])] = $currentType;
							}
						}
					}
					closedir($handle);
				}
			}
		}
		sort($contentTypes);
		return $contentTypes;
	}
	
	/**
	 * Gets the content configuration options set in its (optional) ini file.
	 * 
	 * @param string $type The content type
	 * @return array
	 */
	public static function contentConfig($type=null) {
		$default = array(
			'files' => array(
				'allow' => false,
				'limit' => 1,
				'maxSize' => 5242880, // 5MB
				'extensions' => 'jpg,gif,jpeg,png',
				'replaceOnUpdate' => false // keeps appending files when the content document is updated...files have to be removed manually this way
			)
		);
		
		if(empty($type)) {
			return $default;
		}
		
		$defaultAppConfig = Libraries::get(true);
		$appPath = $defaultAppConfig['path'];
		$configPaths = array($appPath . '/libraries/blackprint/views/content/' . $type . '.ini', $appPath . '/views/_libraries/blackprint/content/' . $type . '.ini');
		
		if(file_exists($configPaths[0])) {
			$config = parse_ini_file($configPaths[0], true);
		}
		if(file_exists($configPaths[1])) {
			$config = parse_ini_file($configPaths[1], true);
		}
		if(isset($config['files']) && is_array($config['files'])) {
			$config['files'] += $default['files'];
		} else {
			$config['files'] = $default['files'];
		}
		
		return $config;
	}

}
?>