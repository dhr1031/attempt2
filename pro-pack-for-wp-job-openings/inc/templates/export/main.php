<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$menu_slug       = AWSM_Job_Openings_Pro_Export::$menu_slug;
$export_page_url = add_query_arg( array( 'page' => $menu_slug ), admin_url( 'edit.php?post_type=awsm_job_openings' ) );
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Export Applications', 'pro-pack-for-wp-job-openings' ); ?></h1>

	<?php
		/**
		 * Fires before export page content.
		 *
		 * @since 3.0.0
		 */
		do_action( 'before_awsm_jobs_export_content' );
	?>

	<div id="<?php echo esc_attr( $menu_slug . '-applications' ); ?>" class="<?php echo esc_attr( $menu_slug . '-wrapper' ); ?>">
		<form action="<?php echo esc_url( $export_page_url ); ?>" method="POST">
			<?php if ( isset( $_GET['awsm_err'] ) && intval( $_GET['awsm_err'] ) === 1 ) : ?>
				<div class="awsm-jobs-error-container">
					<div class="awsm-jobs-error awsm-jobs-warning">
						<p>
							<?php esc_html_e( 'No application found!', 'pro-pack-for-wp-job-openings' ); ?>
						</p>
					</div>
				</div>
			<?php endif; ?>
			<table class="form-table <?php echo esc_attr( $menu_slug . '-table' ); ?>">
				<tbody>
					<tr>
						<th scope="row">
							<label for="awsm-application-export-by"><?php esc_html_e( 'Export By', 'pro-pack-for-wp-job-openings' ); ?></label>
						</th>
						<td>
							<ul class="awsm-list-inline">
								<li>
									<label for="awsm-application-export-by-job"><input type="radio" name="awsm_application_export_by" id="awsm-application-export-by-job" value="job-listing" class="awsm-application-export-by-toggle-control" checked /><?php esc_html_e( 'Job Listing', 'pro-pack-for-wp-job-openings' ); ?></label>
								</li>
								<li>
									<label for="awsm-application-export-by-form"><input type="radio" name="awsm_application_export_by" id="awsm-application-export-by-form" value="application-form" class="awsm-application-export-by-toggle-control" /><?php esc_html_e( 'Application Form', 'pro-pack-for-wp-job-openings' ); ?></label>
								</li>
							</ul>
						</td>
					</tr>
					<tr class="awsm-application-export-by-row awsm-application-export-by-job-listing-row">
						<th scope="row">
							<label for="awsm-job-id"><?php esc_html_e( 'Job', 'wp-job-openings' ); ?></label>
						</th>
						<td>
							<?php
								// phpcs:disable WordPress.Security.NonceVerification.Missing
								$selected_job = isset( $_POST['awsm_job_id'] ) ? intval( $_POST['awsm_job_id'] ) : '';
								$jobs         = get_posts(
									array(
										'posts_per_page'   => -1,
										'post_type'        => 'awsm_job_openings',
										'post_status'      => array( 'publish', 'expired' ),
										'suppress_filters' => false,
									)
								);

								echo '<select name="awsm_job_id" id="awsm-job-id" required>';
									echo '<option value="">' . esc_html__( 'Select Job', 'pro-pack-for-wp-job-openings' ) . '</option>';
								foreach ( $jobs as $job ) {
									printf( '<option value="%1$d"%3$s>%2$s</option>', intval( $job->ID ), esc_html( $job->post_title ), selected( $selected_job, $job->ID, false ) );
								}
								echo '</select>';
								?>
						</td>
					</tr>
					<tr class="awsm-application-export-by-row awsm-application-export-by-application-form-row awsm-hide">
						<th scope="row">
							<label for="awsm-application-form-id"><?php esc_html_e( 'Form', 'pro-pack-for-wp-job-openings' ); ?></label>
						</th>
						<td>
							<?php
								$default_form_id  = AWSM_Job_Openings_Pro_Form::get_default_form_id();
								$forms            = AWSM_Job_Openings_Pro_Form::get_forms(
									array(
										'exclude' => array( $default_form_id ),
									)
								);
								$selected_form_id = isset( $_POST['awsm_application_form_id'] ) ? intval( $_POST['awsm_application_form_id'] ) : '';
								echo '<select name="awsm_application_form_id" id="awsm-application-form-id">';
									echo '<option value="">' . esc_html__( 'Default Form', 'pro-pack-for-wp-job-openings' ) . '</option>';
								foreach ( $forms as $form ) {
									printf( '<option value="%1$s"%3$s>%2$s</option>', esc_attr( $form->ID ), esc_html( $form->post_title ), selected( $selected_form_id, $form->ID, false ) );
								}
								echo '</select>';
								?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="awsm-applications-status"><?php esc_html_e( 'Status', 'pro-pack-for-wp-job-openings' ); ?></label>
						</th>
						<td>
							<?php
								$applications_count = (array) wp_count_posts( 'awsm_job_application' );
								$selected_status    = isset( $_POST['post_status'] ) ? sanitize_text_field( $_POST['post_status'] ) : '';
								$application_status = AWSM_Job_Openings_Pro_Main::get_application_status();

								echo '<select name="post_status" id="awsm-applications-status">';
									echo '<option value="">' . esc_html__( 'All', 'default' ) . '</option>';
							foreach ( $application_status as $status_name => $status_details ) {
								if ( isset( $applications_count[ $status_name ] ) && intval( $applications_count[ $status_name ] ) > 0 ) {
									printf( '<option value="%1$s"%3$s>%2$s</option>', esc_attr( $status_name ), esc_html( $status_details['label'] ), selected( $selected_status, $status_name, false ) );
								}
							}
								echo '</select>';
							?>
						</td>
					</tr>
					<?php
						$available_filters = get_option( 'awsm_applications_available_filters', array( 'job-category', 'job-type', 'job-location' ) );
						$taxonomies        = get_object_taxonomies( 'awsm_job_openings', 'objects' );
					if ( ! empty( $available_filters ) && ! empty( $taxonomies ) ) :
						?>
						<tr class="awsm-application-export-by-row awsm-application-export-by-application-form-row awsm-hide">
							<th scope="row">
								<label for="awsm-job-specifications-1"><?php esc_html_e( 'Job Specifications', 'wp-job-openings' ); ?></label>
							</th>
							<td>
							<?php
								$available_filters = get_option( 'awsm_applications_available_filters', array( 'job-category', 'job-type', 'job-location' ) );
								$job_spec          = array();
							if ( isset( $_POST['awsm_job_admin_filter'] ) ) {
								$job_spec = $_POST['awsm_job_admin_filter'];
							}
								$index = 1;
							foreach ( $taxonomies as $spec => $tax_details ) {
								if ( in_array( $spec, $available_filters ) ) {
									$terms = get_terms( $spec );
									if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
										printf( '<div class="awsm-jobs-export-control-row"><select name="awsm_job_admin_filter[%1$s]" id="awsm-job-specifications-%2$s">', esc_attr( $spec ), esc_attr( $index ) );
											printf( '<option value="">%s</option>', esc_html_x( 'All', 'job filter', 'wp-job-openings' ) . ' ' . esc_html( $tax_details->label ) );
										foreach ( $terms as $spec_option ) {
											$selected_option = isset( $job_spec[ $spec ] ) ? $job_spec[ $spec ] : '';
											printf( '<option value="%1$d"%3$s>%2$s</option>', intval( $spec_option->term_id ), esc_html( $spec_option->name ), selected( $selected_option, $spec_option->term_id, false ) );
										}
										echo '</select></div>';
										$index++;
									}
								}
							}
							// phpcs:enable
							?>
							</td>
						</tr>
					<?php endif; ?>
					<tr>
						<th scope="row">
							<label for="awsm-job-id"><?php esc_html_e( 'Date Range', 'pro-pack-for-wp-job-openings' ); ?></label>
						</th>
						<td>
							<fieldset>
								<p>
									<label for="awsm-date-from"><?php esc_html_e( 'Start Date', 'pro-pack-for-wp-job-openings' ); ?></label>
									<input type="date" name="awsm_date_from" id="awsm-date-from" placeholder="yyyy-mm-dd" pattern="\d{4}-\d{2}-\d{2}" max="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" />
								</p>
								<p>
									<label for="awsm-date-to"><?php esc_html_e( 'End Date', 'pro-pack-for-wp-job-openings' ); ?></label>
									<input type="date" name="awsm_date_to" id="awsm-date-to" placeholder="yyyy-mm-dd" pattern="\d{4}-\d{2}-\d{2}" max="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>" />
								</p>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="hidden" name="awsm_action" value="export_applications" />
			<?php
				wp_nonce_field( 'awsm_export_nonce', 'awsm_nonce' );

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				printf( '<p>%s</p>', AWSM_Job_Openings_Pro_Export::export_button() );
			?>
		</form>
	</div>

	<?php
		/**
		 * Fires after export page content.
		 *
		 * @since 3.0.0
		 */
		do_action( 'after_awsm_jobs_export_content' );
	?>
</div>
