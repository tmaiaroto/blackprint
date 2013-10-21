<?php
namespace blackprint\controllers;

use blackprint\models\User;
use blackprint\models\Asset;
use blackprint\util\Util;
use li3_flash_message\extensions\storage\FlashMessage;
use li3_access\security\Access;
use lithium\security\validation\RequestToken;
use lithium\security\Auth;
use lithium\storage\Session;
use lithium\security\Password;
use lithium\util\Set;
use lithium\util\String;
use lithium\util\Inflector;
use MongoDate;
use MongoId;

class UsersController extends \lithium\action\Controller {

	public function admin_index() {
		$this->_render['layout'] = 'admin';

		$conditions = array();
		// If a search query was provided, search all "searchable" fields (any field in the model's $search_schema property)
		// NOTE: the values within this array for "search" include things like "weight" etc. and are not yet fully implemented...But will become more robust and useful.
		// Possible integration with Solr/Lucene, etc.
		if((isset($this->request->query['q'])) && (!empty($this->request->query['q']))) {
			$search_schema = User::searchSchema();
			$search_conditions = array();
			// For each searchable field, adjust the conditions to include a regex
			foreach($search_schema as $k => $v) {
				// TODO: possibly factor in the weighting later. also maybe note the "type" to ensure our regex is going to work or if it has to be adjusted (string data types, etc.)
				// var_dump($k);
				// The search schema could be provided as an array of fields without a weight
				// In this case, the key value will be the field name. Otherwise, the weight value
				// might be specified and the key would be the name of the field.
				$field = (is_string($k)) ? $k:$v;
				$search_regex = new \MongoRegex('/' . $this->request->query['q'] . '/i');
				$conditions['$or'][] = array($field => $search_regex);
			}
		}

		$limit = $this->request->limit ?: 25;
		$page = $this->request->page ?: 1;
		$order = array('created' => 'desc');
		$total = User::count(compact('conditions'));
		$documents = User::all(compact('conditions','order','limit','page'));

		$page_number = (int)$page;
		$total_pages = ((int)$limit > 0) ? ceil($total / $limit):0;

		// Set data for the view template
		return compact('documents', 'total', 'page', 'limit', 'total_pages');
	}

	/**
	 * Allows admins to update users.
	 *
	 * @param string $id The user id
	*/
	public function admin_create() {
		$this->_render['layout'] = 'admin';

		// Special rules for user creation (includes unique e-mail)
		$rules = array(
			'email' => array(
				array('notEmpty', 'message' => 'E-mail cannot be empty.'),
				array('email', 'message' => 'E-mail is not valid.'),
				array('uniqueEmail', 'message' => 'Sorry, this e-mail address is already registered.'),
			)
		);

		$roles = User::userRoles();

		$document = User::create();

		// If data was passed, set some more data and save
		if ($this->request->data) {
			// CSRF
			if(!RequestToken::check($this->request)) {
				RequestToken::get(array('regenerate' => true));
			} else {
				$now = new MongoDate();
				$this->request->data['created'] = $now;
				$this->request->data['modified'] = $now;

				// Add validation rules for the password IF the password and password_confirm field were passed
				if((isset($this->request->data['password']) && isset($this->request->data['passwordConfirm'])) &&
					(!empty($this->request->data['password']) && !empty($this->request->data['passwordConfirm']))) {
					$rules['password'] = array(
						array('notEmpty', 'message' => 'Password cannot be empty.'),
						array('notEmptyHash', 'message' => 'Password cannot be empty.'),
						array('moreThanFive', 'message' => 'Password must be at least 6 characters long.')
					);

					// ...and of course hash the password
					$this->request->data['password'] = Password::hash($this->request->data['password']);
				} else {
					// Otherwise, set the password to the current password.
					$this->request->data['password'] = $document->password;
				}

				// Ensure the unique e-mail validation rule doesn't get in the way when editing users
				// So if the user being edited has the same e-mail address as the POST data...
				// Change the e-mail validation rules
				if(isset($this->request->data['email']) && $this->request->data['email'] == $document->email) {
					$rules['email'] = array(
						array('notEmpty', 'message' => 'E-mail cannot be empty.'),
						array('email', 'message' => 'E-mail is not valid.')
					);
				}

				// Set the pretty URL that gets used by a lot of front-end actions.
				$this->request->data['url'] = $this->_generateUrl();

				// Save
				if($document->save($this->request->data, array('validate' => $rules))) {
					FlashMessage::write('The user has been created successfully.', 'blackprint');
					$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
				} else {
					$this->request->data['password'] = '';
					FlashMessage::write('The user could not be created, please try again.', 'blackprint');
				}
			}
		}

		$this->set(compact('document', 'roles'));
	}

