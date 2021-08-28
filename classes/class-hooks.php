<?php

namespace demo_site_maker\classes;

use EDD_MP_Demo_Plugin_Updater;
use Demo_Site_Maker;
use demo_site_maker\classes\controllers\Controller_Mail;
use demo_site_maker\classes\models\General_Settings;
use demo_site_maker\classes\models\Restrictions_Settings;
use demo_site_maker\classes\models\Sandbox;
use demo_site_maker\classes\modules\Back_Compatibility;
use demo_site_maker\classes\modules\Menu;
use demo_site_maker\classes\modules\Settings;
use demo_site_maker\classes\modules\Statistics;
use demo_site_maker\classes\modules\Toolbar;
use demo_site_maker\classes\modules\Widget;
use demo_site_maker\classes\modules\Sites;
use demo_site_maker\classes\shortcodes\Shortcode_Is_Not_Sandbox;
use demo_site_maker\classes\shortcodes\Shortcode_Is_Sandbox;
use demo_site_maker\classes\shortcodes\Shortcode_Try_Demo;
use demo_site_maker\classes\shortcodes\Shortcode_Try_Demo_Popup;

class Hooks extends Core {

	protected static $instance;

	public static function get_instance() {

		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init all hooks in projects
	 */
	public static function install_hooks() {

		add_action('init', array(self::get_instance(), 'init'));
		
		/*
		 * Listen to download system info request
		 * wp_nonce check
		 */
		add_action('init', array(self::get_instance(), 'generate_sysinfo_download'));

		add_action('init', array(Shortcodes::get_instance(), 'login_listen'));

		add_action('admin_init', array(self::get_instance(), 'admin_init'));

		//menus
		add_action('network_admin_menu', array(self::get_instance(), 'network_admin_menu'));
		add_action('admin_menu', array(self::get_instance(), 'demo_admin_menu'));

		add_action('admin_init', array(Settings::get_instance(), 'save_settings'));

		// widgets init
		add_action('widgets_init', array(Widget::get_instance(), 'register'));

		add_action('wp_ajax_nopriv_route_url', array(Core::get_instance(), 'wp_ajax_route_url'));
		add_action('wp_ajax_route_url', array(Core::get_instance(), 'wp_ajax_route_url'));

		//cron
		add_action('mp_demo_purge_event', array(Sandbox::get_instance(), 'cron_event'));

        // Gutenberg
        if (function_exists('register_block_type')) {
            add_filter('block_categories', array(Gutenberg::get_instance(), 'register_category'));
            add_action('enqueue_block_editor_assets', array(Gutenberg::get_instance(), 'enqueue_scripts'));
            add_action('init', array(Gutenberg::get_instance(), 'register_blocks'));
        }

		// Toolbar hooks
		if (Toolbar::get_instance()->show_toolbar()) {
			add_filter('template_include', array(Toolbar::get_instance(), 'template_include'), 1);
		}

		// Frontend hooks
		add_action('mp_demo_toolbar_head', array(Toolbar::get_instance(), 'toolbar_head'));
		add_action('mp_demo_toolbar_footer', array(Toolbar::get_instance(), 'toolbar_footer'));

		// save sandboxes_per_page
		add_filter('set-screen-option', array(self::get_instance(), 'set_screen_option'), 10, 3);

		add_action('init', array(Sandbox::get_instance(), 'deleted_blog_check'));

		/*
		 * Commute WP blogs with Sandboxes
		 */
		add_action('archive_blog', array(Sandbox::get_instance(), 'wp_action_archive_blog'));
		add_action('unarchive_blog', array(Sandbox::get_instance(), 'wp_action_unarchive_blog'));
		add_action('deactivate_blog', array(Sandbox::get_instance(), 'wp_action_deactivate_blog'));
		add_action('activate_blog', array(Sandbox::get_instance(), 'wp_action_activate_blog'));
      
		if (Core::is_wp_version('5.1')) {
            add_action('wp_delete_site', array(Sandbox::get_instance(), 'wp_action_delete_site'), 10, 1);
        } else {
            add_action('delete_blog', array(Sandbox::get_instance(), 'wp_action_delete_blog'), 10, 2);
        }

		/**
		 * Back Compatibility actions
		 */
		add_action('admin_notices', array(Back_Compatibility::get_instance(), 'show_upgrade_notices'));
		add_action('network_admin_notices', array(Back_Compatibility::get_instance(), 'show_upgrade_notices'));
		add_action('mp_demo_trigger_upgrades', array(Back_Compatibility::get_instance(), 'trigger_upgrades'));

		// Menu My Sites
		add_action('admin_bar_menu', array(self::get_instance(), 'update_admin_bar_menu'), 999);

		/*
		 * Generate & Download CSV File
		 * wp_nonce check
		 */
		add_action( 'admin_post_generate_csv', array( Statistics::get_instance(), 'generate_csv') );
		
		add_filter( 'removable_query_args', function( $removable_query_args ) {
			$removable_query_args[] = 'update-mailchimp-list';
			return $removable_query_args;
		});

		// Sites table
		add_action( 'init', array( Sites::get_instance(), 'init' ) );

	}

	public function update_admin_bar_menu($wp_admin_bar) {

		$this->remove_sandboxes_nodes($wp_admin_bar);
		$this->add_menu_bar_reset($wp_admin_bar);
	}

	public function remove_sandboxes_nodes($wp_admin_bar) {
		$sites_parent_node = 'my-sites-list';
		$sites_nodes = $wp_admin_bar->get_nodes();

		foreach ($sites_nodes as $bar_node => $bar_node_value) {

			if ($bar_node_value->parent == $sites_parent_node) {
				$matches =  preg_match('/^blog-\d+$/', $bar_node);

				if ($matches == 1) {
					$blog_id =  preg_replace('/blog-/', '', $bar_node);

					if (Sandbox::get_instance()->is_sandbox($blog_id)) {
						$wp_admin_bar->remove_node($bar_node);
					}
				}
			}
		}
	}

	/**
	 * Add an item to the menu bar that allows them to reset the sandbox
	 */
	public function add_menu_bar_reset($wp_admin_bar) {

		if ( Sandbox::get_instance()->is_sandbox() &&
			 General_Settings::get_instance()->get_option('enable_reset') == 1
		 ){
			$wp_admin_bar->add_menu( array(
				'id'   => 'mp-reset-demo',
				'meta' => array(
					'onclick' => 'mpConfirmResetDemo()'
				),
				'title' => __( 'Reset Demo', 'mp-demo' ),
				'href' => '#')
			);
		}
	}

	/**
	 * Hooks for admin panel
	 */
	public function admin_init() {

		if ( Demo_Site_Maker::has_license() ) {
			new EDD_MP_Demo_Plugin_Updater(
				Demo_Site_Maker::get_plugin_store_url(),
				Demo_Site_Maker::get_plugin_file(),
				array(
					'version' => Core::get_version(),                   // current version number
					'license' => get_option('edd_mp_demo_license_key'), // license key (used get_option above to retrieve from DB)
					'item_id' => Demo_Site_Maker::get_plugin_id(),       // id of this plugin
					'author'  => Demo_Site_Maker::get_plugin_author()    // author of this plugin
				)
			);
		}

		//trigger_upgrades
		if ( isset($_GET['mp-demo-action']) ) {
			$action = $_GET['mp-demo-action'];
			if ( $action == 'trigger_upgrades' ) {
				do_action( 'mp_demo_' . $action, $_GET );
			}
		}

		// add btn to TinyMCE
		if ( Demo_Site_Maker::is_admin_user() ) {

			foreach (array('post.php', 'post-new.php') as $hook) {
				add_action("admin_head-$hook", array($this, 'admin_head_js_vars'));
			}

			add_filter('mce_external_plugins', array($this, 'mce_external_plugins'));
			add_filter('mce_buttons', array($this, 'mce_buttons'));

			foreach (array('post.php', 'post-new.php') as $hook) {
				add_action("admin_footer-$hook", array($this, 'output_popups'));
			}
		}

	}

	/**
	 * Init hook
	 */
	public function init() {

		Capabilities::get_instance()->init();

		//shortcodes
		add_shortcode('try_demo', array(Shortcode_Try_Demo::get_instance(), 'render_shortcode'));
		add_shortcode('try_demo_popup', array(Shortcode_Try_Demo_Popup::get_instance(), 'render_shortcode'));
		add_shortcode('is_sandbox', array(Shortcode_Is_Sandbox::get_instance(), 'render_shortcode'));
		add_shortcode('is_not_sandbox', array(Shortcode_Is_Not_Sandbox::get_instance(), 'render_shortcode'));

		//add media in frontend and Backend WP
		add_action('wp_enqueue_scripts', array(Core::get_instance(), 'wp_enqueue_scripts'));
		add_action('admin_enqueue_scripts', array(Core::get_instance(), 'wp_admin_enqueue_scripts'));
		add_filter('script_loader_tag', array(Core::get_instance(), 'script_loader_tag'), 10, 2);
	}

	// ntework admin menu
	public static function network_admin_menu() {

		global $submenu;

		if ( is_network_admin() ) {

			$hook = Menu::add_menu_page(
				array(
					'title' => __('Demo', 'mp-demo'),
					'icon_url' => 'dashicons-networking',
					'capability' => 'manage_network_options',
					'function' => array(Settings::get_instance(), 'render_tabs'),
					'menu_slug' => 'mp-demo',
					'position' => '87',
				)
			);

			add_action("load-$hook", function () {
				add_screen_option( 'per_page',
					array(
						'label' => __('Sandboxes', 'mp-demo'),
						'default' => 20,
						'option' => 'sandboxes_per_page'
					)
				);
			});

			// Sandboxes
			$submenu['mp-demo'] = isset($submenu['mp-demo']) ? $submenu['mp-demo'] : array();
			$submenu['mp-demo'][] = array(
				__('Sandboxes', 'mp-demo'),
				'manage_network_options',
				'mp-demo',
				__('Demo', 'mp-demo')
			);

			// Reports
			Menu::add_submenu_page(
				array(
					'title' => __('Reports', 'mp-demo'),
					'capability' => 'manage_network_options',
					'function' => array(Statistics::get_instance(), 'render_tabs'),
					'parent_slug' => 'mp-demo',
					'menu_slug' => 'mp-demo-statistics',
				)
			);

			// Settings
			Menu::add_submenu_page(
				array(
					'title' => __('Settings', 'mp-demo'),
					'capability' => 'manage_network_options',
					'function' => array(Settings::get_instance(), 'render_settings'),
					'parent_slug' => 'mp-demo',
					'menu_slug' => 'mp-demo-settings',
				)
			);
		}

	}

	// menu on demo site
	public static function demo_admin_menu() {

		global $menu;

		// return default menu
		if (Sandbox::get_instance()->is_sandbox()) {
			return $menu;
		}

		$params = array(
				'title' => __('Demo', 'mp-demo'),
				'icon_url' => 'dashicons-networking',
				'capability' => 'manage_network_options',
				'function' => array(Settings::get_instance(), 'render_blog_restrictions'),
				'menu_slug' => 'mp-demo-restrictions',
				'position' => '87',
		);

		Menu::add_menu_page($params);

		return $menu;
	}

	/**
	 * Add Shortcode-building button in MCE editor
	 *
	 * @param $buttons
	 *
	 * @return mixed
	 */
	public function mce_buttons($buttons) {
		array_push($buttons, 'addMPDemoButton');
		return $buttons;
	}

	/**
	 * Connect js for MCE editor
	 *
	 * @param $plugin_array
	 *
	 * @return mixed
	 */
	public function mce_external_plugins($plugin_array) {
		$path = Demo_Site_Maker::get_plugin_url('assets/js/shortcodes/try-demo.js');
		$plugin_array['mp_demo'] = $path;
		return $plugin_array;
	}


	/**
	 * Localize TinyMCE btn Script
	 */
	public function admin_head_js_vars() {

		wp_enqueue_script('magnific-popup');
		wp_enqueue_style('magnific-popup-style');
		wp_enqueue_style('mp-demo-admin-style');

		$img = 'wp-menu-image dashicons-before dashicons-networking';
		?>
		<!-- TinyMCE Shortcode Plugin -->
		<script type='text/javascript'>
			var MP_Demo_MCE_Ajax = {
				'image': '<?php echo $img; ?>',
				'mce_menu_title': '<?php _e('Demo Shortcodes', 'mp-demo'); ?>',
				'mce_title_try': '<?php _e('Try Demo Form', 'mp-demo'); ?>',
				'mce_title_popup': '<?php _e('Try Demo Popup', 'mp-demo'); ?>',
				'mce_title_created': '<?php _e('Is Sandbox', 'mp-demo'); ?>',
				'mce_title_not_sandbox': '<?php _e('Is Not Sandbox', 'mp-demo'); ?>',
				'save_btn': '<?php _e('Insert', 'mp-demo'); ?>',
				'cancel_btn': '<?php _e('Cancel', 'mp-demo'); ?>',
			};
		</script>
		<?php
	}

	/**
	 * Output shortcodes builder popups
	 */
	public function output_popups() {

		if ( is_admin() ) {
			$options = array(
				'try' => Shortcode_Try_Demo::get_instance()->get_options(),
				'popup' => Shortcode_Try_Demo_Popup::get_instance()->get_options(),
				'created' => Shortcode_Is_Sandbox::get_instance()->get_options(),
				'not_sandbox' => Shortcode_Is_Not_Sandbox::get_instance()->get_options(),
			);

			foreach ($options as $option) {
				$this->get_view()->render_html('admin/shortcodes/popup', array('params' => $option), true);
			}
		}

	}

	public function set_screen_option($status, $option, $value) {
		return $value;
	}
	
	/**
	 * Generates the System Info Download File
	 */
	public function generate_sysinfo_download() {

		if (
			!empty($_POST[ 'mp-demo-action' ]) &&
			$_POST[ 'mp-demo-action' ] == 'download_sysinfo' &&
			check_admin_referer( 'download-sysinfo' ) &&
			Demo_Site_Maker::is_admin_user()
		){
			nocache_headers();
			header("Content-type: text/plain");
			header('Content-Disposition: attachment; filename="mp-demo-system-info.txt"');
			echo wp_strip_all_tags($_POST[ 'mp-demo-sysinfo' ]);
			exit;
		}
	}


}
