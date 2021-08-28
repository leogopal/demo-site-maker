<?php
/*
 * Plugin Name: Demo Site Maker
 * Plugin URI: https://leogopal.com
 * Description: Provide users with a personal demo of your WordPress products. Enhance your marketing possibilities collecting users' email addresses with MailChimp.
 * Version: 1.0.0
 * Author: MotoPress
 * Author URI: https://leogopal.com
 * License: GPLv2 or later
 * Text Domain: demo-sm
 * Domain Path: /languages
 * Network: True
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use demo_site_maker\classes;

register_activation_hook( __FILE__, array( Demo_Site_Maker::get_instance(), 'on_activation' ) );
register_deactivation_hook( __FILE__, array( 'Demo_Site_Maker', 'on_deactivation' ) );
register_uninstall_hook( __FILE__, array( 'Demo_Site_Maker', 'on_uninstall' ) );
add_action( 'plugins_loaded', array( 'Demo_Site_Maker', 'get_instance' ) );

/**
 * Class Demo_Site_Maker
 */
class Demo_Site_Maker {
	
	public static $short_plugin_name = 'demo-site-maker';
	public static $plugin_id = 377136;

	protected static $instance;
	
	/**
	 * Demo_Site_Maker constructor.
	 */
	public function __construct() {

		Demo_Site_Maker::setup_constants();

		if ( Demo_Site_Maker::is_suitable_site() ) {

			$this->include_all();

			demo_site_maker\classes\Core::get_instance()->init_plugin( 'demo_site_maker' );
		} else {
			Demo_Site_Maker::include_error_info_page();
		}

	}

	/**
	 * Setup constants
	 */
	public static function setup_constants() {
		
		/** Absolute path to the WordPress directory. */
		if ( ! defined( 'MP_DEMO_ABSPATH' ) ) {
			define( 'MP_DEMO_ABSPATH', rtrim( ABSPATH, '/' ) );
		}
		if ( ! defined( 'MP_DEMO_STATUS_ACTIVE' ) ) {
			define( 'MP_DEMO_STATUS_ACTIVE', 'active' );
		}
		if ( ! defined( 'MP_DEMO_STATUS_PENDING' ) ) {
			define( 'MP_DEMO_STATUS_PENDING', 'pending' );
		}
		if ( ! defined( 'MP_DEMO_STATUS_ARCHIVED' ) ) {
			define( 'MP_DEMO_STATUS_ARCHIVED', 'archived' );
		}
		if ( ! defined( 'MP_DEMO_STATUS_DEACTIVATED' ) ) {
			define( 'MP_DEMO_STATUS_DEACTIVATED', 'deactivated' );
		}
		if ( ! defined( 'MP_DEMO_STATUS_DELETED' ) ) {
			define( 'MP_DEMO_STATUS_DELETED', 'deleted' );
		}
		if ( ! defined( 'MP_DEMO_ACTION_DELETE' ) ) {
			define( 'MP_DEMO_ACTION_DELETE', 'delete' );
		}
		if ( ! defined( 'MP_DEMO_ACTION_ARCHIVE' ) ) {
			define( 'MP_DEMO_ACTION_ARCHIVE', 'archive' );
		}
		if ( ! defined( 'MP_DEMO_ACTION_DEACTIVATE' ) ) {
			define( 'MP_DEMO_ACTION_DEACTIVATE', 'deactivate' );
		}
		if ( ! defined( 'MP_DEMO_MAIN_BLOG_ID' ) ) {
			define( 'MP_DEMO_MAIN_BLOG_ID', 1 );
		}
		if ( ! defined( 'MP_DEMO_EMPTY_DATE' ) ) {
			define( 'MP_DEMO_EMPTY_DATE', '0000-00-00 00:00:00' );
		}
	}

	/**
	 * Check site settings
	 * @return bool
	 */
	public static function is_suitable_site() {
		$is_suitable_site = is_multisite() && defined( 'SUBDOMAIN_INSTALL' ) && ( SUBDOMAIN_INSTALL == false );
		
		return apply_filters( 'mp_demo_is_suitable_site', $is_suitable_site );
	}

