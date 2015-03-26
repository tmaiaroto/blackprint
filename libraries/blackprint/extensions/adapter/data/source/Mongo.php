<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2015, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace blackprint\extensions\adapter\data\source;

class Mongo extends \lithium\data\source\MongoDb {

	/**
	 * Connects to the Mongo server. Matches up parameters from the constructor to create a Mongo
	 * database connection.
	 *
	 * @see lithium\data\source\MongoDb::__construct()
	 * @link http://php.net/mongo.construct.php PHP Manual: Mongo::__construct()
	 * @return boolean Returns `true` the connection attempt was successful, otherwise `false`.
	 */
	public function connect() {
		if ($this->server && count($this->server->getConnections()) > 0 && $this->connection) {
			return $this->_isConnected = true;
		}

		$cfg = $this->_config;
		$this->_isConnected = false;

		$host = is_array($cfg['host']) ? join(',', $cfg['host']) : $cfg['host'];
		$login = $cfg['login'] ? "{$cfg['login']}:{$cfg['password']}@" : '';
		$connection = "mongodb://{$login}{$host}" . ($login ? "/{$cfg['database']}" : '');

		$options = array(
			'connect' => true,
			'connectTimeoutMS' => $cfg['timeout'],
			'replicaSet' => $cfg['replicaSet']
		);

		try {
			if ($persist = $cfg['persistent']) {
				$options['persist'] = $persist === true ? 'default' : $persist;
			}
			$server = $this->_classes['server'];
			$this->server = new $server($connection, $options);

			if ($this->connection = $this->server->{$cfg['database']}) {
				$this->_isConnected = true;
			}

			if ($prefs = $cfg['readPreference']) {
				$prefs = !is_array($prefs) ? array($prefs, array()) : $prefs;
				$this->server->setReadPreference($prefs[0], $prefs[1]);
			}
		} catch (Exception $e) {
			throw new NetworkException("Could not connect to the database.", 503, $e);
		}
		return $this->_isConnected;
	}

}