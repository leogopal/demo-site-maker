<?php

namespace demo_site_maker\classes\modules;

class Menu {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add menu page
	 *
	 * @param  $params
	 */
	public static function add_menu_page(array $params) {
		$params['capability'] = !empty($params['capability']) ? $params['capability'] : 'manage_options';
		$params['function'] = !empty($params['function']) ? $params['function'] : '';
		$params['position'] = !empty($params['position']) ? $params['position'] : null;
		$params['subtitle'] = !empty($params['subtitle']) ? $params['subtitle'] : $params['title'];
		return add_menu_page($params['title'], $params['subtitle'], $params['capability'], $params['menu_slug'], $params['function'], $params['icon_url'], $params['position']);
	}

	/**
	 * Add submenu page
	 *
	 * @param $params
	 */
	public static function add_submenu_page(array $params) {
		$params['capability'] = !empty($params['capability']) ? $params['capability'] : 'manage_options';
		$params['function'] = !empty($params['function']) ? $params['function'] : '';
		return add_submenu_page($params['parent_slug'], $params['title'], $params['title'], $params['capability'], $params['menu_slug'], $params['function']);
	}

}