	/**
	 * Include classes into plugin
	 */
	public function include_all() {
		
		$plugin_dir = Demo_Site_Maker::get_plugin_dir();
		
		/**
		 * Include Gump Validator
		 */
		require_once $plugin_dir . 'classes/libs/gump.class.php';
		/**
		 * Include MP Logger
		 */
		require_once $plugin_dir . 'classes/libs/logs.php';
		/**
		 * Include Plugin Updater
		 */
		require_once $plugin_dir . 'classes/libs/EDD_MP_Demo_Plugin_Updater.php';
		/**
		 * Include classes
		 */
		require_once $plugin_dir . 'classes/class-capability.php';
		
		require_once $plugin_dir . 'classes/class-state-factory.php';
		
		require_once $plugin_dir . 'classes/class-core.php';
		
		require_once $plugin_dir . 'classes/class-model.php';
		
		require_once $plugin_dir . 'classes/class-controller.php';
		
		require_once $plugin_dir . 'classes/class-preprocessor.php';
		
		require_once $plugin_dir . 'classes/class-module.php';
		
		require_once $plugin_dir . 'classes/class-view.php';
		
		require_once $plugin_dir . 'classes/class-hooks.php';
		
		require_once $plugin_dir . 'classes/class-shortcodes.php';
		
		require_once $plugin_dir . 'classes/class-gutenberg.php';
		
		require_once $plugin_dir . 'functions.php';
	}

	/**
	 * Get plugin dir
	 * @return string
	 */
	public static function get_plugin_dir() {
		$file = Demo_Site_Maker::get_plugin_file();
		
		return trailingslashit( plugin_dir_path( $file ) );
	}
	
	/**
	 * Get plugin File
	 *
	 * @return string
	 */
	public static function get_plugin_file() {
		global $wp_version, $network_plugin;
		if ( version_compare( $wp_version, '3.9', '<' ) && isset( $network_plugin ) ) {
			$pluginFile = $network_plugin;
		} else {
			$pluginFile = __FILE__;
		}
		
		return $pluginFile;
	}
	
	/**
	 * Include classes into plugin
	 */
	static function include_error_info_page() {
		
		require_once Demo_Site_Maker::get_plugin_dir() . 'classes/class-site-error.php';
		
		classes\SiteError::install_hooks();
	}
	
	/**
	 * @return Demo_Site_Maker
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * On activation plugin
	 * Upon activation, setup super admin
	 */
	public static function on_activation() {
		
		if ( Demo_Site_Maker::has_license() && class_exists( 'classes\models\License' ) ) {
			$autoLicenseKey = apply_filters( 'mp_demo_auto_license_key', false );
			if ( $autoLicenseKey ) {
				classes\models\License::set_and_activate_license_key( $autoLicenseKey );
			}
		}
		
		if ( ! wp_next_scheduled( 'mp_demo_purge_event' ) ) {
			wp_schedule_event( time(), 'hourly', 'mp_demo_purge_event' );
		}
		
		Demo_Site_Maker::create_tables();
	}
	
	/**
	 * If License is required - return true
	 * @return bool
	 */
	public static function has_license() {
		return true;
	}
	
	/**
	 * Create / Update tables
	 */
	public static function create_tables() {
		global $wpdb;
		
		$tables_names = Demo_Site_Maker::get_tables_names();
		
		if ( Demo_Site_Maker::is_suitable_site() ) {
			$admin = demo_site_maker\classes\Core::get_instance()->get_last_subfolder( admin_url(), '/wp-admin/' );
			require_once( MP_DEMO_ABSPATH . $admin . 'includes/upgrade.php' );
		} else {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		}
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql_users = "CREATE TABLE " . $tables_names[ 'users' ] . " (
			user_id bigint(20) NOT NULL AUTO_INCREMENT,
			email varchar(255) NOT NULL,
			is_valid tinyint(1) NOT NULL,
			first_name varchar(255),
			last_name varchar(255),
			phone varchar(255),
			wp_user_id varchar(255),
			country varchar(255),
			password varchar(255) NOT NULL,
		  PRIMARY KEY  (user_id)
		)  {$charset_collate};";
		
		dbDelta( $sql_users );
		