	/**
	 * Allows admins to update users.
	 *
	 * @param string $id The user id
	*/
	public function admin_update($id=null) {
		$this->_render['layout'] = 'admin';

		// Special rules for user creation (includes unique e-mail)
		$rules = array(
			'email' => array(
				array('notEmpty', 'message' => 'E-mail cannot be empty.'),
				array('email', 'message' => 'E-mail is not valid.'),
				array('uniqueEmail', 'message' => 'Sorry, this e-mail address is already registered.'),
			)
		);

		$roles = User::userRoles();

		// Get the document from the db to edit
		$conditions = array('_id' => $id);
		$document = User::find('first', array('conditions' => $conditions));

		// Redirect if invalid user
		if(empty($document)) {
			FlashMessage::write('That user was not found.', 'blackprint');
			return $this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
		}

		// If data was passed, set some more data and save
		if ($this->request->data) {
			// CSRF
			if(!RequestToken::check($this->request)) {
				RequestToken::get(array('regenerate' => true));
			} else {
				$now = new MongoDate();
				$this->request->data['modified'] = $now;

				// Add validation rules for the password IF the password and password_confirm field were passed
				if((isset($this->request->data['password']) && isset($this->request->data['passwordConfirm'])) &&
					(!empty($this->request->data['password']) && !empty($this->request->data['passwordConfirm']))) {
					$rules['password'] = array(
						array('notEmpty', 'message' => 'Password cannot be empty.'),
						array('notEmptyHash', 'message' => 'Password cannot be empty.'),
						array('moreThanFive', 'message' => 'Password must be at least 6 characters long.')
					);

					// ...and of course hash the password
					$this->request->data['password'] = Password::hash($this->request->data['password']);
				} else {
					// Otherwise, set the password to the current password.
					$this->request->data['password'] = $document->password;
				}
				// Ensure the unique e-mail validation rule doesn't get in the way when editing users
				// So if the user being edited has the same e-mail address as the POST data...
				// Change the e-mail validation rules
				if(isset($this->request->data['email']) && $this->request->data['email'] == $document->email) {
					$rules['email'] = array(
						array('notEmpty', 'message' => 'E-mail cannot be empty.'),
						array('email', 'message' => 'E-mail is not valid.')
					);
				}

				// Set the pretty URL that gets used by a lot of front-end actions.
				// Pass the document _id so that it doesn't change the pretty URL on an update.
				$this->request->data['url'] = $this->_generateUrl($document->_id);

				// Save
				if($document->save($this->request->data, array('validate' => $rules))) {
					FlashMessage::write('The user has been updated successfully.', 'blackprint');
					$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
				} else {
					$this->request->data['password'] = '';
					FlashMessage::write('The user could not be updated, please try again.', 'blackprint');
				}
			}
		}

		$this->set(compact('document', 'roles'));
	}

	/**
	 * Allows admins to delete other users.
	 *
	 * @param string $id The user id
	*/
	public function admin_delete($id=null) {
		$this->_render['layout'] = 'admin';

		// Get the document from the db to edit
		$conditions = array('_id' => $id);
		$document = User::find('first', array('conditions' => $conditions));

		// Redirect if invalid user
		if(empty($document)) {
			FlashMessage::write('That user was not found.', 'blackprint');
			return $this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
		}

		if($this->request->user['_id'] != (string) $document->_id) {
			if($document->delete()) {
				FlashMessage::write('The user has been deleted.', 'blackprint');
			} else {
				FlashMessage::write('The could not be deleted, please try again.', 'blackprint');
			}
		} else {
			FlashMessage::write('You can\'t delete yourself!', 'blackprint');
		}

		return $this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
	}

	/**
	 * Admin user dashboard.
	 *
	*/
	public function admin_dashboard() {
		$this->_render['layout'] = 'admin';
		
	}

