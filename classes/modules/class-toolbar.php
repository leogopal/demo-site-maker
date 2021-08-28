<?php
/**
 * Toolbar
 *
 * This class handles outputting our front-end theme switcher, toolbar
 */
namespace demo_site_maker\classes\modules;

use Demo_Site_Maker;
use demo_site_maker\classes\models\Toolbar_Settings;
use demo_site_maker\classes\Module;
use demo_site_maker\classes\View;

class Toolbar extends Module {

	protected static $instance;

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 *  Output front-end toolbar
	 *
	 * @param $template
	 *
	 * @return string $template
	 */
	public function template_include($template) {
		if ($this->show_toolbar()) {
			View::get_instance()->get_template('toolbar');

			return;
		}

		return $template;
	}

	public function is_permitted_blog() {
		$unpermitted = Toolbar_Settings::get_instance()->get_option('unpermitted');

		if (is_array($unpermitted)) {

			return !in_array(\get_current_blog_id(), $unpermitted);
		}

		return false;
	}

	/**
	 * If it is possible to show toolbar returns true
	 *
	 * @return bool
	 */
	public function show_toolbar() {

		if (Toolbar_Settings::get_instance()->get_option('show_toolbar') == 1
			&& !is_admin()
			&& isset($_GET['dr'])
			&& $_GET['dr'] == 1
			&& $this->is_permitted_blog()
		) {

			return true;
		}

		return false;
	}

	/**
	 * @param $link
	 * @param $post
	 *
	 * @return string new url
	 */
	public function preview_post_link($link, $post) {

		return add_query_arg(array('dr' => 1), $link);
	}

	/**
	 * Get a reference to the view-site node to modify.
	 *
	 * @param $wp_admin_bar
	 */
	public function admin_bar_menu($wp_admin_bar) {
		$node = $wp_admin_bar->get_node('view-site');
		$url = add_query_arg(array('dr' => 1), $node->href);
		$node->href = $url;
		$wp_admin_bar->add_node($node);
	}

	public function toolbar_head() {
		?>
		<link rel="stylesheet" type="text/css" href="<?php echo Demo_Site_Maker::get_plugin_url('assets/css/toolbar.min.css'); ?>">
		<?php
	}

	public function toolbar_footer() {
		?>
		<script src="<?php echo Demo_Site_Maker::get_plugin_url('assets/js/toolbar.min.js'); ?>"></script>
		<?php
	}
	
	public function render_toolbar_table_row($data, $print = true) {

		ob_start();
		?>
		<tr data-id="<?php echo $data[ 'link_id' ] ?>">
			<td class="check-column"></td>
			<td class="select-link_id"><?php echo $data[ 'link_id' ]; ?></td>
			<td class="select-text"><?php echo $data[ 'text' ]; ?></td>
			<td class="select-link"><?php echo $data[ 'link' ]; ?></td>
			<td class="select-img"><img src="<?php echo $data[ 'img' ]; ?>" height="30"></td>
			<td class="select-btn_text"><?php echo $data[ 'btn_text' ]; ?></td>
			<td class="select-btn_url"><?php echo $data[ 'btn_url' ]; ?></td>
			<td>
				<?php if ($print): ?>
					<a class="button view-event-button" href="<?php echo add_query_arg(array('dr' => '1', 'dl' => $data[ 'link_id' ]), $data[ 'link' ]);
						?>" target="_blank"><?php _e('View', 'mp-demo') ?></a>
				<?php endif; ?>
				<a class="button edit-event-button" data-id="<?php echo $data[ 'link_id' ] ?>"><?php _e('Edit', 'mp-demo') ?></a>
				<a class="button delete-event-button" data-id="<?php echo $data[ 'link_id' ] ?>"><?php _e('Delete', 'mp-demo') ?></a>
				<!-- HIDDEN -->
				<input type="hidden" name="settings[select][<?php echo $data[ 'link_id' ] ?>][link_id]" value="<?php echo $data[ 'link_id' ]; ?>">
				<input type="hidden" name="settings[select][<?php echo $data[ 'link_id' ] ?>][text]" value="<?php echo $data[ 'text' ]; ?>">
				<input type="hidden" name="settings[select][<?php echo $data[ 'link_id' ] ?>][link]" value="<?php echo $data[ 'link' ]; ?>">
				<input type="hidden" name="settings[select][<?php echo $data[ 'link_id' ] ?>][img]" value="<?php echo $data[ 'img' ]; ?>">
				<input type="hidden" name="settings[select][<?php echo $data[ 'link_id' ] ?>][btn_text]" value="<?php echo $data[ 'btn_text' ]; ?>">
				<input type="hidden" name="settings[select][<?php echo $data[ 'link_id' ] ?>][btn_url]" value="<?php echo $data[ 'btn_url' ]; ?>">
				<input type="hidden" name="settings[select][<?php echo $data[ 'link_id' ] ?>][btn_class]" value="<?php echo $data[ 'btn_class' ]; ?>">
			</td>
		</tr>
		<?php
		
		$result = ob_get_clean();
		
		if ($print) {
			echo $result;
		}
		
		return $result;
	}


} // End Class