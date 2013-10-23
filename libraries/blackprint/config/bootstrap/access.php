<?php
/**
 * This will check basic access on all requests.
 * 
 * The filter set below is designed to just protect admin actions.
 * Additional rules and checks may need to be made based on the
 * requirements of the application.
 * 
*/
use lithium\action\Dispatcher;
use lithium\net\http\Router;
use lithium\action\Response;
use lithium\security\Auth;
use lithium\core\Libraries;
use lithium\storage\Session;

use li3_access\security\Access;
use li3_flash_message\extensions\storage\FlashMessage;

// Adding the library here if it hasn't already been added.
if(!class_exists('li3_access\security\Access')) {
	Libraries::add('li3_access');
}

Dispatcher::applyFilter('_callable', function($self, $params, $chain) {
	// Run other filters first. This allows this one to not exactly be overwritten or excluded...But it does allow for a different login action to be used...
	// TODO: Perhaps allow this to be skipped...
	$next = $chain->next($self, $params, $chain);
	

	$request = $params['request'];
	$action = $request->action;
	$user = Auth::check('blackprint');

	// Special role for new visitors logging in and registering using OAuth from some other supported service such as Twitter or Facebook.
	// Or, catch no role at all.
	if($user && ((isset($user['role']) && $user['role'] == 'new_user') || !isset($user['role'])) && $action != 'register' && $action != 'logout') {
		Session::write('blackprintAccessMessage', 'Please complete your registration.', array('name' => 'cookie'));
		header('Location: ' . Router::match(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'register')));
		exit();
	}

	// Clear any temporary session data from the OAuth process (there would be cookies like "blackprint_twitter" otherwise and that's not needed after the user registers).
	if(isset($user['socialLoginService'])) {
		Session::delete('blackprint_' . $user['socialLoginService']);
	}

	// This is a little bit hacky. FlashMessage storage seems to have an issue in our case here.
	$accessFlash = Session::read('blackprintAccessMessage', array('name' => 'cookie'));
	if($accessFlash) {
		FlashMessage::write($accessFlash, 'blackprint');
		Session::delete('blackprintAccessMessage');
	}	

	// Protect all admin methods except for login and logout.
	if($request->admin === true && $action != 'login' && $action != 'logout') {
		$actionAccess = Access::check('blackprint', $user, $request, array('rules' => array('allowManagers')));
		if(!empty($actionAccess)) {
			// NOTE & TODO: For some reason this doesn't work here with MongoDB based session storage...
			// It seems to store, you can get the data back out immediately, but the redirects seem to remove the session data.
			// However, using the Session class straight up seems to keep the data as expected. Maybe a bug or edge case with FlashMessage?
			// In fact, I'm going to use Cookie instead. No sense in writing messages to the database for everyone.
			// FlashMessage::write($actionAccess['message'], 'blackprint');
			Session::write('blackprintAccessMessage', $actionAccess['message'], array('name' => 'cookie'));
			if($user) {
				//$location = Router::match($actionAccess['redirect']);
				//return new Response(compact('location', 'request'));
				header('Location: ' . Router::match($actionAccess['redirect']));
			} else {
				// NOTE: Returning Response() would work if filtering the '_call' or 'run' method on the Dispatcher.
				// I wanted to filter '_callable' for some reason or another...
				// TODO: Look into that and maybe why.
				// I believe it may have been done for execution order - this may be the earliest check available for access.
				// Which would be better for performance (in theory) and security (or at least feels better for security).
				// While "ugly" because it's not using Lithium's facilities, header() is a faster call. So maybe leave it.

				//$location = array('library' => 'blackprint', 'controller' => 'users', 'action' => 'login');
				//return new Response(compact('location', 'request'));
				header('Location: ' . Router::match(array('library' => 'blackprint', 'controller' => 'users', 'action' => 'login')));
			}
		// None shall pass.
			exit();
		}
	}
	
	// Sets the current user in each request for convenience.
	$params['request']->user = $user;
	
	return $next;
	// return $chain->next($self, $params, $chain);
});

Access::config(array(
	'blackprint' => array(
			'adapter' => 'Rules',
			// optional filters applied for each configuration
			'filters' => array(
				/*function($self, $params, $chain) {
					// Any config can have filters that get applied
					exit();
					return $chain->next($self, $params, $chain);
				}*/
			)
	)
));

// Set some basic rules to be used from anywhere

// Allow access for users with a role of "administrator" or "content_editor"
Access::adapter('blackprint')->add('allowManagers', function($user, $request, $options) {
	if(($user) && ($user['role'] == 'administrator' || $user['role'] == 'content_editor')) {
		return true;
	}
	return false;
});

// Restrict access to documents that have a published field marked as true 
// (except for users with a role of "administrator" or "content_editor")
Access::adapter('blackprint')->add('allowIfPublished', function($user, $request, $options) {
	if(isset($options['document']['published']) && $options['document']['published'] === true) {
		return true;
	}
	if(($user) && ($user['role'] == 'administrator' || $user['role'] == 'content_editor')) {
		return true;
	}
	return false;
});
?>