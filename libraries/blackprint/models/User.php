<?php
/**
 * TODO: Make another model for storing user events.
 * Events will be sent to Google sure (and maybe some internal analytics) but we also want to store actual user events.
 * What we'll then do is allow admins to create segments. So for example, "give me a segment of all users who have read less than 2 blog posts" etc.
 * The templating system will be aware of users and the segments they belong to....This way different content can be rendered to different segments.
 *
 * The e-mail system (which needs more work) can also target users to e-mail by segment.
 *
 * Anonymous visitors can also be segmented. They need to get a fingerprint (a hash of user agent + ip ... maybe some other data) and their actions/events
 * are also recorded. Once they register, the fingerprint is matched and applied so they become known in the system, but keep their segments.
 *
 * Going even farther -- if users have connected their social media accounts then we can even work with the social network APIs (like Twitter allows for this I believe)
 * to manage audiences. Even internally we can create segments of users for social networks. So a group of all Twitter usernames that performed some action.
 * 
 * Export ALL segemnts as CSV. Then those twitter usernames, e-mails, etc. can be imported and used elsewhere.
 *
 * TODO: Make a user role "collaborator" or "guest blogger" ... These users can be invited to the system and can write or edit drafts (or certain drafts, maybe their own
 * and maybe limited number of posts), but never publish/unpublish or update existing posts. Only admins (maybe site editors too) can publish their posts. 
 *
 * So an entire guest blogging system by invite. Users get the e-mail or even Twitter message and can go to the URL to sign in/register. If invite via Twitter 
 * (which is totally awesome btw - especially now that anyone can be direct messaged, but even if it was a publish mention it's fine), the Twitter username 
 * would need to match. So only that person could accept the invite.
 *
 * Maybe invite links are one time posts. Only good for one story. Maybe that's just optional too.
 */
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