	/**
	 * Registers a user.
	*/
	public function register() {
		// Special rules for registration
		$rules = array(
			'email' => array(
				array('notEmpty', 'message' => 'E-mail cannot be empty.'),
				array('email', 'message' => 'E-mail is not valid.'),
				array('uniqueEmail', 'message' => 'Sorry, this e-mail address is already registered.'),
			),
			'password' => array(
				array('notEmpty', 'message' => 'Password cannot be empty.'),
				array('notEmptyHash', 'message' => 'Password cannot be empty.'),
				array('moreThanFive', 'message' => 'Password must be at least 6 characters long.')
			)
		);

		$document = User::create();

		// Save
		if ($this->request->data) {
			// CSRF
			if(!RequestToken::check($this->request)) {
				RequestToken::get(array('regenerate' => true));
			} else {
				$now = new MongoDate();
				$this->request->data['created'] = $now;
				$this->request->data['modified'] = $now;

				$this->request->data['active'] = true;

				// Set the pretty URL that gets used by a lot of front-end actions.
				$this->request->data['url'] = $this->_generateUrl();

				// Set the user's role...always hard coded and set.
				$this->request->data['role'] = 'registered_user';

				// However, IF this is the first user ever created, then they will be an administrator.
				$users = User::find('count');
				if(empty($users)) {
					$this->request->data['active'] = true;
					$this->request->data['role'] = 'administrator';
				}

				// Set the password, it has to be hashed
				if((isset($this->request->data['password'])) && (!empty($this->request->data['password']))) {
					$this->request->data['password'] = Password::hash($this->request->data['password']);
				}

				if($document->save($this->request->data, array('validate' => $rules))) {
					FlashMessage::write('User registration successful.', 'blackprint');
					$this->redirect('/');
				} else {
					$this->request->data['password'] = '';
				}
			}
		}

		$this->set(compact('document'));
	}

	/*
	 * Also make the login method available to admin routing.
	 * It can have a different template and layout if need be.
	 * I'm not sure it will need one yet...
	*/
	public function admin_login() {
		$this->_render['layout'] = 'admin';
		$this->_render['template'] = 'login';
		return $this->login();
	}

