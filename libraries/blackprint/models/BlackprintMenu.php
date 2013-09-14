<?php
namespace blackprint\models;

class BlackprintMenu extends \lithium\core\StaticObject {

	/**
	 * Default static menus.
	 *
	 * @var array
	*/
	static $staticMenus = array(
		'admin' => array(
			'users' => array(
				'title' => 'Users <b class="caret"></b>',
				'url' => '#',
				'activeIf' => array('library' => 'blackprint', 'controller' => 'users'),
				'options' => array('escape' => false),
				'subItems' => array(
					array(
						'title' => 'List All',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'index')
					),
					array(
						'title' => 'Create New',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'users', 'action' => 'create')
					)
				)
			)
		)
	);

	/**
	 * Returns a static menu.
	 * Static menus are defined as arrays.
	 * There is a default admin menu and a default public site menu.
	 *
	 * This method is filterable so the menus can be added, added to or changed.
	 *
	 * @param string $name The name of the static menu to return (empty value returns all menus)
	 * @param array $options
	 * @return array The static menu(s)
	*/
	public static function staticMenu($name=null, $options=array()) {
		$defaults = array();
		$options += $defaults;
		$params = compact('name', 'options');

		$filter = function($self, $params) {
			$options = $params['options'];
			$name = $params['name'];
			$staticMenus = array();

			// get a specific menu or all menus to return
			if(empty($name)) {
				$staticMenus = $self::$staticMenus;
			} else {
				$staticMenus = isset($self::$staticMenus[$params['name']]) ? $self::$staticMenus[$params['name']]:array();
			}

			// sort parent menu items by key name
			ksort($staticMenus);

			// return the static menus
			return $staticMenus;
		};

		return static::_filter(__FUNCTION__, $params, $filter);
	}

}
?>