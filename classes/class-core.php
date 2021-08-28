<?php

namespace demo_site_maker\classes;

use Demo_Site_Maker;
use demo_site_maker\classes\libs\MP_Demo_Logs;
use demo_site_maker\classes\models\General_Settings;
use demo_site_maker\classes\models\Sandbox;
use demo_site_maker\classes\modules\Widget;
use demo_site_maker\classes\Shortcodes;
use demo_site_maker\classes\Gutenberg;


/**
 * Class main state
 */
class Core {
	/**
	 * Current state
	 */
	private $state;
	protected $log;

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init current plugin
	 */
	public function init_plugin($name) {

		load_plugin_textdomain('mp-demo', FALSE, Demo_Site_Maker::get_plugin_dir() . 'languages');

		$this->log = new MP_Demo_Logs();

		// include plugin files
		Model::install();

		Controller::get_instance()->install();

		Preprocessor::install();

		Module::install();

		Shortcodes::install();

		Widget::install();

        if (function_exists('register_block_type')) {
            Gutenberg::install();
        }

		$this->install_state($name);

		Hooks::install_hooks();

	}


	/**
	 * Register frontend scripts
	 */
	public static function wp_enqueue_scripts() {
		$ver = Core::get_version();

		$settings = General_Settings::get_instance()->get_option('recaptcha');
		$lang = '';

		if (!empty($settings['lang'])) {
			$lang = 'hl=' . trim($settings['lang']) . '&';
		}
		wp_register_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js?' . $lang . 'render=explicit&onload=mpDemoGCaptchaOnLoad');

		// Popup
		wp_register_script('magnific-popup', Demo_Site_Maker::get_plugin_url('assets/js/libs/magnific-popup.min.js'), array('jquery'), '0.9.9', true);
		wp_register_script('mp-demo-script', Demo_Site_Maker::get_plugin_url('assets/js/script.min.js'), array('jquery'), $ver, true);
		wp_localize_script('mp-demo-script', 'MP_Demo_Ajax',
			array(
				'url' => admin_url('admin-ajax.php'),
				'security' => wp_create_nonce('mp-ajax-public-nonce'),
			)
		);

		wp_enqueue_style('magnific-popup-style', Demo_Site_Maker::get_plugin_url('assets/css/magnific-popup.min.css'), array(), '0.9.9');
		wp_enqueue_style('mp-demo-style', Demo_Site_Maker::get_plugin_url('assets/css/popup.min.css'), array(), $ver);
		
		Core::wp_enqueue_reset_scripts();
	}

