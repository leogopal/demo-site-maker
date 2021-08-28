<?php

namespace demo_site_maker\classes\controllers;

use demo_site_maker\classes\Controller;
use demo_site_maker\classes\modules\Back_Compatibility;
use demo_site_maker\classes\Shortcodes;

class Controller_Back_Compatibility extends Controller {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function action_trigger_upgrades() {
		
		$upgraded = Back_Compatibility::get_instance()->trigger_upgrades();

		if ($upgraded) {
			wp_send_json_success(array('upgraded' => 1));
		} else {
			wp_send_json_error(array('data' => _('No data', 'mp-demo'), 'status' => false));
		}
	}
}
