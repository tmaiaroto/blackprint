<?php
use lithium\security\Auth;

Auth::config(array(
	'blackprint' => array(
		'adapter' => 'Form',
		'model'  => '\blackprint\models\User',
		'fields' => array('email', 'password'),
		'scope'  => array('active' => true),
		/*'filters' => array(
		//'password' => 'app\models\User::hashPassword'
		),*/
		'session' => array(
			'options' => array('name' => 'default')
		)
	)
));

?>