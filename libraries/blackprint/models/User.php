<?php
namespace blackprint\models;

use blackprint\extensions\Util;
use lithium\util\Validator;
use lithium\storage\Cache;
use lithium\util\Inflector;
use lithium\security\Auth;
use lithium\security\Password;
use lithium\data\entity\Document;
use lithium\core\Libraries;
use \MongoId;
use \Exception;

class User extends \blackprint\models\BaseModel {

	protected $_meta = array(
		'locked' => true,
		'source' => 'blackprint.users'
	);

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'firstName' => array('type' => 'string'),
		'lastName' => array('type' => 'string'),
		'profilePicture' => array('type' => 'string'),
		'utcOffset' => array('type' => 'number'),
		'timezone' => array('type' => 'string'),
		'locale' => array('type' => 'string'),
		'externalAuthServices' => array('type' => 'object'),
		'url' => array('type' => 'string'),
		'email' => array('type' => 'string'),
		'password' => array('type' => 'string'),
		'resetCode' => array('type' => 'string'),
		'resetExpiration' => array('type' => 'date'),
		'resetRequests' => array('type' => 'number'),
		'resetRequestedFromIp' => array('type' => 'string'),
		'resetRequestedDate' => array('tpe' => 'date'),
		'role' => array('type' => 'string'),
		'active' => array('type' => 'boolean'),
		'lastLoginIp' => array('type' => 'string'),
		'lastLoginTime' => array('type' => 'date'),
		'modified' => array('type' => 'date'),
		'created' => array('type' => 'date')
	);

	static $urlField = array('firstName', 'lastName');

	static $urlSeparator = '-';

	static $searchSchema = array(
		'first_name' => array(
			'weight' => 1
		),
		'last_name' => array(
			'weight' => 1
		),
		'email' => array(
			'weight' => 1
		)
	);

	// These are user roles for the entire system.
	protected $_user_roles = array(
		'administrator' => 'Administrator',
		'content_editor' => 'Content Editor',
		'registered_user' => 'Registered User'
	);

	public $validates = array(
		'firstName' => array(
			array('notEmpty', 'message' => 'First name cannot be empty.')
		),
		'email' => array(
			array('notEmpty', 'message' => 'E-mail cannot be empty.'),
			array('email', 'message' => 'E-mail is not valid.'),
			// array('uniqueEmail', 'message' => 'Sorry, this e-mail address is already registered.'),
		),
		'password' => array(
			array('notEmpty', 'message' => 'Password cannot be empty.'),
			array('notEmptyHash', 'message' => 'Password cannot be empty.'),
			array('moreThanFive', 'message' => 'Password must be at least 6 characters long.')
		),
		'profilePicture' => array(
			array('notTooLarge', 'message' => 'Profile picture cannot be larger than 250px in either dimension.'),
			array('invalidFileType', 'message' => 'Profile picture must be a jpg, png, or gif image.')
		)
	);

	public function __construct() {
		/*
		 * Some special validation rules
		*/
		Validator::add('uniqueEmail', function($value) {
			$current_user = Auth::check('blackprint');
			if(!empty($current_user)) {
				$user = User::find('first', array('fields' => array('_id'), 'conditions' => array('email' => $value, '_id' => array('$ne' => new MongoId($current_user['_id'])))));
			} else {
				$user = User::find('first', array('fields' => array('_id'), 'conditions' => array('email' => $value)));
			}
			if(!empty($user)) {
			    return false;
			}
			return true;
		});

		Validator::add('notEmptyHash', function($value) {
			if($value == Password::hash('')) {
			    return false;
			}
			return true;
		});

		Validator::add('moreThanFive', function($value) {
			if(strlen($value) < 5) {
				return false;
			}
			return true;
		});

		Validator::add('notTooLarge', function($value) {
			if($value == 'TOO_LARGE.jpg') {
				return false;
			}
			return true;
		});

		Validator::add('invalidFileType', function($value) {
			if($value == 'INVALID_FILE_TYPE.jpg') {
				return false;
			}
			return true;
		});

	}

	/**
	 * Get the user roles.
	 *
	 * @return Array
	*/
	public static function userRoles() {
		$class =  __CLASS__;
		return $class::_object()->_user_roles;
	}

}
?>