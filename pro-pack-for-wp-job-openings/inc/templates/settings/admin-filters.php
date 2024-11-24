<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$available_job_filters_choices = $available_application_filters_choices = array(); // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

$specifications = get_option( 'awsm_jobs_filter' );
if ( ! empty( $specifications ) ) {
	foreach ( $specifications as $spec ) {
		$spec_key                                = $spec['taxonomy'];
		$general_choice                          = array(
			'value' => $spec_key,
			'text'  => $spec['filter'],
		);
		$available_job_filters_choices[]         = array_merge(
			$general_choice,
			array(
				'id' => "awsm_jobs_available_job_filters-{$spec_key}",
			)
		);
		$available_application_filters_choices[] = array_merge(
			$general_choice,
			array(
				'id' => "awsm_jobs_available_application_filters-{$spec_key}",
			)
		);
	}
}

$enable_job_filters         = get_option( 'awsm_jobs_enable_admin_filters', 'enable' );
$enable_application_filters = get_option( 'awsm_applications_enable_admin_filters', 'enable' );
$default_filters            = array( 'job-category', 'job-type', 'job-location' );
$hidden_class               = 'awsm-hide';

$settings_fields = array(
	array(
		'id'    => 'awsm-jobs-manage-admin-filters-title',
		'label' => __( 'Manage Admin Filters', 'pro-pack-for-wp-job-openings' ),
		'type'  => 'title',
	),
	array(
		'name'    => 'awsm_jobs_enable_admin_filters',
		'label'   => __( 'Admin Filters for Jobs', 'pro-pack-for-wp-job-openings' ),
		'type'    => 'checkbox',
		'class'   => 'awsm-check-toggle-control',
		'choices' => array(
			array(
				'value'      => 'enable',
				'text'       => __( 'Enable filters in Job Openings', 'pro-pack-for-wp-job-openings' ),
				'data_attrs' => array(
					array(
						'attr'  => 'toggle',
						'value' => 'true',
					),
					array(
						'attr'  => 'toggle-target',
						'value' => '#awsm_jobs_openings_admin_filters_row',
					),
				),
			),
		),
		'value'   => $enable_job_filters,
	),
	array(
		'name'            => 'awsm_jobs_available_filters',
		'visible'         => ! empty( $specifications ),
		'label'           => __( 'Available filters', 'pro-pack-for-wp-job-openings' ),
		'type'            => 'checkbox',
		'multiple'        => true,
		'container_id'    => 'awsm_jobs_openings_admin_filters_row',
		'container_class' => $enable_job_filters !== 'enable' ? $hidden_class : '',
		'choices'         => $available_job_filters_choices,
		'default_value'   => $default_filters,
	),
	array(
		'name'    => 'awsm_applications_enable_admin_filters',
		'label'   => __( 'Admin Filters for Applications', 'pro-pack-for-wp-job-openings' ),
		'type'    => 'checkbox',
		'class'   => 'awsm-check-toggle-control',
		'choices' => array(
			array(
				'value'      => 'enable',
				'text'       => __( 'Enable filters in Job Applications', 'pro-pack-for-wp-job-openings' ),
				'data_attrs' => array(
					array(
						'attr'  => 'toggle',
						'value' => 'true',
					),
					array(
						'attr'  => 'toggle-target',
						'value' => '#awsm_jobs_applications_admin_filters_row',
					),
				),
			),
		),
		'value'   => $enable_application_filters,
	),
	array(
		'name'            => 'awsm_applications_available_filters',
		'visible'         => ! empty( $specifications ),
		'label'           => __( 'Available filters', 'pro-pack-for-wp-job-openings' ),
		'type'            => 'checkbox',
		'multiple'        => true,
		'container_id'    => 'awsm_jobs_applications_admin_filters_row',
		'container_class' => $enable_application_filters !== 'enable' ? $hidden_class : '',
		'choices'         => $available_application_filters_choices,
		'default_value'   => $default_filters,
	),
);
?>
	<div class="awsm-form-section-main awsm-sub-options-container" id="awsm-job-specifications-admin-filters-container" style="display: none;">
		<?php
			/**
			 * Fires before the admin filters specifications settings.
			 *
			 * @since 3.1.0
			 */
			do_action( 'before_awsm_specifications_admin_filters_settings' );
		?>

		<table class="form-table">
			<tbody>
				<?php $this->display_settings_fields( $settings_fields ); ?>
			</tbody>
		</table>

		<?php
			/**
			 * Fires after the admin filters specifications settings.
			 *
			 * @since 3.1.0
			 */
			do_action( 'after_awsm_specifications_admin_filters_settings' );
		?>
	</div>
<?php
