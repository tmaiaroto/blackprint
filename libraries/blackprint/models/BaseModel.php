<?php
namespace blackprint\models;

class BaseModel extends \lithium\data\Model {

	protected $_meta = array(
		'locked' => true
	);
	
	static $urlField;
	
	static $urlSeparator = '-';
	
	static $searchSchema = array();
	
	public static function __init() {
		// BC...Though can likely remove now.
		if(method_exists('\lithium\data\Model', '__init')) {
			parent::__init();
		}
	}

}
?>