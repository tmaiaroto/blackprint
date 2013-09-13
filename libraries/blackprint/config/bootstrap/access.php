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
	
	// Protect all admin methods except for login and logout.
	if($request->admin === true && $action != 'login' && $action != 'logout') {
		$action_access = Access::check('default', $user, $request, array('rules' => array('allowManagers')));
		if(!empty($action_access)) {
			FlashMessage::write($action_access['message'], 'default');
			if($user) {
				header('Location: ' . Router::match($action_access['redirect']));
			} else {
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
	'default' => array(
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
Access::adapter('default')->add('allowManagers', function($user, $request, $options) {
	if(($user) && ($user['role'] == 'administrator' || $user['role'] == 'content_editor')) {
		return true;
	}
	return false;
});

// Restrict access to documents that have a published field marked as true 
// (except for users with a role of "administrator" or "content_editor")
Access::adapter('default')->add('allowIfPublished', function($user, $request, $options) {
	if(isset($options['document']['published']) && $options['document']['published'] === true) {
		return true;
	}
	if(($user) && ($user['role'] == 'administrator' || $user['role'] == 'content_editor')) {
		return true;
	}
	return false;
});
?>