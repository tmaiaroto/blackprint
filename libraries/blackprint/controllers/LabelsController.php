<?php
namespace blackprint\controllers;

use blackprint\models\Label;

class LabelsController extends \lithium\action\Controller {

  public function admin_index() {
  	$response = array('success' => false, 'labels' => array());
  	if(!$this->request->is('json')) {
  		var_dump("NO JSON");exit();
	  return $response;
	}

	$documents = Label::all(array('order' => array('name')));
	if($documents) {
	  $response['labels'] = $documents;
	  $response['success'] = true;
	}
	
	return $response;
  }
  
  public function admin_create() {
	$response = array('success' => false);
	if(!$this->request->is('json')) {
		return $response;
	}
	
	$document = Label::create();
	
	// If data was passed, set some more data and save
	if ($this->request->data) {
	  // Labels are all lowercase, so there is no need for regular expressions 
	  // Or if we ignored case and didn't use regex, users would need to be careful about case.
	  $this->request->data['name'] = strtolower($this->request->data['name']);
	  
	  // Simple validation here, without returning any error messages.
	  if(strlen($this->request->data['name']) > 40) {
		return $response;
	  }
	  
	  // Save
	  if($document->save($this->request->data)) {
		$response['success'] = true;
		return $response;
	  }
	}
	return $response;
  }

  public function admin_update($name=null) {
	$response = array('success' => false);
	if(!$this->request->is('json')) {
		return $response;
	}
	
	if(!empty($name)) {
		$name = urldecode($name);
	}
	$document = Label::find('first', array('conditions' => array('name' => $name)));
	if(empty($document)) {
		return $reponse;
	}
	
	// If data was passed, set some more data and save
	if ($this->request->data) {
	  // Labels are all lowercase, so there is no need for regular expressions 
	  // Or if we ignored case and didn't use regex, users would need to be careful about case.
	  $this->request->data['name'] = strtolower($this->request->data['name']);
	  
	  // Simple validation here, without returning any error messages.
	  if(strlen($this->request->data['name']) > 40) {
		return $response;
	  }
	  
	  // Save
	  if($document->save($this->request->data)) {
		$response['success'] = true;
		return $response;
	  }
	}
	return $response;
  }
  
  public function admin_delete($name=null) {
	$response = array('success' => false);
	if(!$this->request->is('json')) {
	  return $response;
	}
	  
	// If data was passed, set some more data and save
	if ($name) {
	  $label = Label::find('first', array('conditions' => array('name' => strtolower(urldecode($name)))));
	  if($label && $label->delete()) {
		$label['_id'] = (string)$label->_id;
		$response['success'] = true;
	  }
	}
	return $response;
  }
  
}
?>