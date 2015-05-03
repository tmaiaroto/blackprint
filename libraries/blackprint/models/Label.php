<?php
namespace blackprint\models;

use lithium\util\Validator;

class Label extends \lithium\data\Model {

  protected $_meta = array(
	'locked' => true,
	'source' => 'blackprint.labels'
  );
  
  protected $_schema = array(
	'_id' => array('type' => 'id'),
	'name' => array('type' => 'string'),
	'color' => array('type' => 'string'),
	'bgColor' => array('type' => 'string')
  );
  
  public $validates = array(
	'name' => array(
	  array('validLabel', 'message' => 'Name cannot contain any special characters other than dashes and underscores.'),
	  array('notEmpty', 'message' => 'Name cannot be empty.')
	)
  );
  
  public function __construct() {
	$class = __CLASS__;
	
	Validator::add('validLabel', '/^[A-z0-9 _-]*$/i');
  }
  
}
?>