<?php

namespace demo_site_maker\classes\controllers;

use Demo_Site_Maker;
use demo_site_maker\classes\Controller;
use demo_site_maker\classes\Shortcodes;

class Controller_Toolbar extends Controller {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function action_add_row() {

		if ( isset($_POST['security']) &&
			 wp_verify_nonce($_POST['security'], 'mp-ajax-nonce') &&
			 Demo_Site_Maker::is_admin_user()
		 ){
			if (isset($_POST['data'])) {
				wp_send_json_success(mp_demo_render_toolbar_table_row($_POST['data'], false));
			} else {
				wp_send_json_error(array('data' => _('No data', 'mp-demo'), 'status' => false));
			}

		} else {
			wp_send_json_error(
				array(
					'data' => __('Permission denied.', 'mp-demo'),
					'status' => false
				)
			);
		}
	}
}