	/**
	 * Register all scripts
	 */
	public static function wp_admin_enqueue_scripts() {

		$ver = Core::get_version();

		// Popup
		wp_register_script('magnific-popup', Demo_Site_Maker::get_plugin_url('assets/js/libs/magnific-popup.min.js'), array('jquery'), '0.9.9', true);
		wp_register_style('magnific-popup-style', Demo_Site_Maker::get_plugin_url('assets/css/magnific-popup.min.css'), array(), '0.9.9');

		// Admin
		wp_register_style('jquery-ui-datepicker-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), '1.11.4');
		wp_register_style('mp-demo-admin-style', Demo_Site_Maker::get_plugin_url('assets/css/admin.min.css'), array(), $ver);

		wp_register_script('mp-demo-admin-script', Demo_Site_Maker::get_plugin_url('assets/js/admin.min.js'), array('jquery'), $ver, true);
		wp_localize_script('mp-demo-admin-script', 'MP_Demo_Ajax',
			array(
				'url' => admin_url('admin-ajax.php'),
				'upload_url' => admin_url('media-upload.php?referer=mp-demo-settings&tab=toolbar&type=image&TB_iframe=true&post_id=0'),
				'security' => wp_create_nonce('mp-ajax-nonce'),
				'update_text' => __('Update Product', 'mp-demo'),
				'add_text' => __('Add Product', 'mp-demo')
			)
		);
		
		Core::wp_enqueue_reset_scripts();

	}

	function script_loader_tag($tag, $handle) {

		$scripts_to_async = array('google-recaptcha');

		foreach($scripts_to_async as $script){
			if($handle === $script)
				return str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}

		return $tag;
	}

	public static function wp_enqueue_reset_scripts() {

		if ( Sandbox::get_instance()->is_sandbox() &&
			 General_Settings::get_instance()->get_option('enable_reset') == 1
		){

			$ver = Core::get_version();
			wp_register_script('mp-demo-sandbox-reset', Demo_Site_Maker::get_plugin_url('assets/js/sandbox-reset.min.js'), array('jquery'), $ver, true);
			wp_localize_script('mp-demo-sandbox-reset', 'MP_Demo_Ajax',
					array(
							'url' => admin_url('admin-ajax.php'),
							'security' => wp_create_nonce('mp-ajax-reset-sandbox-nonce'),
							'confirmMessage' => __("Reset your demo to default (syncronized with Administrator's updates).\nYour demo expiration date, URL and login credentials won't change.\n\nIt may take a while.", 'mp-demo'),
							'successMessage' => __("Great! Your demo has been reset successfully.", 'mp-demo'),
							'warningMessage' => __("Oops! Something went wrong.", 'mp-demo'),
					)
			);
			wp_enqueue_script('mp-demo-sandbox-reset');
		}
	}

	/**
	 * Get model instace
	 *
	 * @param bool|false $type
	 *
	 * @return bool|mixed
	 */
	public function get($type = false) {
		$state = false;
		if ($type) {
			$state = $this->get_model($type);
		}
		return $state;
	}

	/**
	 * install current state
	 */
	public function install_state($name) {
		// include plugin state
		Core::get_instance()->set_state(new State_Factory($name));
	}

	/**
	 * Route plugin url
	 * ajax
	 */
	public function wp_ajax_route_url() {
		$controller = isset($_REQUEST["controller"]) ? $_REQUEST["controller"] : null;
		$action = isset($_REQUEST["mp_demo_action"]) ? $_REQUEST["mp_demo_action"] : null;

		if ( !empty($action) ) {

			if (empty($_POST)) {
				die();
			}

			// call controller
			Preprocessor::get_instance()->call_controller($action, $controller);
			die();
		}
	}

	/**
	 * Check for ajax post
	 *
	 * @return type
	 */
	static function is_ajax() {
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get State
	 *
	 * @return State
	 */
	public function get_state() {
		if ($this->state) {
			return $this->state;
		} else {
			return false;
		}
	}

	/**
	 * Get controller
	 *
	 * @param type $type
	 *
	 * @return boolean
	 */
	public function get_controller($type) {
		return Core::get_instance()->get_state()->get_controller($type);
	}

	/**
	 * Get view
	 *
	 * @return type
	 */
	public function get_view() {
		return View::get_instance();
	}

	/**
	 * Check and return current state
	 *
	 * @param string $type
	 *
	 * @return boolean
	 */
	public function get_model($type = null) {
		return Core::get_instance()->get_state()->get_model($type);
	}

	/**
	 * Get preprocessor
	 *
	 * @param $type
	 *
	 * @return mixed
	 */
	public function get_preprocessor($type = NULL) {
		return Core::get_instance()->get_state()->get_preprocessor($type);
	}

	/**
	 * @return Plugin Version
	 */
	public static function get_version() {

		if (!function_exists('get_plugin_data')) {
			$admin = Core::get_instance()->get_last_subfolder(admin_url(), '/wp-admin/');
			include_once(MP_DEMO_ABSPATH . $admin . 'includes/plugin.php');
		}
		$pluginObject = get_plugin_data(Demo_Site_Maker::get_plugin_file());

		return $pluginObject['Version'];
	}

    /**
     * @param string $at_least
     *
     * @return bool
     */
    public static function is_wp_version($at_least) {
        global $wp_version;
        return version_compare($wp_version, $at_least, '>=');
    }

	/**
	 * Set state
	 *
	 * @param State $state
	 */
	public function set_state($state) {
		$this->state = $state;
	}

	/**
	 * Generate a random string
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public function random_string($length = 15) {
		$set = '0123456789abcdefghijklmnopqrstuvwxyz';

		return substr(str_shuffle(str_repeat($set, ceil($length / strlen($set)))), 1, $length);
	}

	/**
	 * Filter array of arrays by key => value
	 *
	 * @param $array
	 * @param $key
	 * @param $value
	 * @param $equal
	 */
	public function array_filter($array, $key, $value, $equal = false) {
		$filtered = array();

		foreach ($array as $k => $v) {
			if ( ($equal && isset($v[$key]) && $v[$key] == $value)
			|| (!$equal && isset($v[$key]) && $v[$key] != $value)) {
				$filtered[$k] = $v;
			}
		}

		return $filtered;
	}

	/**
	 * Decode HTML entities within an array
	 *
	 * @access public
	 * @since 1.0
	 * @return array $value
	 */
	public function decode_special_chars($value) {
		$value = is_array($value) ?
			array_map(array(self::$instance, 'decode_special_chars'), $value) :
			html_entity_decode($value);
		return $value;
	}

	/**
	 * Replace the values at the array
	 */
	public function recursive_replace($find, $replace, &$data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$this->recursive_replace($find, $replace, $data[$key]);
				} else {
					if (is_string($value)) $data[$key] = str_replace($find, $replace, $value);
				}
			}
		} else {
			if (is_string($data)) $data = str_replace($find, $replace, $data);
		}
	}

	/**
	 * Search an array recursively for a value
	 *
	 * @access public
	 * @return string $key
	 */
	public function recursive_array_search($needle, $haystack) {
		foreach ($haystack as $key => $value) {
			$current_key = $key;
			if ($needle === $value OR (is_array($value) && $this->recursive_array_search($needle, $value) !== false)) {
				return $current_key;
			}
		}
		return false;
	}

	public function get_last_subfolder($url, $default) {
		$regex = '/\/[\w\.]*\/$/';
		$matches = array();
		preg_match($regex, $url, $matches);

		return (isset($matches[0])) ? $matches[0] : $default;
	}

	public static function get_sites($args = array()) {
		global $wp_version;
		$min_version = '3.7';
		$max_version = '4.6';

		if (version_compare($wp_version, $max_version, '>=')) {
			$sites = get_sites($args);
			$site_array = array();

			foreach ($sites as $key => $s) {
				$site_array[$key] = array();
				$site_array[$key]['blog_id'] = $s->blog_id;
			}

			return $site_array;
		}
		if (version_compare($wp_version, $min_version, '>=')) {
			return wp_get_sites($args);
		}

		return array();
	}

	/**
	 * Include all files from folder
	 *
	 * @param string $folder
	 * @param boolean $inFolder
	 */
	public static function include_all($folder, $inFolder = true) {
		if (file_exists($folder)) {
			$includeArr = scandir($folder);
			foreach ($includeArr as $include) {
				if (!is_dir($folder . "/" . $include)) {
					include_once($folder . "/" . $include);
				} else {
					if ($include != "." && $include != ".." && $inFolder) {
						Core::include_all($folder . "/" . $include);
					}
				}
			}
		}
	}

	function decode_pass($pass='') {
		return base64_decode ($pass);
	}
	function encode_pass($pass='') {
		return base64_encode($pass);
	}

}
