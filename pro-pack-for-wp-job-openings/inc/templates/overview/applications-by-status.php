<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="<?php echo esc_attr( "awsm-jobs-overview-widget-wrapper awsm-jobs-overview-{$widget_id}-widget-wrapper" ); ?>">
	<?php
		/**
		 * Fires before the overview widget content.
		 *
		 * @since 3.0.0
		 *
		 * @param string $widget_id Overview widget ID.
		 */
		do_action( 'before_awsm_jobs_overview_widget_content', $widget_id );

		$status_data = AWSM_Job_Openings_Pro_Overview::get_applications_by_status_data();
	if ( isset( $status_data['total_applications'] ) && $status_data['total_applications'] > 0 ) :
		?>
		<div class="awsm-jobs-overview-chart-wrapper">
			<canvas id="awsm-jobs-overview-applications-by-status-chart"></canvas>
		</div>
	<?php else : ?>
		<div class="awsm-jobs-overview-empty-wrapper">
			<img src="<?php echo esc_url( AWSM_JOBS_PLUGIN_URL . '/assets/img/applications-by-status-chart.png' ); ?>">
			<p>📂 <?php esc_html_e( 'Awaiting applications', 'wp-job-openings' ); ?></p>
		</div>
		<?php
		endif;

		/**
		 * Fires after the overview widget content.
		 *
		 * @since 3.0.0
		 *
		 * @param string $widget_id Overview widget ID.
		 */
		do_action( 'after_awsm_jobs_overview_widget_content', $widget_id );
	?>
</div>
