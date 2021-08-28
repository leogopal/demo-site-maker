<?php
/**
 * class Sandbox_List
 */

namespace demo_site_maker\classes\models;

use demo_site_maker\classes\Core;

if (!class_exists('WP_List_Table')) {
	$admin = Core::get_instance()->get_last_subfolder(admin_url(), '/wp-admin/');
	require_once(MP_DEMO_ABSPATH . $admin . 'includes/class-wp-list-table.php');
}

class Sandbox_List extends \WP_List_Table {
	
	protected static $instance;
	
	public function __construct() {
		
		parent::__construct(array(
			'singular' => __('Sandbox', 'mp-demo'), //singular name of the listed records
			'plural' => __('Sandboxes', 'mp-demo'), //plural name of the listed records
			'ajax' => false //does this table support ajax
		));
		
	}

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	/** Text displayed when no sandboxes data is available */
	public function no_items() {
		_e('No sandboxes avaliable.', 'mp-demo');
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default($item, $column_name) {

		$blog_details = get_blog_details( $item[ $column_name ] );

		switch ($column_name) {
			case 'status':
				return ucfirst($item[ $column_name ]);
				break;
			case 'is_lifetime':
				if ($item[ $column_name ] == 1) {
					return '<span class="dashicons dashicons-yes"></span>';
				} else {
					return '&mdash;';
				}
				break;
			case 'source_blog_id':
				return '<a href="' . $blog_details->siteurl . '" target="_blank"><span style="color:#999">' . $blog_details->blogname . '</span></a>';
				break;
			case 'registered':
				$registered = '<abbr title="' . $item[ $column_name ] . '">' . date_i18n( get_option( 'date_format' ), strtotime( $item[ $column_name ] ) ) . '</abbr>';
				$registered .= '<div class="row-actions"><span style="color:#999">' . sprintf( __('%s ago', 'mp-demo'),
					human_time_diff(
						strtotime( $item[ $column_name ] ),
						current_time( 'timestamp' )
					)
				) . '</span></div>';
				return $registered;
				break;
			case 'expiration_date':
				if ( $item[ 'is_lifetime' ] == 1 ) {
					return '&mdash;';
				} else {
					$expiration_date = '<abbr title="' . $item[ $column_name ] . '">' . date_i18n( get_option( 'date_format' ), strtotime( $item[ $column_name ] ) ) . '</abbr>';
					$template = current_time( 'timestamp' ) > strtotime( $item[ $column_name ] ) ? __('%s ago', 'mp-demo') : __('in %s', 'mp-demo');
					$expiration_date .= '<div class="row-actions"><span style="color:#999">' .
						sprintf( $template, human_time_diff( current_time( 'timestamp' ), strtotime( $item[ $column_name ] ))) . '</span></div>';
					return $expiration_date;
				}
				break;
			case 'path':
			case 'email':
				return $item[ $column_name ];
				break;
			default:
				return print_r($item, true);
		}
	}
	
	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="sandbox[]" value="%s" />', $item[ 'blog_id' ]
		);
	}
	
	/**
	 * Method for sandbox path column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_path($item) {

		$delete_nonce = wp_create_nonce('mp_delete_sandbox');
		$actions = array();

		$base = network_site_url();
		$base = rtrim($base, "/");
		$blog_id = $item[ 'blog_id' ];
		$path = $item[ 'path' ];

		$actions[ 'sandbox_id' ] = '<span style="color:#999">ID:' . $blog_id . '</span>';
		$actions[ 'edit' ] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			get_admin_url( $blog_id ), $path, __('Dashboard', 'mp-demo')
		);
		$actions[ 'view' ] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			$base . $path, $path, __('Visit', 'mp-demo')
		);
		$actions[ 'inline hide-if-no-js' ] = sprintf(
			'<a href="?page=%s&action=edit&sandbox=%s" aria-label="%s">%s</a>', esc_attr($_REQUEST[ 'page' ]),
			absint($blog_id), $path, __('Edit', 'mp-demo')
		);
		$actions[ 'delete' ] = sprintf(
			'<a href="?page=%s&action=delete&sandbox=%s&_wpnonce=%s" aria-label="%s">%s</a>',
			esc_attr($_REQUEST[ 'page' ]), absint($blog_id), $delete_nonce, $path, __('Delete', 'mp-demo')
		);

		$actions[ 'export hide-if-no-js' ] = sprintf(
			'<a href="?page=%s&action=export&sandbox=%s" aria-label="%s">%s</a>',
			esc_attr($_REQUEST[ 'page' ]), absint($blog_id), $path, __('Export', 'mp-demo')
		);

		return sprintf('<strong>%1$s</strong> %2$s', $path, $this->row_actions($actions));
	}
	
	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __('Delete', 'mp-demo'),
			'bulk-edit' => __('Edit', 'mp-demo')
		);
		
		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = array(
			$this->get_columns(),         //  columns
			array(),                     //  hidden
			$this->get_sortable_columns(),// sortable
		);

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page = $this->get_items_per_page('sandboxes_per_page', 20);

		$current_page = $this->get_pagenum();

		$this->items = self::get_sandboxes($per_page, $current_page);

		$total_items = Sandbox::get_sandbox_list_count();

		$this->set_pagination_args(array(
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page' => $per_page //WE have to determine how many items to show on a page
		));

	}
	
	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'path' => __('Sandbox', 'mp-demo'),
			'email' => __('E-mail', 'mp-demo'),
			'registered' => __('Registered', 'mp-demo'),
			'expiration_date' => __('Expires', 'mp-demo'),
			'status' => __('Status', 'mp-demo'),
			'is_lifetime' => __('Lifetime', 'mp-demo'),
			'source_blog_id' => __('Source', 'mp-demo'),
		);

		return $columns;
	}
	
	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		
		return $sortable = array(
			'registered' => array('registered', true),
			'expiration_date' => array('expiration_date', true),
			'is_lifetime' => array('is_lifetime', true),
			'status' => array('status', true)
		);
	}
	
	public function process_bulk_action() {

		$current_action = $this->current_action();

		switch ($current_action) {

			case 'delete' :
				$nonce = esc_attr($_REQUEST[ '_wpnonce' ]);

				if ( wp_verify_nonce($nonce, 'mp_delete_sandbox') ) {
					self::delete_sandbox(absint($_GET[ 'sandbox' ]));
				}
			break;
			case 'bulk-delete' :
				if ( isset($_REQUEST[ 'sandbox' ]) ) {

					$delete_ids = esc_sql($_POST[ 'sandbox' ]);

					foreach ($delete_ids as $id) {
						self::delete_sandbox($id);
					}
				}
			break;
			case 'edit' :
			case 'bulk-edit' :
				if ( isset($_REQUEST[ 'sandbox' ]) ) {
					Sandbox_Settings::get_instance()->render_menu_tab($current_action);
				}
			break;
		}
	}
	
	/**
	 * Delete a sandbox record.
	 *
	 * @param int $id sandbox ID
	 */
	public static function delete_sandbox($id) {
		Sandbox::get_instance()->delete($id);
	}
	
	/**
	 * Retrieve sandbox data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_sandboxes($per_page = 10, $page_number = 1) {
		$result = Sandbox::get_instance()->get_sandbox_list($per_page, $page_number);
		
		return $result;
	}
	
	protected function get_views() {
		$status_links = array(
			"all" => sprintf( "<a href='admin.php?page=mp-demo'>%s</a>", __('All', 'mp-demo') ),
			"active" => sprintf( "<a href='admin.php?page=mp-demo&status=active'>%s</a>" , __('Active', 'mp-demo') ),
			"archived" => sprintf( "<a href='admin.php?page=mp-demo&status=archived'>%s</a>", __('Archived', 'mp-demo') ),
			"deactivated" => sprintf( "<a href='admin.php?page=mp-demo&status=deactivated'>%s</a>", __('Deactivated', 'mp-demo') )
		);
		
		return $status_links;
	}
	protected function get_table_classes() {
		return array( 'widefat', 'striped', $this->_args['plural'] );
	}
}
