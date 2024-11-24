<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_listing_strings = AWSM_Job_Openings_Pro_Main::get_listing_strings();
$job_detail_strings  = AWSM_Job_Openings_Pro_Main::get_job_detail_strings();
$form_strings        = AWSM_Job_Openings_Pro_Form::get_form_strings();
$validation_notices  = AWSM_Job_Openings_Pro_Form::get_validation_notices();

$job_listing_fields = array(
	array(
		'name'  => 'awsm_jobs_customize_job_listing_strings[filter_prefix]',
		'label' => __( 'Filter Prefix', 'pro-pack-for-wp-job-openings' ),
		'value' => $job_listing_strings['filter_prefix'],
	),
	array(
		'name'  => 'awsm_jobs_customize_job_listing_strings[filter_suffix]',
		'label' => __( 'Filter Suffix', 'pro-pack-for-wp-job-openings' ),
		'value' => $job_listing_strings['filter_suffix'],
	),
	array(
		'name'  => 'awsm_jobs_customize_job_listing_strings[search_placeholder]',
		'label' => __( 'Search Placeholder', 'pro-pack-for-wp-job-openings' ),
		'value' => $job_listing_strings['search_placeholder'],
	),
	array(
		'name'     => 'awsm_jobs_customize_job_listing_strings[more_details]',
		'label'    => __( 'Details Link', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $job_listing_strings['more_details'],
	),
	array(
		'name'     => 'awsm_jobs_customize_job_listing_strings[load_more]',
		'label'    => __( 'Load More', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $job_listing_strings['load_more'],
	),
	array(
		'name'     => 'awsm_jobs_customize_job_listing_strings[loading]',
		'label'    => __( 'Loading', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $job_listing_strings['loading'],
	),
	array(
		'name'     => 'awsm_jobs_customize_job_listing_strings[no_filtered_jobs]',
		'label'    => __( 'No Filtered Jobs', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $job_listing_strings['no_filtered_jobs'],
	),
);

$job_detail_fields = array(
	array(
		'name'     => 'awsm_jobs_customize_job_detail_strings[expired_job]',
		'label'    => __( 'Expired Job', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $job_detail_strings['expired_job'],
	),
	array(
		'name'     => 'awsm_jobs_customize_job_detail_strings[position_filled]',
		'label'    => __( 'Position Filled', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $job_detail_strings['position_filled'],
	),
	array(
		'name'     => 'awsm_jobs_customize_job_detail_strings[back_to_listings]',
		'label'    => __( 'Back to listings', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $job_detail_strings['back_to_listings'],
	),
);

$form_strings_fields = array(
	array(
		'name'     => 'awsm_jobs_customize_form_strings[field_description_drag_and_drop]',
		'label'    => __( 'Field Description (Drag and Drop)', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $form_strings['field_description_drag_and_drop'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_strings[uploading_drag_and_drop]',
		'label'    => __( 'Uploading (Drag and Drop)', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $form_strings['uploading_drag_and_drop'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_strings[cancel_uploading_drag_and_drop]',
		'label'    => __( 'Cancel Uploading (Drag and Drop)', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $form_strings['cancel_uploading_drag_and_drop'],
	),
	array(
		'name'        => 'awsm_jobs_customize_form_strings[allowed_file_types]',
		'label'       => __( 'Allowed File Types', 'pro-pack-for-wp-job-openings' ),
		'required'    => true,
		'value'       => sprintf( $form_strings['allowed_file_types'], '{{fileTypes}}' ),
		'description' => __( 'Comma separated list of allowed file types', 'pro-pack-for-wp-job-openings' ) . ': <strong>{{fileTypes}}</strong>',
	),
	array(
		'name'        => 'awsm_jobs_customize_form_strings[maximum_allowed_file_size]',
		'label'       => __( 'Maximum Allowed File Size', 'pro-pack-for-wp-job-openings' ),
		'required'    => true,
		'value'       => sprintf( $form_strings['maximum_allowed_file_size'], '{{maxFilesize}}' ),
		'description' => __( 'Maximum allowed file size', 'pro-pack-for-wp-job-openings' ) . ': <strong>{{maxFilesize}}</strong>',
	),
	array(
		'name'        => 'awsm_jobs_customize_form_strings[multi_upload_maximum_allowed_file_size]',
		'label'       => __( 'Maximum Allowed File Size (Multiple Uploads)', 'pro-pack-for-wp-job-openings' ),
		'required'    => true,
		'value'       => sprintf( $form_strings['multi_upload_maximum_allowed_file_size'], '{{maxFiles}}', '{{maxFilesize}}' ),
		'description' => __( 'Maximum Allowed Files', 'pro-pack-for-wp-job-openings' ) . ': <strong>{{maxFiles}}</strong><br />' . __( 'Maximum allowed file size', 'pro-pack-for-wp-job-openings' ) . ': <strong>{{maxFilesize}}</strong>',
	),
	array(
		'name'     => 'awsm_jobs_customize_form_strings[submitting]',
		'label'    => __( 'Button Text on Submission', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $form_strings['submitting'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_strings[restrict_application_form]',
		'label'    => __( 'Restrict Duplicate Applications', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $form_strings['restrict_application_form'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_strings[error]',
		'label'    => __( 'Error Message', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $form_strings['error'],
	),
);

$validation_notices_fields = array(
	array(
		'name'     => 'awsm_jobs_customize_form_validation_notices[required]',
		'label'    => __( 'Required Field', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $validation_notices['required'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_validation_notices[email]',
		'label'    => __( 'Email Field', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $validation_notices['email'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_validation_notices[file_size_default]',
		'label'    => __( 'File Size (Default)', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $validation_notices['file_size_default'],
	),
	array(
		'name'        => 'awsm_jobs_customize_form_validation_notices[file_size_drag_and_drop]',
		'label'       => __( 'File Size (Drag and Drop)', 'pro-pack-for-wp-job-openings' ),
		'required'    => true,
		'value'       => $validation_notices['file_size_drag_and_drop'],
		'description' => __( 'Selected file size', 'pro-pack-for-wp-job-openings' ) . ': <strong>{{filesize}}</strong><br />' . __( 'Maximum allowed file size', 'pro-pack-for-wp-job-openings' ) . ': <strong>{{maxFilesize}}</strong>',
	),
	array(
		'name'        => 'awsm_jobs_customize_form_validation_notices[max_files]',
		'label'       => __( 'Maximum Files', 'pro-pack-for-wp-job-openings' ),
		'required'    => true,
		'value'       => $validation_notices['max_files'],
		'description' => __( 'Maximum Allowed Files', 'pro-pack-for-wp-job-openings' ) . ': <strong>{{maxFiles}}</strong>',
	),
	array(
		'name'     => 'awsm_jobs_customize_form_validation_notices[file_type]',
		'label'    => __( 'File Type', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $validation_notices['file_type'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_validation_notices[gdpr]',
		'label'    => __( 'Privacy Policy', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $validation_notices['gdpr'],
	),
	array(
		'name'     => 'awsm_jobs_customize_form_validation_notices[recaptcha]',
		'label'    => __( 'reCAPTCHA', 'pro-pack-for-wp-job-openings' ),
		'required' => true,
		'value'    => $validation_notices['recaptcha'],
	),
);
?>
	<div class="awsm-form-section-main awsm-sub-options-container" id="awsm-change-strings-appearance-options-container" style="display: none;">
		<?php
			/**
			 * Fires before the change strings appearance settings.
			 *
			 * @since 3.1.0
			 */
			do_action( 'before_awsm_appearance_change_strings_settings' );
		?>

		<table class="form-table">
			<tbody>
				<?php
					printf( '<tr><th scope="row" colspan="2" class="awsm-form-head-title"><h2 id="awsm-jobs-customize-listing-strings-title">%1$s</h2><p class="description">%2$s</p></th></tr>', esc_html__( 'Job Listing Strings', 'pro-pack-for-wp-job-openings' ), esc_html__( 'You can change the default strings in the job listing page to any string you want here.', 'pro-pack-for-wp-job-openings' ) );
					$this->display_settings_fields( $job_listing_fields );

					printf( '<tr><th scope="row" colspan="2" class="awsm-form-head-title"><h2 id="awsm-jobs-customize-job-detail-strings-title">%1$s</h2><p class="description">%2$s</p></th></tr>', esc_html__( 'Job Detail Strings', 'pro-pack-for-wp-job-openings' ), esc_html__( 'You can change the default strings in the job detail page to any string you want here.', 'pro-pack-for-wp-job-openings' ) );
					$this->display_settings_fields( $job_detail_fields );

					/* translators: %1$s: opening anchor tag, %2$s: closing anchor tag */
					$description = '<p class="description">' . sprintf( esc_html__( 'You can change the default form related strings to any string you want here. Rest of the strings are customizable from the %1$sForm Settings%2$s.', 'pro-pack-for-wp-job-openings' ), '<a href="' . esc_url( admin_url( 'edit.php?post_type=awsm_job_openings&page=awsm-jobs-settings&tab=form' ) ) . '">', '</a>' ) . '</p>';
					printf( '<tr><th scope="row" colspan="2" class="awsm-form-head-title"><h2 id="awsm-jobs-customize-generic-notices-title">%1$s</h2>%2$s</th></tr>', esc_html__( 'Form Strings', 'pro-pack-for-wp-job-openings' ), $description ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					$this->display_settings_fields( $form_strings_fields );

					printf( '<tr><th scope="row" colspan="2" class="awsm-form-head-title"><h2 id="awsm-jobs-customize-validation-notices-title">%1$s</h2><p class="description">%2$s</p></th></tr>', esc_html__( 'Form Validation Messages', 'pro-pack-for-wp-job-openings' ), esc_html__( 'You can change the default validation messages to any string you want here.', 'pro-pack-for-wp-job-openings' ) );
					$this->display_settings_fields( $validation_notices_fields );
				?>
			</tbody>
		</table>

		<?php
			/**
			 * Fires after the change strings appearance settings.
			 *
			 * @since 3.1.0
			 */
			do_action( 'after_awsm_appearance_change_strings_settings' );
		?>
	</div>
<?php
