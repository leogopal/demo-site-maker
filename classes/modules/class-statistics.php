<?php
/**
 *  Statistics class
 * This class handles the output statistics
 */

namespace demo_site_maker\classes\modules;


use DateInterval;
use DatePeriod;
use DateTime;
use Demo_Site_Maker;
use demo_site_maker\classes\Module;

class Statistics extends Module {

	protected static $instance;
	private $defaultDateStart = '';
	private $defaultDateEnd = '';

	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {

		$this->mailManager = $this->get_model('Sandbox_DAO');
		$this->defaultDateEnd = date("Y-m-d"); //  today
		$this->defaultDateStart = date("Y-m-d", strtotime('-6 days')); // last week
	}

	/**
	 * Output our network admin page
	 *
	 * @access public
	 * @return void
	 */
	public function render_tabs() {

		wp_enqueue_style('jquery-ui-datepicker-style');
		wp_enqueue_script('jquery-ui-datepicker');

		$tabs = apply_filters('mp_demo_statistics_tabs', array(
				'list' => array(
					'label' => __('Reports', 'mp-demo'),
					'priority' => 0,
					'callback' => array(Statistics::get_instance(), 'render_table')
				)
			)
		);
		$curTabId = isset($_GET['tab']) ? $_GET['tab'] : 'list';

		Settings::get_instance()->enqueue_scripts();

		$this->get_view()->render_html("admin/menu-tabs", array('tabs' => $tabs, 'curTabId' => $curTabId), true);
	}

	public function render_table() {

		$start = $this->getDate('mp-demo-start', $this->defaultDateStart);
		$end = $this->getDate('mp-demo-end', $this->defaultDateEnd);

		$table = $this->getTable($start, $end);
		$total = $this->get_total($start, $end);

		$this->get_view()->render_html("admin/statistics", array('total' => $total, 'table' => $table), true);
	}

	private function getTable($start, $end) {
		$table = array(); // date , created , activated

		$rows = $this->mailManager->get_list_between($start, $end);

		// Requires PHP5.3:
		$begin = new DateTime($start);
		$interval = DateInterval::createFromDateString('1 day');
		$end = new DateTime($end);
		$end->add($interval);

		$period = new DatePeriod($begin, $interval, $end);

		foreach ($period as $dt) {
			$table[$dt->format("Y_m_d")] = array('date' => $dt->format("Y-m-d"), 'created' => 0, 'activated' => 0);
		}

		// NOW FILL TABLE
		foreach ($rows['created'] as $item) {
			$table_key = str_replace('-', '_', $item['date']);
			$table[$table_key]['created'] = $item['created'];
		}
		foreach ($rows['activated'] as $item) {
			$table_key = str_replace('-', '_', $item['date']);
			$table[$table_key]['activated'] = $item['activated'];
		}

		return $table;
	}

	private function get_total($start, $end) {
		$total = array(); // start , end, created , activated

		$created = $this->mailManager->get_count_created_between($start, $end);
		$activated = $this->mailManager->get_count_activated_between($start, $end);

		$total = array('start' => $start, 'end' => $end, 'created' => $created, 'activated' => $activated);

		return $total;
	}

	/**
	 * @param $date_param_name string $_REQUEST index
	 * @param $default string date
	 *
	 * @return string date
	 */
	public function getDate($date_param_name, $default) {
		$regex = "/^(19|20)\d\d[\-.](0[1-9]|1[012])[\-.](0[1-9]|[12][0-9]|3[01])$/";

		if (!empty($_REQUEST[$date_param_name])) {
			$date = $_REQUEST[$date_param_name];

			return  preg_match($regex,$date) ? $date : $default;
		}

		return $default;
	}
	
	public function generate_csv() {

		if (
			! empty( $_POST ) &&
			check_admin_referer( 'generate-csv' ) &&
			Demo_Site_Maker::is_admin_user() ) {

			$start = $this->getDate( 'mp-demo-start', $this->defaultDateStart );
			$end = $this->getDate( 'mp-demo-end', $this->defaultDateEnd );

			$filename = 'sandboxes-report_' . $start . '-' . $end;
			$generatedDate = date('dmY-His');

			nocache_headers();
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename="' . $filename . '_' . $generatedDate . '.csv";' );
			header('Content-Transfer-Encoding: binary');

			/**
			* create a file pointer connected to the output stream
			* @var [type]
			*/
			$output = fopen('php://output', 'w');
			$data = $this->getTable($start, $end);

			/**
			* output the column headings
			*/
			fputcsv( $output, array(
				__('Date', 'mp-demo'),
				__('Created', 'mp-demo'),
				__('Activated', 'mp-demo'),
			));

			if ( !empty($data) ) {
				foreach ( $data as $key => $value ) {
					fputcsv( $output, $value );
				}
			}

			return $output;
		}
	}

}
