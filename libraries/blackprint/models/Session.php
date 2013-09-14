<?php

namespace blackprint\models;

class Session extends \lithium\data\Model {
	
	protected $_meta = array(
		'connection' => 'blackprint_mongodb',
		'source' => 'blackprint.sessions'
	);
	
}
?>