<?php
/**
 * Generic e-mail utility class that allows messages to be sent.
 *
 */
namespace blackprint\extensions;

use blackprint\models\Config;
use li3_swiftmailer\mailer\Transports;
use li3_swiftmailer\mailer\Message;

class Email extends \lithium\core\StaticObject {

	/**
	 * Checks if an adapter configuration is available, by name.
	 *
	 * @param string $name
	 * @return boolean
	*/
	public static function isAvailable($name=null) {
		if(empty($name)) {
			return false;
		}

		$emailConfigurations = Transports::config();
		foreach($emailConfigurations as $configName => $value) {
			if($name == $configName) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Sends messages via e-mail. SMTP by default using the `blackprint_smtp` configuration,
	 * which will be set if the admin has provided credentials in the system configuration.
	 * Alternative transport adapters may be specified, but they must use SwiftMailer's
	 * Message class to send the message.
	 *
	 * @param array $message
	 * 		- to: Array list of e-mail addresses, ex. array('email@site.com', ;email2@site.com;) or array('email@site.com' => 'name', 'email2@site.com' => 'name2')
	 *      - from: From address in array or string format, ex. array('email@site.com' => 'Name') or 'email@site.com'
	 *      - subject: The subject of the e-mail
	 *      - body: The body copy for the e-mail
	 * @param array $options Various options for sending the e-mail.
	 *		- adapter: Default is `blackprint_smtp`
	 *		- format: The e-mail message format, HTML or plain text, ex. 'text/html' or 'text/plain' or 'text' or 'html'
	 * @return boolean
	*/
	public static function send($message=array(), $options=array()) {
		if(empty($message) || !is_array($message)) {
			return false;
		}
		// Use `blackprint_smtp` by default to send e-mail messages.
		$options += array(
			'adapter' => 'blackprint_smtp',
			'format' => 'text/html'
		);
		// But try to send the e-mail via PHP's mail() function if possible.
		if(!self::isAvailable($options['adapter'])) {
			$options['adapter'] = 'blackprint_mail';
		}

		// Shorthand options
		if($options['format'] == 'text') {
			$options['format'] = 'text/plain';
		}
		if($options['format'] == 'html') {
			$options['format'] = 'text/html';
		}

		$systemConfig = Config::get('default');
		$adminEmail = isset($systemConfig['adminEmail']) ? $systemConfig['adminEmail']:'admin@' . $_SERVER['HTTP_HOST'];
		$adminName =  isset($systemConfig['adminName']) ? $systemConfig['adminName']:'System Admin';

		// Now set some defaults for the e-mail message.
		$message += array(
			// If no "from" address/es was/were provided, send it from the "System Admin" ...
			'from' => array($adminEmail => $adminName),
			// If no "toAddress" was provided, send it to the system administrator's e-mail on file (if available).
			// This will at least let them know something is wrong and the message isn't being delievered to the proper place, if they're getting it... =) 
			'to' => array($adminEmail => $adminName),
			'body' => '',
			'subject' => 'E-mail from ' . $_SERVER['HTTP_HOST']
		);
		
		$mailer = Transports::adapter($options['adapter']);

		if(!empty($message['to'])) {
			$emailMessage = Message::newInstance()
			  ->setSubject($message['subject'])
			  ->setFrom($message['from'])
			  ->setTo($message['to'])
			  ->setBody($message['body'], $options['format']);

			if($mailer->send($emailMessage)) {
				return true;
			}
		}

		return false;
	}

}
?>