<?php
namespace demo_site_maker\classes;

use Demo_Site_Maker;
use demo_site_maker\classes\models\Sandbox_DAO;
use demo_site_maker\classes\models\Restrictions_Settings;
use demo_site_maker\classes\models\Sandbox;

class Capabilities {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get things going
	 *
	 * @since 1.0
	 */
	public function __construct() {
	}

	public function init() {

		if (Sandbox::get_instance()->is_sandbox()) {
			add_action('current_screen', array($this, 'remove_pages'), 999);
			add_filter('show_password_fields', array($this, 'disable_passwords'));
			add_filter('allow_password_reset', array($this, 'disable_passwords'));
			add_action('personal_options_update', array($this, 'disable_email_editing'), 1);
			add_action('edit_user_profile_update', array($this, 'disable_email_editing'), 1);
			add_action('admin_bar_menu', array($this, 'remove_menu_bar_items'), 999);
		}

        if (Core::is_wp_version('5.1')) {
            add_action('wp_validate_site_deletion', array($this, 'prevent_delete_site'), 10, 2);
        } else {
            add_action('delete_blog', array($this, 'prevent_delete_blog'), 10, 2);
        }
	}

	/**
	 * Prevent the user from visiting various pages
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function remove_pages() {

		global $menu, $submenu;

		if ( !Demo_Site_Maker::is_admin_user() && is_admin() ) {

			remove_meta_box('dashboard_primary', 'dashboard', 'side');
			remove_meta_box('dashboard_secondary', 'dashboard', 'side');

			$sub_menu = Core::get_instance()->decode_special_chars($submenu);
			$options = Restrictions_Settings::get_instance()->get_options(Sandbox_DAO::get_instance()->get_blog_source(get_current_blog_id()));

			$allowed_parent_pages = $options['parent_pages'];
			$allowed_child_pages = $options['child_pages'];

			$disabled_child_pages = $options['child_disabled_pages'];
			$disabled_urls = $options['black_list'];

			//Dashboard
			$allowed_parent_pages[] = 'index.php';

			$allowed_parent_pages = apply_filters('mp_demo_allowed_pages', $allowed_parent_pages);
			$allowed_child_pages = apply_filters('mp_demo_allowed_subpages', $allowed_child_pages);

			// check permissions
			$this->_check_permissions( $disabled_child_pages, $disabled_urls, $allowed_parent_pages );

			//remove menu items
			foreach ($menu as $item) {
				$parent_slug = $item[2];
				if ( !in_array($parent_slug, $allowed_parent_pages) || in_array($parent_slug, $disabled_urls) ) {
					remove_menu_page($parent_slug);
				}
			}

			foreach ($sub_menu as $parent => $parent_item) {

				if ( in_array($parent, $allowed_parent_pages) ) {

					foreach ($parent_item as $item) {
						$child_slug = $item[2];
						$generated_child_slug = mp_demo_generate_submenu_uri($parent, $item[2]);

						if( in_array($generated_child_slug, $disabled_urls) ) {
							$this->remove_submenu_item($parent, $child_slug);
							continue;
						}

						if ( !in_array($generated_child_slug, $allowed_child_pages) ) {
							if ($parent === 'themes.php') {
								if ( !$this->in_restrictions($generated_child_slug, $allowed_child_pages) ) {
									$this->remove_submenu_item($parent, $child_slug);
								}
							} else {
								$this->remove_submenu_item($parent, $child_slug);
							}
						}
					}
				}
			}
		}
	}
	
	private function _check_permissions( $disabled_child_pages, $disabled_urls, $allowed_parent_pages ) {

		global $pagenow;

		$disallow = false;

		if ( $pagenow && (in_array($pagenow, $disabled_child_pages) || in_array($pagenow, $disabled_urls)) ) {
			$disallow = true;
		}

		//prevent access to system pages if they are not manually allowed
		$system_pages = array(
			'plugins.php',
			'users.php',
		);
		foreach( $system_pages as $page ) {
			if ( $pagenow && $pagenow == $page && !in_array($pagenow, $allowed_parent_pages) ) {
				$disallow = true;
				break;
			}
		}

		//user-new.php
		if ( $pagenow && $pagenow == 'user-new.php' && in_array($pagenow, $disabled_child_pages) ) {
			$disallow = true;
		}

		$disallow = apply_filters( 'mp_demo_disallow_access', $disallow, $pagenow, get_current_blog_id() );

		if ( $disallow ) {
			/*echo "<pre>";
			var_dump($pagenow);
			echo "disabled_child_pages" . PHP_EOL;
			var_dump($disabled_child_pages);
			echo "disabled_urls" . PHP_EOL;
			var_dump($disabled_urls);
			echo "system_pages" . PHP_EOL;
			var_dump($system_pages);
			echo "allowed_parent_pages" . PHP_EOL;
			var_dump($allowed_parent_pages);
			echo "</pre>";*/

