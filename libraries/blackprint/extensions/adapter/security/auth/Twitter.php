<?php
namespace blackprint\extensions\adapter\security\auth;

use blackprint\extensions\oauth\storage\BlackprintTokenStorage;
use OAuth\OAuth1\Signature\Signature;
use OAuth\Common\Consumer\Credentials;
use OAuth\ServiceFactory;

/**
 * Twitter authentication.
 */
class Twitter extends \lithium\core\Object {

	/**
	 * Session storage.
	 *
	 * @var [type]
	 */
	protected $_storage ;

	/**
	 * Constructor
	 *
	 * @param array $config [description]
	 */
	public function __construct(array $config = array()) {
		$config += array('tokenKey' => 'oauth_token');
		$this->_storage = new BlackprintTokenStorage(false, $config['tokenKey']);
		parent::__construct($config) ;
	}

	/**
	 * Check the authentication. Will be called two times : first to request a token, and redirect
	 * to the Twitter api url, then to authenticate the user.
	 *
	 * @param  [type] $request [description]
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	public function check($request, array $options = array()) {
		$credentials = new Credentials(
		    $this->_config['key'],
		    $this->_config['secret'],
		    $request->to('url')
		);

		$serviceFactory = new ServiceFactory();
		$service = $serviceFactory->createService('twitter', $credentials, $this->_storage);

		if (!empty($request->query['denied'])) {
			return false;
		} elseif (empty($request->query['oauth_token'])) {
			$token = $service->requestRequestToken();
		    $url = $service->getAuthorizationUri(array('oauth_token' => $token->getRequestToken()));
		    header('Location: ' . $url);
		} else {
			$token = $this->_storage->retrieveAccessToken('Twitter');
		    $service->requestAccessToken(
		    	$request->query['oauth_token'],
		    	$request->query['oauth_verifier'],
		    	$token->getRequestTokenSecret()
		    );

		    $credentials = json_decode($service->request( 'account/verify_credentials.json'), true);
		    // var_dump($credentials);exit();
		    $profilePicture = $request->env('HTTPS') ? $credentials['profile_image_url_https']:$credentials['profile_image_url'];
		    $profilePicture = str_replace('_normal', '_bigger', $profilePicture); // Use the larger version. Still not perfect. The original version is available but it could be a bit large and for now, let's not crop here.
		    $result = array(
		    	'socialLogin' => array(
		    		// The 'service' needs to match the adapter name (after the blackprint_ prefix)
		    		'service' => 'twitter',
		    		// The 'serviceName' is the formatted name users see, it can contain spaces, etc.
		    		'serviceName' => 'Twitter',
		    		// Some extra data about the service (used for visuals and external links in the CMS)
		    		// If no icon is provided, the CMS will use the favicon from the serviceUrl
		    		'serviceIcon' => '<i class="fa fa-twitter"></i>',
		    		'serviceUrl' => 'http://www.twitter.com',
		    		// These values will all be different based on the service...But standardize "userId", "name", and "userName" for use in
		    		// new user registration OR in user lookup for already registered users.
		    		'userId' => $credentials['id_str'],
		    		'name' => $credentials['name'],
		    		// Even if not available, the timezone, utcOffset, and locale fields should always be set (null is ok).
		    		'timezone' => $credentials['time_zone'],
		    		'utcOffset' => $credentials['utc_offset'],
		    		'locale' => $credentials['lang'],
		    		'userName' => $credentials['screen_name'],
		    		'profilePicture' => $profilePicture,
		    		// Last, but not least, store the token so we can make API calls on the user's behalf.
		    		// NOTE: It has to be serialized because MongoDB doesn't like the OAuth class objects.
		    		// Also note that upon each login the tokens stored on the User document (for each associated service)
		    		// should be checked and refreshed if necessary. They can expire.
		    		'token' => serialize($token)
		    	)
		    );

		    return $result;
		}

	}

	/**
	 * Prepare the data to be stored.
	 *
	 * @param [type] $data    [description]
	 * @param array  $options [description]
	 */
	public function set($data, array $options = array()) {
		return $data;
	}

	/**
	 * Clear the token session key.
	 *
	 * @param  array  $options [description]
	 * @return [type]          [description]
	 */
	public function clear(array $options = array()) {
		$this->_storage->clearToken('twitter');
	}

}
?>