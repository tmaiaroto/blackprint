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
			'blog' => array(
				'title' => 'Blog <b class="caret"></b>',
				'url' => '#',
				'activeIf' => array('library' => 'blackprint', 'controller' => 'posts'),
				'options' => array('escape' => false),
				'subItems' => array(
					array(
						'title' => 'List All Posts',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'posts', 'action' => 'index')
					),
					array(
						'title' => 'Create New Post',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'posts', 'action' => 'create_blank')
					),
					array(
						'title' => 'Configuration',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'config', 'action' => 'update', 'args' => 'blog')
					),
				)
			),
			'content' => array(
				'title' => 'Content <b class="caret"></b>',
				'url' => '#',
				'activeIf' => array(array('library' => 'blackprint', 'controller' => 'content'), array('library' => 'blackprint', 'controller' => 'assets')),
				'options' => array('escape' => false),
				'subItems' => array(
					array(
						'title' => 'Asset Manager',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'assets', 'action' => 'index')
					),
					array(
						'title' => 'List All',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'content', 'action' => 'index')
					),
					array(
						'title' => 'Create New',
						'url' => array('library' => 'blackprint', 'admin' => true, 'controller' => 'content', 'action' => 'create')
					)
				)
			),
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
		),
		'public' => array(
			'home' => array(
				'title' => 'Home',
				'url' => '/',
				'activeIf' => array('url' => '/')
			),
			'blog' => array(
				'title' => 'Blog',
				'url' => array('library' => 'blackprint', 'controller' => 'posts', 'action' => 'index'),
				'activeIf' => array('library' => 'blackprint', 'controller' => 'posts'),
				'options' => array('escape' => false)
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