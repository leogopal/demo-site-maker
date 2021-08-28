<?php

namespace demo_site_maker\classes\modules;

use Demo_Site_Maker;
use demo_site_maker\classes\Module;
use demo_site_maker\classes\models\Sandbox;
use demo_site_maker\classes\models\Sandbox_DAO;

class Sites extends Module {
	
	protected static $instance;
	
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	public function __construct() {
	}
	
	public function init() {

		add_filter( 'wpmu_blogs_columns', array( $this, 'wpmu_blogs_columns' ) );
		add_action( 'manage_sites_custom_column', array( $this, 'add_columns' ), 10, 2 );
		add_action( 'manage_blogs_custom_column', array( $this, 'add_columns' ), 10, 2 );
		add_action( 'admin_footer-sites.php', array( $this, 'add_style' ) );
	}

	public function add_columns( $column_name, $blog_id ) {

		$sandbox = Sandbox_DAO::get_instance()->get_data( 'blog_id', $blog_id );

		switch ( $column_name ) {
			case 'is_lifetime':
				if ( $sandbox && $sandbox[ $column_name ] == 1 ) {
					?><span class="dashicons dashicons-yes"></span><?php
				} elseif( $sandbox ) {
					echo '&mdash;';
				}
			break;
			case 'expiration_date':
				if ( $sandbox && $sandbox[ 'is_lifetime' ] == 1 ) {
					echo '&mdash;';
				} elseif( $sandbox ) {
					$expiration_date = '<abbr title="' . $sandbox[ $column_name ] . '">' .
						date_i18n( get_option( 'date_format' ), strtotime( $sandbox[ $column_name ] ) ) . '</abbr>';
					$expiration_date = '<a href="' . network_admin_url('admin.php?page=mp-demo&action=edit&sandbox=' . $blog_id ) . '">' . $expiration_date . '</a>';
					$template = current_time( 'timestamp' ) > strtotime( $sandbox[ $column_name ] ) ? __('%s ago', 'mp-demo') : __('in %s', 'mp-demo');
					$expiration_date .= '<div class="row-actions"><span style="color:#999">' .
						sprintf( $template, human_time_diff(
							current_time( 'timestamp' ),
							strtotime( $sandbox[ $column_name ] )
						)) . '</span></div>';
					echo $expiration_date;
				}
			break;
		}

		return $column_name;
	}

	// Add in a column header
	public function wpmu_blogs_columns( $columns ) {

		$columns['is_lifetime'] = __('Lifetime');
		$columns['expiration_date'] = __('Expires');

		return $columns;
	}

	public function add_style() {
		?>
		<style>
			.sites.fixed .column-is_lifetime{
				width:80px;
			}
			.sites.fixed .column-expiration_date{
				width:10%;
			}
		</style><?php
	}

}