		$sql_sandboxes = "CREATE TABLE " . $tables_names[ 'sandboxes' ] . " (
			sandbox_id bigint(20) NOT NULL AUTO_INCREMENT,
			blog_id bigint(20),
			source_blog_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			status varchar(255) NOT NULL,
			secret varchar(255) NOT NULL,
			site_url varchar(255),
			is_lifetime tinyint(1),
			creation_date datetime DEFAULT '0000-00-00 00:00:00',
			activation_date datetime DEFAULT '0000-00-00 00:00:00',
			expiration_date datetime DEFAULT '0000-00-00 00:00:00',
		  PRIMARY KEY  (sandbox_id)
		)  {$charset_collate};";
		
		dbDelta( $sql_sandboxes );
	}
	
	/**
	 * Get names of tables
	 * @return array
	 */
	public static function get_tables_names() {

		global $wpdb;

		if ( Demo_Site_Maker::is_suitable_site() ) {
			switch_to_blog( MP_DEMO_MAIN_BLOG_ID );
		}

		$name = array(
			'users'     => $wpdb->prefix . 'mp_demo_users',
			'sandboxes' => $wpdb->prefix . 'mp_demo_sandboxes'
		);

		if ( Demo_Site_Maker::is_suitable_site() ) {
			restore_current_blog();
		}

		return $name;
	}
	
	/**
	 * On deactivation plugin
	 */
	public static function on_deactivation() {
		wp_clear_scheduled_hook( 'mp_demo_purge_event' );
	}
	
	/**
	 * On uninstall
	 */
	public static function on_uninstall() {
		if ( Demo_Site_Maker::is_suitable_site() ) {
			classes\modules\Settings::get_instance()->remove_settings();
		}
	}
	
	/**
	 * Get plugin name
	 * @return string
	 */
	public static function get_plugin_name() {
		return self::$short_plugin_name;
	}
	
	/**
	 * Get full plugin Name
	 * @return
	 */
	public static function get_plugin_full_name() {
		$plugin_data = get_plugin_data( Demo_Site_Maker::get_plugin_file() );
		
		return $plugin_data[ 'Name' ];
	}

	/**
	 * Get plugin ID
	 * @return int
	 */
	public static function get_plugin_id() {
		return self::$plugin_id;
	}
	
	/**
	 * Plugin STORE_URL
	 *
	 * @return mixed
	 */
	public static function get_plugin_store_url() {
		$plugin_data = get_plugin_data( Demo_Site_Maker::get_plugin_file() );
		
		return $plugin_data[ 'PluginURI' ];
	}
	
	/**
	 * Get plugin author
	 *
	 * @return mixed
	 */
	public static function get_plugin_author() {
		$plugin_data = get_plugin_data( Demo_Site_Maker::get_plugin_file(), false, false );
		
		return $plugin_data[ 'Author' ];
	}
	
	/**
	 *  Plugin Url
	 *
	 * @param bool $path
	 * @param string $sync
	 *
	 * @return string
	 */
	public static function get_plugin_url( $path = false, $sync = '' ) {
		$pluginFile = Demo_Site_Maker::get_plugin_file();
		$dirName    = basename( dirname( $pluginFile ) );
		
		return plugin_dir_url( $dirName . '/' . basename( $pluginFile ) ) . '' . $path . $sync;
	}
	
	/**
	 * Get plugin part path
	 *
	 * @param string $part
	 *
	 * @return string
	 */
	public static function get_plugin_part_path( $part = '' ) {
		return Demo_Site_Maker::get_plugin_dir() . $part;
	}
	
	/**
	 * Retrieve relative to theme root path to templates.
	 *
	 * @return string
	 */
	public static function get_template_path() {
		return apply_filters( 'mp_demo_template_path', 'demo-site-maker/' );
	}
	
	/**
	 * Retrieve relative to plugin root path to templates.
	 *
	 * @return string
	 */
	public static function get_templates_path() {
		return self::get_plugin_dir() . 'templates/';
	}
	
	/**
	 * Check to see if the current user is our admin user
	 *
	 * @return bool
	 */
	public static function is_admin_user() {
		
		return current_user_can( 'manage_network_options' );
	}
	
	/**
	 * Check if hide Plugins menu from sandbox and settings
	 * @return bool
	 */
	public static function hide_plugins() {

		$menu_perms   = get_site_option( 'menu_items' );
		$hide_plugins_menu = ( isset( $menu_perms[ 'plugins' ] ) && $menu_perms[ 'plugins' ] == '1' ) ? false : true;

		return apply_filters( 'mp_demo_hide_plugins', $hide_plugins_menu, $menu_perms );
	}

}
