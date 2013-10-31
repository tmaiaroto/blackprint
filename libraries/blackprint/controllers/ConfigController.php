<?php
namespace blackprint\controllers;

use blackprint\models\Config;
use blackprint\extensions\storage\FlashMessage;
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
	
}
?>