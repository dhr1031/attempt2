<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'AWSM_Job_Openings_Overview' ) ) :

	class AWSM_Job_Openings_Pro_Overview extends AWSM_Job_Openings_Overview {
		private static $instance = null;

		public function __construct() {
			$this->cpath = untrailingslashit( plugin_dir_path( __FILE__ ) );

			add_action( 'before_awsm_jobs_overview_widget_content', array( $this, 'before_overview_widget_content' ) );
			add_action( 'wp_ajax_awsm_jobs_overview_applications_analytics_data', array( $this, 'applications_analytics_data_handler' ) );

			add_filter( 'awsm_jobs_overview_widget_template_path', array( $this, 'pro_widget_template_path' ), 100, 2 );
			add_filter( 'awsm_jobs_overview_widgets', array( $this, 'overview_pro_widgets' ), 100 );
		}

		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function pro_widget_template_path( $path, $unique_id ) {
			if ( $unique_id === 'awsm-jobs-overview-applications-by-status' ) {
				$path = $this->cpath . '/templates/overview/applications-by-status.php';
			}
			return $path;
		}

		public function overview_pro_widgets( $widgets ) {
			$widgets['applications-by-status']['active'] = current_user_can( 'edit_applications' );
			unset( $widgets['applications-by-status']['callback'] );
			return $widgets;
		}

		public function before_overview_widget_content( $widget_id ) {
			if ( $widget_id === 'applications-analytics' ) :
				?>
				<div class="awsm-jobs-overview-widget-control-wrapper">
					<select id="awsm-jobs-overview-analytics-chart-control">
						<option value="today"><?php esc_html_e( 'Today', 'pro-pack-for-wp-job-openings' ); ?></option>
						<option value="week"><?php esc_html_e( 'This week', 'pro-pack-for-wp-job-openings' ); ?></option>
						<option value="month"><?php esc_html_e( 'This month', 'pro-pack-for-wp-job-openings' ); ?></option>
						<option value="year" selected><?php esc_html_e( 'This year', 'pro-pack-for-wp-job-openings' ); ?></option>
					</select>
				</div>
				<?php
			endif;
		}

		public function applications_analytics_data_handler() {
			$response = array(
				'data'  => array(),
				'error' => false,
			);
			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) || ! current_user_can( 'edit_applications' ) ) {
				$response['error'] = true;
			} else {
				$option       = sanitize_text_field( $_POST['awsm_overview_analytics_widget_option'] );
				$date_query   = array();
				$key_format   = 'n';
				$label_format = 'M';

				$timezone_string = get_option( 'timezone_string' );
				if ( function_exists( 'wp_timezone_string' ) ) {
					$timezone_string = wp_timezone_string();
				}
				$selected_zone = get_option( 'awsm_jobs_timezone' );
				if ( is_array( $selected_zone ) && isset( $selected_zone['gmt_offset'] ) && isset( $selected_zone['timezone_string'] ) ) {
					$timezone_string = AWSM_Job_Openings::get_timezone_string( $selected_zone );
				}
				$date_timezone = new DateTimeZone( $timezone_string );
				$datetime      = new DateTime( 'now', $date_timezone );
				$year          = $datetime->format( 'Y' );
				$month         = $datetime->format( 'n' );
				$week          = $datetime->format( 'W' );
				$day           = $datetime->format( 'j' );

				switch ( $option ) {
					case 'month':
						$date_query[0] = array(
							'year'  => $year,
							'month' => $month,
						);
						$key_format    = 'j';
						$label_format  = 'M j';
						break;
					case 'week':
						$date_query[0] = array(
							'year' => $year,
							'week' => $week,
						);
						$key_format    = 'N';
						$label_format  = 'D';
						break;
					case 'today':
						$date_query[0] = array(
							'year'  => $year,
							'month' => $month,
							'day'   => $day,
						);
						$key_format    = 'H';
						$label_format  = 'h A';
						break;
				}
				$response['data'] = AWSM_Job_Openings_Overview::get_applications_analytics_data( $date_query, $key_format, $label_format );
			}
			wp_send_json( $response );
		}

		public static function get_applications_by_status_data() {
			$chart_data = array();
			if ( ! current_user_can( 'edit_applications' ) ) {
				return $chart_data;
			}

			$applications_count = (array) wp_count_posts( 'awsm_job_application' );
			unset( $applications_count['auto-draft'] );
			$total_applications = array_sum( $applications_count );

			$chart_data = array(
				'labels'             => array(),
				'data'               => array(),
				'colors'             => array(),
				'total_applications' => $total_applications,
			);

			$status_options = AWSM_Job_Openings_Pro_Main::get_application_status();
			unset( $status_options['trash'] );
			foreach ( $status_options as $status => $status_option ) {
				$chart_data['labels'][] = $status_option['label'];
				$chart_data['data'][]   = $applications_count[ $status ];
				$chart_data['colors'][] = $status_option['color'];
			}
			/**
			 * Filters the applications by status overview widget chart data.
			 *
			 * @since 3.0.0
			 *
			 * @param array $chart_data Chart data.
			 * @param array $applications_count Applications count array.
			 */
			return apply_filters( 'awsm_jobs_overview_applications_by_status_data', $chart_data, $applications_count );
		}
	}

	AWSM_Job_Openings_Pro_Overview::init();

endif;