	/**
	 * Provides a login page for users to login.
	 *
	 * @return type
	*/
	public function login() {
		$user = Auth::check('blackprint', $this->request);
		// 'triedAuthRedirect' so we don't end up in a redirect loop
		if (!Session::check('triedAuthRedirect', array('name' => 'cookie'))) {
			Session::write('triedAuthRedirect', 'false', array('name' => 'cookie', 'expires' => '+1 hour'));
		}

		// Facebook returns a session querystring... We don't want to show this to the user.
		// Just redirect back so it ditches the querystring. If the user is logged in, then
		// it will redirect like expected using the $url variable that has been set below.
		// Not sure why we need to do this, I'd figured $user would be set...And I think there's
		// a session just fine if there was no redirect and the user navigated away...
		// But for some reason it doesn't see $user and get to the redirect() part...
		if(isset($_GET['session'])) {
			$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'login'));
		}

		if ($user) {
			// Users will be redirected after logging in, but where to?
			$url = '/';

			// Default redirects for certain user roles
			switch($user['role']) {
				case 'administrator':
				case 'content_editor':
					$url = '/admin';
					break;
				default:
					$url = '/';
					break;
			}

			// Second, look to see if a cookie was set. The could have ended up at the login page
			// because he/she tried to go to a restricted area. That URL was noted in a cookie.
			if (Session::check('beforeAuthURL', array('name' => 'cookie'))) {
				$url = Session::read('beforeAuthURL', array('name' => 'cookie'));

				// 'triedAuthRedirect' so we don't end up in a redirect loop
				$triedAuthRedirect = Session::read('triedAuthRedirect', array('name' => 'cookie'));
				if($triedAuthRedirect == 'true') {
					$url = '/';
					Session::delete('triedAuthRedirect', array('name' => 'cookie'));
				} else {
					Session::write('triedAuthRedirect', 'true', array('name' => 'cookie', 'expires' => '+1 hour'));
				}

				Session::delete('beforeAuthURL', array('name' => 'cookie'));
			}

			// Save last login IP and time
			$user_document = User::find('first', array('conditions' => array('_id' => $user['_id'])));

			if($user_document) {
				$user_document->save(array('lastLoginIp' => $_SERVER['REMOTE_ADDR'], 'lastLoginTime' => new MongoDate()));
			}

			// only set a flash message if this is a login. it could be a redirect from somewhere else that has restricted access
			// $flash_message = FlashMessage::read('blackprint');
			// if(!isset($flash_message['message']) || empty($flash_message['message'])) {
				FlashMessage::write('You\'ve successfully logged in.', 'blackprint');
			// }
			$this->redirect($url);
		} else {
			if($this->request->data) {
				FlashMessage::write('You entered an incorrect username and/or password.', 'blackprint');
			}
		}
		$data = $this->request->data;

		return compact('data');
	}

	/**
	 * Also make the login available to admin routing.
	*/
	public function admin_logout() {
		return $this->logout();
	}

	/**
	 * Logs a user out.
	*/
	public function logout() {
		Auth::clear('blackprint');
		FlashMessage::write('You\'ve successfully logged out.', 'blackprint');
		$this->redirect('/');
	}

	/**
	 * Checks to see if an e-mail address is already in use.
	 *
	 */
	public function email_check($email=null) {
		$this->_render = false;
		if(empty($email)) {
			echo false;
		}
		echo User::find('count', array('conditions' => array('email' => $email)));
	}

	/**
	 * Change a user password.
	 * This is a method that you request via AJAX.
	 *
	 * @param string $url
	*/
	public function update_password($url=null) {
		// First, get the record
		$record = User::find('first', array('conditions' => array('url' => $url)));
		if(!$record) {
			return array('error' => true, 'response' => 'User record not found.');
		}

		$user = Auth::check('blackprint');
		if(!$user) {
			return array('error' => true, 'response' => 'You must be logged in to change your password.');
		}

		$record_data = $record->data();
		if($user['_id'] != $record_data['_id']) {
			return array('error' => true, 'response' => 'You can only change your own password.');
		}

		// Update the record
		if ($this->request->data) {
			// Make sure the password matches the confirmation
			if($this->request->data['password'] != $this->request->data['password_confirm']) {
				return array('error' => true, 'response' => 'You must confirm your password change by typing it again in the confirm box.');
			}

			// Call save from the User model
			if($record->save($this->request->data)) {
				return array('error' => false, 'response' => 'Password has been updated successfully.');
			} else {
				return array('error' => true, 'response' => 'Failed to update password, try again.');
			}
		} else {
			return array('error' => true, 'response' => 'You must pass the proper data to change your password and you can\'t call this URL directly.');
		}
	}

	/**
	 * Enables/disables the user.
	 * This method should be called via AJAX.
	 *
	 * @param string $id The user's MongoId
	 * @param mixed $active What to set the active field to. 1 = true and 0 = false, 'false' = false too
	 * @return boolean Success
	*/
	public function admin_set_status($id=null, $active=true) {
		$this->_render['layout'] = 'admin';

		// Do our best here
		if($active == 'false') {
			$active = false;
		} else {
			$active = (bool) $active;
		}

		// Only allow this method to be called via JSON
		if(!$this->request->is('json')) {
			return array('success' => false);
		}

		$requested_user = User::find('first', array('conditions' => array('_id' => $id)));

		$current_user = Auth::check('blackprint');

		// Don't allow a user to make themself active or inactive.
		if((string)$request_user->_id == $current_user['_id']) {
			return array('success' => false);
		}

		if(User::update(
			// query
			array(
				'$set' => array(
					'active' => $active
				)
			),
			// conditions
			array(
				'_id' => $requested_user->_id
			),
			array('atomic' => false)
		)) {
			return array('success' => true);
		}

		// Otherwise, return false. Who knows why, but don't do anything.
		return array('success' => false);
	}

	/**
	 * Generates a pretty URL for the user document.
	 *
	 * @return string
	 */
	private function _generateUrl($id=null) {
		$url = '';
		$url_field = User::urlField();
		$url_separator = User::urlSeparator();
		if($url_field != '_id' && !empty($url_field)) {
			if(is_array($url_field)) {
				foreach($url_field as $field) {
					if(isset($this->request->data[$field]) && $field != '_id') {
						$url .= $this->request->data[$field] . ' ';
					}
				}
				$url = Inflector::slug(trim($url), $url_separator);
			} else {
				$url = Inflector::slug($this->request->data[$url_field], $url_separator);
			}
		}

		// Last check for the URL...if it's empty for some reason set it to "user"
		if(empty($url)) {
			$url = 'user';
		}

		// Then get a unique URL from the desired URL (numbers will be appended if URL is duplicate) this also ensures the URLs are lowercase
		$options = array(
			'url' => $url,
			'model' => 'blackprint\models\User'
		);
		// If an id was passed, this will ensure a document can use its own pretty URL on update instead of getting a new one.
		if(!empty($id)) {
			$options['id'] = $id;
		}
		return Util::uniqueUrl($options);
	}

	/**
	 * Allows a user to update their own profile.
	 *
	 */
	public function update() {
		if(!$this->request->user) {
			FlashMessage::write('You must be logged in to do that.', 'blackprint');
			return $this->redirect('/');
		}

		// Special render case. Allow admin users to update their own profile from the admin layout.
		// Since the admin_update() method is for updating OTHER users...We still use this method.
		// We can't, of course, use 'admin' in the route for this method, so that's part of why we have
		// the short and friendly "my-account" and "admin/my-account" routes.
		if(strstr($this->request->url, 'admin')) {
			$this->_render['layout'] = 'admin';
		}

		// Special rules for user creation (includes unique e-mail)
		$rules = array(
			'email' => array(
				array('notEmpty', 'message' => 'E-mail cannot be empty.'),
				array('email', 'message' => 'E-mail is not valid.'),
				array('uniqueEmail', 'message' => 'Sorry, this e-mail address is already registered.'),
			)
		);

		// Get the document from the db to edit
		$conditions = array('_id' => $this->request->user['_id']);
		$document = User::find('first', array('conditions' => $conditions));
		$existingProfilePic = !empty($document->profilePicture) ? $document->profilePicture:false;

		// Redirect if invalid user...This should not be possible.
		if(empty($document)) {
			FlashMessage::write('You must be logged in to do that.', 'blackprint');
			return $this->redirect('/');
		}

		// If data was passed, set some more data and save
		if ($this->request->data) {
			// CSRF
			if(!RequestToken::check($this->request)) {
				RequestToken::get(array('regenerate' => true));
			} else {
				$now = new MongoDate();
				$this->request->data['modified'] = $now;

				// Add validation rules for the password IF the password and password_confirm field were passed
				if((isset($this->request->data['password']) && isset($this->request->data['passwordConfirm'])) &&
					(!empty($this->request->data['password']) && !empty($this->request->data['passwordConfirm']))) {
					$rules['password'] = array(
						array('notEmpty', 'message' => 'Password cannot be empty.'),
						array('notEmptyHash', 'message' => 'Password cannot be empty.'),
						array('moreThanFive', 'message' => 'Password must be at least 6 characters long.')
					);

					// ...and of course hash the password
					$this->request->data['password'] = Password::hash($this->request->data['password']);
				} else {
					// Otherwise, set the password to the current password.
					$this->request->data['password'] = $document->password;
				}
				// Ensure the unique e-mail validation rule doesn't get in the way when editing users
				// So if the user being edited has the same e-mail address as the POST data...
				// Change the e-mail validation rules
				if(isset($this->request->data['email']) && $this->request->data['email'] == $document->email) {
					$rules['email'] = array(
						array('notEmpty', 'message' => 'E-mail cannot be empty.'),
						array('email', 'message' => 'E-mail is not valid.')
					);
				}

				// Set the pretty URL that gets used by a lot of front-end actions.
				// Pass the document _id so that it doesn't change the pretty URL on an update.
				$this->request->data['url'] = $this->_generateUrl($document->_id);

				// Do not let roles or user active status to be adjusted via this method.
				if(isset($this->request->data['role'])) {
					unset($this->request->data['role']);
				}
				if(isset($this->request->data['active'])) {
					unset($this->request->data['active']);
				}

				// Profile Picture
				if(isset($this->request->data['profilePicture']['error']) && $this->request->data['profilePicture']['error'] == UPLOAD_ERR_OK) {

					$rules['profilePicture'] = array(
						array('notTooLarge', 'message' => 'Profile picture cannot be larger than 250px in either dimension.'),
						array('invalidFileType', 'message' => 'Profile picture must be a jpg, png, or gif image.')
					);

					list($width, $height) = getimagesize($this->request->data['profilePicture']['tmp_name']);
					// Check file dimensions first.
					// TODO: Maybe make this configurable.
					if($width > 250 || $height > 250) {
						$this->request->data['profilePicture'] = 'TOO_LARGE.jpg';
					} else {
						// Save file to gridFS
						$ext = substr(strrchr($this->request->data['profilePicture']['name'], '.'), 1);
						switch(strtolower($ext)) {
							case 'jpg':
							case 'jpeg':
							case 'png':
							case 'gif':
							case 'png':
								$gridFile = Asset::create(array('file' => $this->request->data['profilePicture']['tmp_name'], 'filename' => (string)uniqid(php_uname('n') . '.') . '.'.$ext, 'fileExt' => $ext));
								$gridFile->save();
							break;
							default:
								$this->request->data['profilePicture'] = 'INVALID_FILE_TYPE.jpg';
								//exit();
							break;
						}

						// If file saved, set the field to associate it (and remove the old one - gotta keep it clean).
						if (isset($gridFile) && $gridFile->_id) {
							if($existingProfilePic && substr($existingProfilePic, 0, 4) != 'http') {
								$existingProfilePicId = substr($existingProfilePic, 0, -(strlen(strrchr($existingProfilePic, '.'))));
								// Once last check...This REALLY can't be empty, otherwise it would remove ALL assets!
								if(!empty($existingProfilePicId)) {
									Asset::remove(array('_id' => $existingProfilePicId));
								}
							}
							// TODO: Maybe allow saving to disk or S3 or CloudFiles or something. Maybe.
							$this->request->data['profilePicture'] = (string)$gridFile->_id . '.' . $ext;
						} else {
							if($this->request->data['profilePicture'] != 'INVALID_FILE_TYPE.jpg') {
								$this->request->data['profilePicture'] = null;
							}
						}
					}
				} else {
					$this->request->data['profilePicture'] = null;
				}

				// Save
				if($document->save($this->request->data, array('validate' => $rules))) {
					FlashMessage::write('You have successfully updated your user settings.', 'blackprint');
					$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));
				} else {
					$this->request->data['password'] = '';
					FlashMessage::write('There was an error trying to update your user settings, please try again.', 'blackprint');
				}
			}
		}

		$this->set(compact('document'));
	}

	/**
	 * Allows the user to set their profile picture from a URL.
	 * This is a very useful method because users can then easily use
	 * their profile pictures from other sites, etc.
	 * It's separate from the update() action so it can be called
	 * from a variety of places. Namely, from libraries that make
	 * use of social media networks. This would allow a user to use
	 * their Facebook profile picture for example by going to a Facebook
	 * library of some sort.
	 *
	 * This is a JSON method, meant for use with JavaScript on the front-end.
	 *
	 * Note: The user must be logged in to do this, but this may make
	 * for a good API method in the future - allowing other apps/sites
	 * to set the user's profile picture on this one.
	 */
	public function set_profile_picture_from_url() {
		$response = array('success' => false, 'result' => null);
		if(!$this->request->is('json')) {
			return json_encode($response);
		}

		if(!$this->request->user || !isset($this->request->data['url'])) {
			return $response;
		}

		// Don't allow the URL to be used if it returns a 404.
		$ch = curl_init($this->request->data['url']);
		curl_setopt($ch,  CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($httpCode == 404) {
		    return $response;
		}

		$conditions = array('_id' => new MongoId($this->request->user['_id']));

		// Remove the existing image from the database (keep things tidy).
		$document = User::find('first', array('conditions' => $conditions));
		$existingProfilePicId = false;
		if(isset($document->profilePicture) && substr($document->profilePicture, 0, 4) != 'http') {
			$existingProfilePicId = substr($document->profilePicture, 0, -(strlen(strrchr($document->profilePicture, '.'))));
		}

		// Update the user document.
		if(User::update(
			// query
			array(
				'$set' => array(
					'profilePicture' => $this->request->data['url']
				)
			),
			$conditions,
			array('atomic' => false)
		)) {
			// A final check to ensure there actually is an id.
			if(!empty($existingProfilePicId)) {
				Asset::remove(array('_id' => $existingProfilePicId));
			}
			$response = array('success' => true, 'result' => $this->request->data['url']);
		}

		return $response;
	}

	/**
	 * Public view action, for user profiles and such.
	 *
	 * @param $url The user's pretty URL
	 */
	public function read($url=null) {
		$conditions = array('url' => $url);

		/**
		 * If nothing is passed, get the currently logged in user's profile.
		 * This is safer to use for logged in users, because if they update
		 * their profile and change their name...The pretty URL changes.
		*/
		if(empty($url) && isset($this->request->user)) {
			$conditions = array('_id' => $this->request->user['_id']);
		}
		$user = User::find('first', array('conditions' => $conditions));

		if(empty($user)) {
			FlashMessage::write('Sorry, that user does not exist.', 'blackprint');
			return $this->redirect('/');
		}

		/**
		 * Protect the password in case changes are made where this action
		 * could be called with a handler like JSON or XML, etc. This way,
		 * even if the user document is returned, it won't contain any
		 * sensitive password information. Not even the _id.
		 */
		$user->set(array('password' => null, '_id' => null));

		$this->set(compact('user'));
	}
}
?>