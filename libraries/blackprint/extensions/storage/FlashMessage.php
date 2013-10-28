<?php
/**
 * A simple flash message class.
 * More limited than li3_flash_message, but that library
 * seemed to have some problems with Blackprint.
 *
 * This is a very basic implementation using cookies so that
 * the flash messages work across load balanced web servers.
 * The configuration `cookie` is always used, which is set
 * to use the Cookie adapter in `session.php` in
 * Blackprint's boostrap process.
 */

namespace blackprint\extensions\storage;

use lithium\core\Libraries;
use lithium\util\String;
use lithium\storage\Session;

class FlashMessage extends \lithium\core\StaticObject {
	
	/**
	 * Writes a flash message.
	 *
	 * @param string $message Message that will be stored.
	 * @param string $key Optional key.
	 * @return boolean True on successful write, false otherwise.
	 */
	public static function write($message, $key = 'flashMessage') {
		return Session::write($key, $message, array('name' => 'cookie'));
	}

	/**
	 * Reads a flash message.
	 *
	 * @param string [$key] Optional key.
	 * @return array The stored flash message.
	 */
	public static function read($key = 'flashMessage') {
		return Session::read($key, array('name' => 'cookie'));
	}

	/**
	 * Clears all flash messages from the session.
	 *
	 * @param string $key Optional key.
	 * @return void
	 */
	public static function clear($key = 'flashMessage') {
		return Session::delete($key, array('name' => 'cookie'));
	}
}

?>