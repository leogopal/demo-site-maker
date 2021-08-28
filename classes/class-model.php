<?php

namespace demo_site_maker\classes;

use Demo_Site_Maker;

/**
 * Model class
 */
class Model extends Core {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Install models by type
	 */
	static function install() {
		$models_path = Demo_Site_Maker::get_plugin_part_path('classes/models/');
		// include all core models
		Core::include_all($models_path);
	}

	/**
	 * Get return Array
	 *
	 * @param array $data
	 * @param bool|false $success
	 *
	 * @return array
	 */
	public function get_arr($data = array(), $success = false) {
		return array('success' => $success, 'data' => $data);
	}

}
