<?php
/**
 * This will check basic access on all requests.
 * 
 * The filter set below is designed to just protect admin actions.
 * Additional rules and checks may need to be made based on the
 * requirements of the application.
 * 
*/
use blackprint\extensions\storage\FlashMessage;
use li3_access\security\Access;

use lithium\action\Dispatcher;
use lithium\net\http\Router;
use lithium\action\Response;
use lithium\security\Auth;
use lithium\core\Libraries;
use lithium\storage\Session;

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

	// Protect all admin methods except for login and logout.
	if($request->admin === true && $action != 'login' && $action != 'logout') {
		$actionAccess = Access::check('blackprint', $user, $request, array('rules' => array('allowManagers')));
		if(!empty($actionAccess)) {
			FlashMessage::write($actionAccess['message']);
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

$config = Access::config();
$config += array(
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
);
Access::config($config);

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