<?php
namespace blackprint\controllers;

use blackprint\models\User;
use blackprint\models\Asset;
use blackprint\models\Config;
use blackprint\extensions\Email;
use blackprint\extensions\Util;
use blackprint\extensions\storage\FlashMessage;
use li3_access\security\Access;
use lithium\security\validation\RequestToken;
use lithium\security\Auth;
use lithium\storage\Session;
use lithium\security\Password;
use lithium\util\Set;
use lithium\util\String;
use lithium\util\Inflector;
use lithium\net\http\Router;
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
			$search_schema = User::$searchSchema;
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
					FlashMessage::write('The user has been created successfully.');
					$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
				} else {
					$this->request->data['password'] = '';
					FlashMessage::write('The user could not be created, please try again.');
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
			FlashMessage::write('That user was not found.');
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
					FlashMessage::write('The user has been updated successfully.');
					$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
				} else {
					$this->request->data['password'] = '';
					FlashMessage::write('The user could not be updated, please try again.');
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
			FlashMessage::write('That user was not found.');
			return $this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'index', 'admin' => true));
		}

		if($this->request->user['_id'] != (string) $document->_id) {
			if($document->delete()) {
				FlashMessage::write('The user has been deleted.');
			} else {
				FlashMessage::write('The user could not be deleted, please try again.');
			}
		} else {
			FlashMessage::write('You can\'t delete yourself!');
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
		// For users logging in, for the first time, via some external service such as Twitter or Facebook.
		// Or the first time with the external service.
		$externalRegistration = Session::read('externalRegistration', array('name' => 'blackprint'));

		// Don't let a logged in user register again. That would be silly.
		if(isset($this->request->user) && !empty($this->request->user)) {
			// Unless they are linking a new external OAuth service.
			if(isset($this->request->user) && !empty($this->request->user)) {
				$document = User::find('first', array('conditions' => array('_id' => $this->request->user['_id'])));
				if(!empty($document)) {
					$updateData = array(
						'modified' => new MongoDate()
					);
					$docData = $document->data();
					if(!empty($document->externalAuthServices)) {
						$updateData['externalAuthServices'] = $document->externalAuthServices->data();
					}
					// Double check to make sure we have the 'service' key (if not, the auth adapter didn't pass it along, which would be an error in the adapter).
					if(isset($externalRegistration['service'])) {
						$updateData['externalAuthServices'][$externalRegistration['service']] = $externalRegistration;
						if($document->save($updateData, array('validate' => false))) {
							FlashMessage::write('You have successfully linked your ' . $externalRegistration['serviceName'] . ' account.');
							unset($docData['password']); // Don't set this in the Auth session data.
							Auth::set('blackprint', $updateData += $docData);
							return $this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));
						}
					}
				}
			}

			return $this->redirect('/');
		}

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

		// Set any basic data from the service that Blackprint may want to be displayed in the registration form.
		// Note: By having this registration process, the user must click "submit" after reviewing the information pulled
		// from the external service. This satisfies the TOS for pretty much all of these services as far as I can tell.
		// However, automatically storing the information without the user's consent is problematic.
		// It is a shame that the user experience is "register" OR "login with xxxxx" while both take the user to register.
		// Though it's important in the event a user decides to login using Twitter one day and then Facebook the next.
		// This way, we can hit them with a registration again, they'll hopefully use the same e-mail and password and
		// we can associate their new social media account with the same User document.
		/*
		if(!empty($externalRegistration)) {
			if(!$this->request->data) {
				$assumedName = explode(' ', $externalRegistration['name']);
				$document->firstName = isset($assumedName[0]) ? $assumedName[0]:null;
				$document->lastName = isset($assumedName[1]) ? $assumedName[1]:null;
			}
		}
		*/

		// Save
		if ($this->request->data) {
			// CSRF
			if(!RequestToken::check($this->request)) {
				RequestToken::get(array('regenerate' => true));
			} else {
				$now = new MongoDate();

				// If the user is registering a new third party service to their existing account.
				// Let them "register" but really it could be an update. So when setting data below,
				// look for it from the $document first.
				if(!empty($externalRegistration) && isset($externalRegistration['service'])) {
					$service = $externalRegistration['service'];
					unset($externalRegistration['service']);
					
					// All stuff that the user is not asked upon registration anyway. These settings can be changed later under "/my-account"
					$this->request->data['utcOffset'] = (isset($externalRegistration['utcOffset']) && !empty($externalRegistration['utcOffset'])) ? $externalRegistration['utcOffset']:null;
					$this->request->data['timezone'] = (isset($externalRegistration['timezone']) && !empty($externalRegistration['timezone'])) ? $externalRegistration['timezone']:null;
					$this->request->data['locale'] = (isset($externalRegistration['locale']) && !empty($externalRegistration['locale'])) ? $externalRegistration['locale']:null;
					$this->request->data['profilePicture'] = (isset($externalRegistration['profilePicture']) && !empty($externalRegistration['profilePicture'])) ? $externalRegistration['profilePicture']:null;

					// Just keeping form data straight. While we could use "email" and "password" to login from the form in the template...
					// The form fields appear twice on the page. So by using completely different form field names, we avoid any possibility
					// of passing along incorrect or empty information due to multiple fields with the same name. Alternatively, the JavaScript
					// in the register template could change to completely remove form fields...But let's do it here to cover all the bases.
					// Maybe a template override will work differently. This provides a consistent "emailLogin" and "passwordLogin" convention
					// for this particular scenario.
					if(isset($this->request->data['emailLogin']) && !empty($this->request->data['emailLogin'])) {
						$this->request->data['email'] = $this->request->data['emailLogin'];
						unset($this->request->data['emailLogin']); // don't technically need to unset, model schema won't allow save and it shouldn't affect Auth:check()
					}
					if(isset($this->request->data['passwordLogin']) && !empty($this->request->data['passwordLogin'])) {
						$this->request->data['password'] = $this->request->data['passwordLogin'];
						unset($this->request->data['passwordLogin']);
					}
					$existingUser = Auth::check('blackprint', $this->request);
					if($existingUser) {
						$document = User::find('first', array('conditions' => array('_id' => $existingUser['_id'])));
					}
					// Of course if not found, create a new document.
					if(empty($document)) {
						$document = User::create();
					} else {
						// It is possible the user is linking a new 3rd party account and did not enter their name in again.
						// Why make them do that if they already have an account? In that case, unset the values from the form,
						// which are empty, so the user doesn't clear out their name upon save.
						if(!empty($document->firstName) && empty($this->request->data['firstName'])) {
							unset($this->request->data['firstName']);
						}
						if(!empty($document->lastName) && empty($this->request->data['lastName'])) {
							unset($this->request->data['lastName']);
						}
					}

					// For existing accounts, ensure we append to this field and not overwrite it.
					if(!empty($document->externalAuthServices)) {
						$this->request->data['externalAuthServices'] = $document->externalAuthServices->data();
					}
					$this->request->data['externalAuthServices'][$service] = $externalRegistration;
				}

				$this->request->data['created'] = (!empty($document->created)) ? $document->created:$now;
				$this->request->data['active'] = (!empty($document->active)) ? $document->active:true;
				$this->request->data['modified'] = $now;

				// Set the pretty URL that gets used by a lot of front-end actions.
				$this->request->data['url'] = (!empty($document->_id)) ? $this->_generateUrl($document->_id):$this->_generateUrl();

				// Set the user's role...always hard coded and set to "registered_user" when using this action
				// to register a NEW user. Otherwise, leave it set to whatever it was.
				$this->request->data['role'] = (!empty($document->role)) ? $document->role:'registered_user';

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
					FlashMessage::write('User registration successful.');
					// Delete this session data that was used during registration.
					Session::delete('externalRegistration', array('name' => 'blackprint'));
					// Not set on $this->request->data of course, but needed by authentation and this controller at various points.
					$this->request->data['_id'] = $document->_id;
					// Let everything looking at Auth data know which service the user linked when registering.
					if(isset($service)) {
						$this->request->data['socialLoginService'] = $service;
					}
					$existingDocData = array();
					if(!empty($document)) {
						$existingDocData = $document->data();
					}
					$user = Auth::set('blackprint', $this->request->data += $existingDocData);
					// Redirect URL after registering is always to the update action. Users can fill out more details of their profile there.
					$url = array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update');
					// ...watch for admin users
					if($user['role'] == 'administrator') {
						$url = array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update', 'admin' => true);
					}
					if($externalRegistration) {
						FlashMessage::write('You have successfully linked your ' . $externalRegistration['serviceName'] . ' account.');
					}
					$this->redirect($url);
				} else {
					$this->request->data['password'] = '';
				}
			}
		}

		$this->set(compact('document', 'externalRegistration'));
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
	public function login($service=null) {
		if(empty($service)) {
			// All users ultimately have to have a User document.
			$user = Auth::check('blackprint', $this->request);
		} else {
			// But they can login using certain OAuth services like Twitter, Facebook, etc.
			// Note: The service configuration name will be prefixed by "blackprint_" though we don't want
			// that to be shown in the URL of course (and it's also not stored in the database).
			// It's also going to be lowercase. Always. Since a configuration name of 'twitter' is going
			// to be fairly common and perhaps even used by a third party add-on or something, prefix
			// it to ensure there's no configuration naming conflicts.
			$service = strtolower($service);
			$serviceConfigName = 'blackprint_' . $service;
			
			// Of course, exceptions will be thrown if the configuration is not available. So check for it first.
			$configurations = Auth::config();
			if(!isset($configurations[$serviceConfigName])) {
				return $this->redirect('/');
			}
			// Check against a user who is potentially already logged in.
			$currentlyLoggedInUser = isset($this->request->user) && !empty($this->request->user)  ? $this->request->user:false;

			$user = Auth::check($serviceConfigName, $this->request);

			// If so we get back less data (vs. a User document lookup), but that's ok.
			// We use what we can get back from the service to lookup locally and automatically
			// set authentication because we choose to trust these services in this case.
			if($user && isset($user['socialLogin'])) {
				// Of course, $user['socialLogin']['userId'] must be unique PER service.
				$externalUserId = isset($user['socialLogin']['userId']) ? $user['socialLogin']['userId']:false;
				$userDocument = User::find('first', array('conditions' => array('externalAuthServices.' . $service . '.userId' => $externalUserId)));
				if(!empty($userDocument)) {
					// Check to ensure the User document that has already linked the service is the same as the currently logged in user (if the user is currently logged in)
					// This will be the case in situations where a user links an external service to another local account. We can't link to more than one because
					// we wouldn't know which to use when loggined in using the service in the future if there were multiple User documents matching the conditions.
					if($currentlyLoggedInUser) {
						if($currentlyLoggedInUser['_id'] !== (string)$userDocument->_id) {
							FlashMessage::write('Another user already linked this service and only one user can link a third party service at a time.');
							$url = array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update');
							if($currentlyLoggedInUser['role'] == 'administrator') {
								$url = '/admin/my-account';
							}
							return $this->redirect($url);
						}
					}

					$userData = $userDocument->data();
					// Don't set the user's password in the Auth data.
					unset($userData['password']);
					// Do set the service name that authenticated the user though. This lets anything looking at Auth data know how the user chose to log in this time.
					$userData['socialLoginService'] = $service;
					$user = Auth::set('blackprint', $userData);
				} else {
					// If the user hasn't fully registered yet (or it's a new OAuth service being linked), send them to do that now.
					// Of course let's hang on to some of this data from the external OAuth service so they can login in the future.
					Session::write('externalRegistration', $user['socialLogin'], array('name' => 'blackprint'));
					return $this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register'));
				}
			}
		}

		// 'triedAuthRedirect' so we don't end up in a redirect loop
		if (!Session::check('triedAuthRedirect', array('name' => 'cookie'))) {
			Session::write('triedAuthRedirect', 'false', array('name' => 'cookie', 'expires' => '+1 hour'));
		}

		if ($user) {
			// Users will be redirected after logging in, but where to?
			$url = $this->_getUserLoginRedirect($user);

			// Save last login IP and time
			$userDocument = User::find('first', array('conditions' => array('_id' => $user['_id'])));

			if($userDocument) {
				$userDocument->save(array('lastLoginIp' => Util::visitorIp(), 'lastLoginTime' => new MongoDate()));
			}

			// only set a flash message if this is a login. it could be a redirect from somewhere else that has restricted access
			// $flashMessage = FlashMessage::read();
			// if(empty($flashMessage)) {
				FlashMessage::write('You\'ve successfully logged in.');
			// }
			$this->redirect($url);
		} else {
			if($this->request->data) {
				FlashMessage::write('You entered an incorrect username and/or password.');
			}
		}
		$data = $this->request->data;

		// SMTP e-mail must be setup for users to reset their passwords.
		$canResetPassword = Email::isAvailable('blackprint_smtp');

		// Get all external auth services.
		$config = Config::get('default');
		$externalAuthServices = array();
		if(isset($config['externalAuthServices']) && !empty($config['externalAuthServices'])) {
			foreach($config['externalAuthServices'] as $service => $v) {
				if(!empty($v['key'])) {
					$externalAuthServices[$service] = array('name' => $v['name'], 'logo' => $v['logo']);
				}
			}
		}


		return compact('data', 'canResetPassword', 'externalAuthServices');
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
		// Clear all Auth configurations.
		$configurations = Auth::config();
		foreach($configurations as $name => $config) {
			// Prefixing the Auth config names with "blackprint_" also helps here.
			// Now, only Blackprint Auth will be cleared upon logout. Other libraries
			// using the Auth class will need to clear their own sessions and this logout()
			// action won't clear any session data it shouldn't.
			// NOTE: The file and class names under /libraries/blackprint/extensions/adapter/security/auth/... are case sensitive.
			// The Auth::config() must specify an 'adapter' value which by convention will be Firstlettercapital only.
			if(strstr($name, 'blackprint_')) {
				try {
					Auth::clear($name);
				} catch(\Exception $e) {
					// var_dump($e);exit();
				}
			}
		}
		FlashMessage::write('You\'ve successfully logged out.');
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
		$url_field = User::$urlField;
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
			FlashMessage::write('You must be logged in to do that.');
			return $this->redirect('/');
		}

		// Special render case. Allow admin users to update their own profile from the admin layout.
		// Since the admin_update() method is for updating OTHER users...We still use this method.
		// We can't, of course, use 'admin' in the route for this method, so that's part of why we have
		// the short and friendly "my-account" and "admin/my-account" routes.
		if(strstr($this->request->url, 'admin')) {
			$this->_render['layout'] = 'admin';
			// We  can use this same cookie here too in order to redirect to the admin layout.
			Session::write('beforeAuthURL', '/admin/my-account', array('name' => 'cookie'));
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
			FlashMessage::write('You must be logged in to do that.');
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
					$this->request->data['profilePicture'] = $document->profilePicture;
				}

				// Save
				if($document->save($this->request->data, array('validate' => $rules))) {
					FlashMessage::write('You have successfully updated your user settings.');
					$url = array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update');
					if(Session::check('beforeAuthURL', array('name' => 'cookie'))) {
						$url = Session::read('beforeAuthURL', array('name' => 'cookie'));
						Session::delete('beforeAuthURL', array('name' => 'cookie'));
					}
					$this->redirect($url);
				} else {
					$this->request->data['password'] = '';
					FlashMessage::write('There was an error trying to update your user settings, please try again.');
				}
			}
		}

		// Get all currently available third party authentication services.
		$config = Config::get('default');
		$externalAuthServices = array();
		if(isset($config['externalAuthServices']) && !empty($config['externalAuthServices'])) {
			foreach($config['externalAuthServices'] as $service => $v) {
				if(!empty($v['key'])) {
					$externalAuthServices[$service] = array('name' => $v['name'], 'logo' => $v['logo']);
				}
			}
		}

		$this->set(compact('document', 'externalAuthServices'));
	}

	/**
	 * Allows users to recover their lost password, by resetting it,
	 * via an e-mail link.
	 *
	 * NOTE: This method will only be available if the server can
	 * send e-mails an SMTP server was setup in the configuration.
	*/
	public function reset_password($email=null, $resetCode=null) {
		// The admin must have provided SMTP credentials in the system configuration.
		if(!Email::isAvailable('blackprint_smtp')) {
			return $this->redirect('/');
		}

		$document = null;

		// If using a reset code.
		if(!empty($resetCode) && !empty($email)) {
			$now = new MongoDate();
			$document = User::find('first', array('conditions' => array('email' => $email, 'resetCode' => $resetCode)));
			if(empty($document)) {
				FlashMessage::write('Invalid e-mail address or reset code.');
				return $this->redirect('/forgot-password');
			}

			if(!empty($document->resetExpiration) && !empty($document->resetExpiration->sec)) {
				if($document->resetExpiration->sec < $now->sec) {
					FlashMessage::write('For security purposes, that reset request has expired. Please request another password reset.');
					return $this->redirect('/forgot-password');
				}
			}

			if($document && $this->request->data) {
				// Validation rules.
				$rules = array(
					'email' => array(
						array('notEmpty', 'message' => 'E-mail cannot be empty.'),
						array('email', 'message' => 'E-mail is not valid.')
					)
				);

				$this->request->data['modified'] = $now;

				// Add validation rules for the password IF the password and passwordConfirm field were passed
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

				// Ditch the reset code. It's a single use thing.
				$this->request->data['resetCode'] = null;

				if($document->save($this->request->data, array('validate' => $rules))) {
					FlashMessage::write('You have successfully reset your password.');
					$docData = $document->data();
					unset($docData['password']);
					Auth::set('blackprint', $this->request->data += $docData);

					if($document->role == 'administrator') {
						return $this->redirect('/admin/my-account');	
					}
					return $this->redirect('/my-account');
				}
			}
		}

		$config = Config::get('default');
		$siteName = isset($config['siteName']) && !empty($config['siteName']) ? $config['siteName']:$_SERVER['HTTP_HOST'];

		// If requesting a reset code.
		if($this->request->data && empty($resetCode)) {
			if(!empty($this->request->data['email'])) {
				// Get the User document so that it can be adjusted to allow a password reset.
				$userDocument = User::find('first', array('conditions' => array('email' => $this->request->data['email'])));
				if($userDocument) {
					// Anti-spam. Check to see if the User document already had an open request. Users should be able to make more than one
					// request to reset their password, but only within a certain period of time. Otherwise, it could lead to abuse or waste.
					if(isset($userDocument->resetRequestedDate) && !empty($userDocument->resetRequestedDate)) {
						if(strtotime('-5 minutes') < $userDocument->resetRequestedDate->sec === true) {
							FlashMessage::write('Please check your e-mail for further instructions. It may take a few minutes to receive the e-mail.');
							return $this->redirect('/');
						}
					}

					$resetData = array(
						'resetCode' => String::uuid(),
						// Users will have three days to reset their password before needing to request a new reset. Should be plenty of time.
						// This helps protect against any sort of brute force attempts to guess the resetCode which is pretty hard to guess anyway.
						'resetExpiration' => new MongoDate(strtotime(('+3 days'))),
						// For security auditing purposes...May be useful in determining if there is a malicious user.
						'resetRequests' => is_int($userDocument->resetRequests) ? ($userDocument->resetRequests + 1):1,
						'resetRequestedFromIp' => Util::visitorIp(),
						'resetRequestedDate' => new MongoDate()
					);

					$resetLink = Util::siteAddress(true) . '/' . $this->request->data['email'] . '/' . $resetData['resetCode'];

					if($userDocument->save($resetData, array('validate' => false))) {
						$userName = $userDocument->firstName . ' ' . $userDocument->lastName;
						$userName = trim($userName);
						if(empty($userName)) {
							$userName = 'Hello';
						}
						try {
							if(Email::send(array(
								'to' => array($userDocument->email => $userName),
								'subject' => 'Password Reset Requested',
								'body' => '<html><p>' . $userName . ',</p><p>You, or someone else, has requested that your password be reset on ' . $siteName . '. If you did not make this request, you can safely ignore this message. If you did want to reset your password, simply follow this link:</p><p><pre>' . $resetLink . '</pre></p></html>'
							))) {
								FlashMessage::write('Please check your e-mail for further instructions.');
								return $this->redirect('/');
							}
						} catch(\Exception $error) {
							FlashMessage::write('Sorry, something went wrong, please try again.');
							return $this->redirect(Util::siteAddress(true));
						}
					}
				} else {
					return $this->redirect('/forgot-password');
				}
			}
		}

		$this->set(compact('document'));
	}

	/**
	 * Allows a user to revoke access to a third party OAuth service.
	 * This would also prevent them from logging in using that service.
	*/
	public function revoke_service($service=null) {
		if(!$this->request->user || empty($service)) {
			FlashMessage::write('You must be logged in to do that.');
			return $this->redirect('/');
		}

		$service = strtolower($service);
		$serviceName = $service;
		$document = User::find('first', array('conditions' => array('_id' => $this->request->user['_id'])));
		if(!empty($document)) {
			if(!empty($document->externalAuthServices)) {
				$updateData = array(
					'modified' => new MongoDate()
				);
				$updateData['externalAuthServices'] = $document->externalAuthServices->data();
				foreach($updateData['externalAuthServices'] as $k => $v) {
					if($k == $service) {
						$serviceName = isset($updateData['externalAuthServices'][$k]['serviceName']) ? $updateData['externalAuthServices'][$k]['serviceName']:$serviceName;
						unset($updateData['externalAuthServices'][$k]);
					}
				}

				if($document->save($updateData, array('validate' => false))) {
					$docData = $document->data();
					unset($docData['password']);
					Auth::set('blackprint', $updateData += $docData);
					FlashMessage::write('You have revoked access to ' . $serviceName . '.');
					$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));
				}
			}
		}
		FlashMessage::write('Invalid service or there was a problem revoking access, please try again.');
		$this->redirect(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'update'));
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

		$url = isset($this->request->data['url']) ? $this->request->data['url']:false;
		$url = isset($this->request->query['url']) ? $this->request->query['url']:$url;

		if(!$this->request->user || !$url) {
			return $response;
		}

		// Don't allow the URL to be used if it returns a 404.
		$ch = curl_init($url);
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
					'profilePicture' => $url
				)
			),
			$conditions,
			array('atomic' => false)
		)) {
			// A final check to ensure there actually is an id.
			if(!empty($existingProfilePicId)) {
				Asset::remove(array('_id' => $existingProfilePicId));
			}
			$response = array('success' => true, 'result' => $url);
		}

		return $response;
	}

	/**
	 * Figures out where to redirect users based on various conditions.
	 * TODO: Allow CMS configuration options to set these as well...
	*/
	public function _getUserLoginRedirect($user=null) {
		$url = '/';

		if(!empty($user)) {
			// Default redirects for certain user roles
			switch($user['role']) {
				case 'administrator':
				case 'content_editor':
					$url = '/admin';
					break;
				case 'new_user':
					$url = '/register';
					break;
				default:
					$url = '/';
					break;
			}
		}

		// Look to see if a cookie was set. The could have ended up at the login page
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

		return $url;
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
			FlashMessage::write('Sorry, that user does not exist.');
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