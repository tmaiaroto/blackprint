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
			'options' => array('name' => 'blackprint')
		)
	)
));

/**
 * Third party authentication services.
*/
if(isset($blackprintConfig['externalAuthServices']) && is_array($blackprintConfig['externalAuthServices'])) {
	$authConfig = Auth::config();
	$externalAuthConfig = array();

	foreach($blackprintConfig['externalAuthServices'] as $service => $value) {
		$serviceConfig = array('adapter' => (isset($value['adapter'])) ? $value['adapter']:ucfirst($service));
		$serviceConfig += $value;
		// This has a defult value in the adapter. Don't send an empty value that would override that.
		if(isset($serviceConfig['scope'])) {
			if(empty($serviceConfig['scope'])) {
				unset($serviceConfig['scope']);
			} else {
				$serviceConfig['scope'] = explode(',', $serviceConfig['scope']);
			}
		}
		$externalAuthConfig['blackprint_' . $service] = $serviceConfig;
	}

	$completeAuthConfig = $authConfig += $externalAuthConfig;
	Auth::config($completeAuthConfig);
}
?>