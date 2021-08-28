<?php

namespace demo_site_maker\classes\shortcodes;

use demo_site_maker\classes\models\Sandbox;
use demo_site_maker\classes\Shortcodes;

class Shortcode_Is_Sandbox extends Shortcodes {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function render_shortcode($attrs, $content = null) {
		if (Sandbox::get_instance()->is_sandbox()) {
			return do_shortcode($content);
		} else {
			return '';
		}
	}

	public function get_options() {
		$params = array(
			'form_id' => 'mce-mp-demo-is-sandbox',
			'popup_title' => __('Content visible in sandbox', 'mp-demo'),
			'options' => array(
				0 => array(
					'type' => 'textarea',
					'name' => 'content',
					'label' => 'This content will be visible in a created sandbox only',
					'value' => ''
				)
			)
		);

		return $params;
	}

}
