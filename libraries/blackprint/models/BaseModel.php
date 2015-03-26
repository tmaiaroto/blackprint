<?php
namespace blackprint\models;

class BaseModel extends \lithium\data\Model {

	protected $_meta = array(
		'locked' => true
	);
	
	static $urlField;
	
	static $urlSeparator = '-';
	
	static $searchSchema = array();
	
	

}
?>