			wp_die( __('Sorry, you are not allowed to access this page.') );
		}
	}

	/**
	 * It compares with function mp_demo_compare_restrictions
	 *
	 * @param $elem
	 * @param $array
	 * @param $field
	 *
	 * @return bool
	 */
	function in_restrictions($elem, $restrictions) {

		foreach ($restrictions as $restriction) {
			if (mp_demo_compare_restrictions($elem, $restriction))
				return true;
		}

		return false;
	}

	/**
	 * Remove from admin submenu array submenu page
	 *
	 * @param $parent_slug
	 * @param $child_slug
	 */
	function remove_submenu_item($parent_slug, $child_slug) {

		global $submenu;

		remove_submenu_page(htmlentities($parent_slug), htmlentities($child_slug));

		foreach ($submenu[$parent_slug] as $priority => $sub) {
			if ($sub[2] == $child_slug) {
				unset($submenu[$parent_slug][$priority]);
			}
		}
	}

	/**
	 * Disable the password field on our profile page if this isn't the admin user.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function disable_passwords() {

		return Demo_Site_Maker::is_admin_user();
	}

	/**
	 * Remove the email address from the profile page if this isn't the admin user.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function disable_email_editing($user_id) {
		$user_info = get_userdata($user_id);

		if (!Demo_Site_Maker::is_admin_user())
			$_POST['first_name'] = $user_info->user_firstname;
		$_POST['last_name'] = $user_info->user_lastname;
		$_POST['nickname'] = $user_info->nickname;
		$_POST['display_name'] = $user_info->display_name;
		$_POST['email'] = $user_info->user_email;
	}

	/**
	 * Remove items from our admin bar if the user isn't our network admin
	 *
	 * @access public
	 * @return void
	 */
	public function remove_menu_bar_items($wp_admin_bar) {
		if (!Demo_Site_Maker::is_admin_user()) {
			$wp_admin_bar->remove_node('my-sites');
			$wp_admin_bar->remove_node('new-content');
		} else {
			$elements = $wp_admin_bar->get_nodes();
			if (is_array($elements)) {
				foreach ($elements as $element) {

					if ($element->parent == 'my-sites-list') {
						$blog_id = str_replace('blog-', '', $element->id);
						if (Sandbox::get_instance()->is_sandbox($blog_id)) {
							$wp_admin_bar->remove_node($element->id);
						}
					}
				}
			}
		}
	}

	/**
	 * Prevent a user from deleting the main blog
	 *
	 * @access public
	 * @return void
	 */
	public function prevent_delete_blog($blog_id, $drop) {
		if ( $blog_id == MP_DEMO_MAIN_BLOG_ID && !Demo_Site_Maker::is_admin_user() ) {
			wp_die( __('You do not have sufficient permissions to access this page.', 'mp-demo') );
		}
	}

    public function prevent_delete_site($errors, $site) {
        $this->prevent_delete_blog($site->id, true);
    }

}
