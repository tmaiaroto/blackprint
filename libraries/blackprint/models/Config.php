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

use lithium\storage\Cache;

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
		'adminName' => array('type' => 'string'),
		'privacyPolicyUrl' => array('type' => 'string'),
		'termsOfServiceUrl' => array('type' => 'string'),
		// Facebook, Twitter, etc. If you can login via OAuth, the system can authenticate you that way too.
		'externalAuthServices' => array('type' => 'object'),
		// What's allowed to be uploaded, etc.
		'assets' => array('type' => 'object'),
		// E-mail server info, SMS API, etc. All settings related to how the system sends/receives messages.
		'communications' => array('type' => 'object'),
		// Default OpenGraph tags
		'og' => array('type' => 'object'),
		// App IDs, and other data, for social media apps (Facebook's JS SDK will need this for example and it gets included in the layout template)
		'socialApps' => array('type' => 'object'),
		// Other web page meta data
		'meta' => array('type' => 'object'),

		'modified' => array('type' => 'date'),
		'created' => array('type' => 'date')
	);

	/*
	 * A safe set of allowed file extensions by default.
	*/
	public static $allowedFileExtensions = array(
		// images, fonts, icons, and adobe app files
		'jpg', 'jpeg', 'png', 'gif', 'pdf', 'svg', 'bmp', 'tif', 'tiff', 'tga', 'thm', 'pspimage', 'ico', 'icns', 'pct', 'psd', 'ps', 'indd', 'eps', 'ai', 'yuv', 'dds', 'dwg', 'dxf', 'ttf', 'fnt', 'fon', 'otf', '3ds', 'max', '3dm', 'obj', 
		// flash and flash media
		'swf', 'flv', 'f4v', 'fvp', 'f4a', 'f4b',
		// audio and video
		'avi', 'xvid', 'xmv', 'mp4', 'mov', 'qtm', 'mpg', 'mpeg', 'mp3', 'wma', 'wav', 'mid', 'aif', 'mpa', 'm4a', 'm4v', 'wmv', 'ogg', 'ogx', 'ogv', 'oga',
		// documents, vCard, ebooks, calendar, etc.
		'csv', 'xml', 'vcf', 'ibooks', 'mobi', 'epub', 'txt', 'rtf', 'wps', 'wpd', 'odt', 'pages', 'tex', 'doc', 'docx', 'xls', 'xlsx', 'xlr', 'ppt', 'pps', 'pptx', 'ics',
		// compressed files
		'zip', 'zipx', 'tar', 'rar', 'gzip', 'gz', 'bz2', '7z', 'pkg', 'sitx', 'iso', 'dmg',
		// log files, database files, configuration files
		'log', 'msg', 'sql', 'mdb', 'db', 'accdb', 'ini', 'cfg', 'rss',
		// executables/apps (no linux binaries, no cgi, no java executables -- apps should be zipped first for distribution anyway)
		'apk', 'app', 'msi'
	);

	/**
	 * Get the configuration (from cache is possible).
	*/
	public static function get($name=null, $fields=array()) {
		// Just one for now
		$name = 'default';

		$configData = array();

		if($cache = Cache::read('blackprint', 'blackprintConfig')) {
			$configData = $cache;
		} else {
			if(!empty($fields) && is_array($fields)) {
				$configDoc = Config::find('first', array('conditions' => array('name' => 'default'), 'fields' => $fields));
			} else {
				$configDoc = Config::find('first', array('conditions' => array('name' => 'default')));
			}
			if(!empty($configDoc)) {
				$configData = $configDoc->data();
			}
		}

		return $configData;
	}

	/**
	 * Returns an array of allowed file extensions, safe for upload.
	*/
	public static function getAllowedFileExtensions($append=false) {
		$allowedFileExtensions = self::$allowedFileExtensions;
		$assetsConfig = Config::get('default', array('assets'));
		
		// Allow $append to be forced true by argument or use the config setting if set
		$append = (isset($assetsConfig['appendToAllowedExtensionsDefault']) && is_bool($assetsConfig['appendToAllowedExtensionsDefault']) && $append !== true) ? $assetsConfig['appendToAllowedExtensionsDefault']:$append;
		
		if(isset($assetsConfig['allowedExtensions'])) {
			if($append) {
				$allowedFileExtensions += explode(',', $assetsConfig['allowedFileExtensions']);
			} else {
				$allowedFileExtensions = explode(',', $assetsConfig['allowedFileExtensions']);
			}
		}

		return $allowedFileExtensions;
	}
	
}
?>