<?php
namespace blackprint\controllers;

use blackprint\models\Config;
use blackprint\extensions\storage\FlashMessage;
use li3_swiftmailer\mailer\Transports;
use li3_swiftmailer\mailer\Message;
use lithium\storage\Cache;
use lithium\security\validation\RequestToken;
use \MongoDate;

/**
 * This controller is used for working with the CMS configuration.
 */
class ConfigController extends \lithium\action\Controller {
	
	/**
	 * Manages the configuration settings.
	 * 
	*/
	public function admin_update() {
		$this->_render['layout'] = 'admin';
	
		// For now, just one config named "default"
		$document = Config::find('first', array('conditions' => array('name' => 'default')));
		if(empty($document)) {
			$document = Config::create(array('name' => 'default'));
		}

		if($this->request->data) {
			// CSRF
			if(!RequestToken::check($this->request)) {
				RequestToken::get(array('regenerate' => true));
			} else {
				$now = new MongoDate();
				$this->request->data['modified'] = $now;
				if(empty($document->created)) {
					$this->request->data['created'] = $now;
				}

				// Configuration name, again just one for now.
				$this->request->data['name'] = 'default';

				// Save
				if($document->save($this->request->data)) {
					FlashMessage::write('The configuration has been updated successfully.');
					return $this->redirect(array('library' => 'blackprint', 'controller' => 'config', 'action' => 'update', 'admin' => true));
				} else {
					FlashMessage::write('The configuration could not be updated, please try again.');
				}
			}
		}

		$defaultAllowedExtensions = Config::$allowedFileExtensions;

		$this->set(compact('document', 'defaultAllowedExtensions'));
	}
	
	/**
	 * A simple test for the e-mail configuration.
	*/
	public function admin_test_email($toEmail=false, $toName='') {
		$configurations = Transports::config();

		$method = 'PHP\'s mail() function';
		if(isset($configurations['blackprint_smtp'])) {
			// SMTP configured by the user.
			$mailer = Transports::adapter('blackprint_smtp');
			$method = 'An SMTP server';
		} else {
			// Default PHP mail() ... if the server is setup for it (things could also be seen as spam here, come from admin system users, and so on).
			$mailer = Transports::adapter('blackprint_mail');
		}
		//var_dump($mailer);exit();

		// array('john.doe@example.org' => 'John Doe')

		$systemConfig = Config::get('default');
		$fromAddress = array('admin@' . $_SERVER['HTTP_HOST'] => 'System Admin');
		$toAddress = isset($systemConfig['adminName']) ? $systemConfig['adminName']:'';
		$toAddress = isset($systemConfig['adminEmail']) ? array($systemConfig['adminEmail'] => $toName):array($toEmail => $toName);
		$testBody = 'This is a test message from your Blackprint installation to ensure e-mail is working.' . "\n";
		$testBody .= $method . ' was used to send this message.';

		$communicationsConfig = isset($systemConfig['communications']) && !empty($systemConfig['communications']) ? $systemConfig['communications']:false;
		if(isset($communicationsConfig['smtp']) && !empty($communicationsConfig['smtp'])) {
			if(isset($communicationsConfig['smtp']['fromAddress']) && !empty($communicationsConfig['smtp']['fromAddress'])) {
				$fromName = isset($communicationsConfig['smtp']['fromName']) && !empty($communicationsConfig['smtp']['fromName']) ? $communicationsConfig['smtp']['fromName']:'System Admin';
				$fromAddress = array($communicationsConfig['smtp']['fromAddress'] => $fromName);
			}
		}

		var_dump('from');
		var_dump($fromAddress);
		var_dump('to');
		var_dump($toAddress);
	//	var_dump($mailer->getTransport());
		//exit();
		// If we have some place to send this test message, try to send it.
		if($toAddress) {
			$message = Message::newInstance()
			                  ->setSubject('E-mail Configuration Test')
			                  ->setFrom($fromAddress)
			                  ->setTo($toAddress)
			                  ->setBody($testBody);

	//	var_dump($message);exit();
			echo $mailer->send($message) ? "success" : "fail";
		}
		exit();
	}
	
}
?>