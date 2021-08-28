<?php

namespace demo_site_maker\classes;

use Demo_Site_Maker;

class Module extends Core {

	/**
	 * Install modules
	 */
	public static function install() {
		// include all core modules
		Core::include_all(Demo_Site_Maker::get_plugin_part_path('classes/modules/'));
	}

}
