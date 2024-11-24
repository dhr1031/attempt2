<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form            = AWSM_Job_Openings_Pro_Form::get_custom_form_data( $post_id );
$choices         = array(
	array(
		'default' => __( 'WP Job Openings Application Form (Recommended)', 'pro-pack-for-wp-job-openings' ),
	),
	array(
		'custom_button' => __( 'Custom Button', 'pro-pack-for-wp-job-openings' ),
		'custom_form'   => __( 'Custom Form', 'pro-pack-for-wp-job-openings' ),
	),
	array(
		'disable' => __( 'Disable Form', 'pro-pack-for-wp-job-openings' ),
	),
);
$default_form_id = AWSM_Job_Openings_Pro_Form::get_default_form_id();
$forms           = AWSM_Job_Openings_Pro_Form::get_forms(
	array(
		'exclude' => array( $default_form_id ),
	)
);
if ( ! empty( $forms ) ) {
	$default_form_title    = get_post_field( 'post_title', $default_form_id, 'raw' );
	$choices[0]['default'] = ! empty( $default_form_title ) ? $default_form_title : __( 'Default Form', 'pro-pack-for-wp-job-openings' );
	foreach ( $forms as $job_form ) {
		$job_form_id                = $job_form->ID;
		$choices[0][ $job_form_id ] = ! empty( $job_form->post_title ) ? $job_form->post_title : $job_form_id;
	}
}
$form_id = ! empty( $form ) && isset( $form['id'] ) ? $form['id'] : 'default';
?>

<div class="awsm-pro-application-form-section">
	<?php
	/**
	 * Fires before job display options.
	 *
	 * @since 3.2.0
	 *
	 * @param int|string $post_id The Post ID.
	 */
	do_action( 'before_awsm_job_display_options', $post_id );

	if ( $post_id !== 'option' ) :
		$job_id      = get_the_ID();
		$exclude_job = get_post_meta( $job_id, 'awsm_pro_exclude_job', true );
		?>
		<div class="awsm-pro-generic-option-container">
			<p class="awsm-pro-generic-option-field">
				<label for="awsm-pro-exclude-job">
				<input type="checkbox" name="awsm_pro_exclude_job" id="awsm-pro-exclude-job" value="exclude"<?php echo ( $exclude_job === 'exclude' ) ? ' checked' : ''; ?> /><?php esc_html_e( 'Exclude this from Job Listing', 'pro-pack-for-wp-job-openings' ); ?></label>
			</p>
			<p class="description"><?php esc_html_e( 'This option will exclude job from job listing but will be publicly available', 'pro-pack-for-wp-job-openings' ); ?></p>
		</div>
		<div class="awsm-pro-generic-option-container">
			<p class="awsm-pro-generic-option-field">
				<label for="awsm-job-filled">
				<input type="checkbox" name="awsm_job_filled" id="awsm-job-filled" value="filled"<?php echo AWSM_Job_Openings_Pro_Pack::is_position_filled( $job_id ) ? ' checked' : ''; ?> /><?php esc_html_e( 'Position Filled', 'pro-pack-for-wp-job-openings' ); ?></label>
			</p>
		</div>
	<?php endif; ?>
	<div class="awsm-pro-application-form-container">
		<div class="awsm-wpjo-form-group<?php echo ( $form_id === 'default' || $form_id === 'disable' ) ? ' awsm-last-visible-group' : ''; ?>">
			<p>
				<?php if ( $post_id !== 'option' ) : ?>
					<label for="awsm-pro-application-form"><?php esc_html_e( 'Application Form', 'pro-pack-for-wp-job-openings' ); ?></label>
				<?php endif; ?>
				<select id="awsm-pro-application-form" name="awsm_jobs_custom_application_form[id]">
					<?php
						$item          = 1;
						$total_choices = count( $choices );
					foreach ( $choices as $sub_choices ) {
						foreach ( $sub_choices as $option_val => $option_label ) {
							printf( '<option value="%2$s" %3$s>%1$s</option>', esc_html( $option_label ), esc_attr( $option_val ), selected( $form_id, $option_val, false ) );
						}
						if ( $item < $total_choices ) {
							echo '<option value="" disabled>------------------</option>';
						}
						$item++;
					}
					?>
				</select>
			</p>
		</div>

		<?php $button = isset( $form['button'] ) ? $form['button'] : array(); ?>
		<div class="awsm-wpjo-form-group<?php echo $form_id !== 'custom_button' ? ' awsm-hide' : ''; ?>">
			<div class="awsm-pro-application-form-option-custom_button awsm-pro-application-form-option">
				<label for="awsm-pro-application-custom-button-url"><?php esc_html_e( 'Enter URL', 'pro-pack-for-wp-job-openings' ); ?></label>
				<input type="url" name="awsm_jobs_custom_application_form[button][url]" id="awsm-pro-application-custom-button-url" class="awsm-pro-application-custom-button" value="<?php echo isset( $button['url'] ) ? esc_url( $button['url'] ) : ''; ?>"<?php echo $form_id === 'custom_button' ? ' required' : ''; ?> />
			</div>
		</div>

		<div class="awsm-wpjo-form-group<?php echo $form_id !== 'custom_button' ? ' awsm-hide' : ''; ?>">
			<div class="awsm-pro-application-form-option-custom_button awsm-pro-application-form-option">
				<label for="awsm-pro-application-custom-button-text"><?php esc_html_e( 'Button text', 'pro-pack-for-wp-job-openings' ); ?></label>
				<input type="text" name="awsm_jobs_custom_application_form[button][text]" id="awsm-pro-application-custom-button-text" class="awsm-pro-application-custom-button" value="<?php echo isset( $button['text'] ) ? esc_html( $button['text'] ) : ''; ?>"<?php echo $form_id === 'custom_button' ? ' required' : ''; ?> />
			</div>
		</div>

		<div class="awsm-wpjo-form-group<?php echo $form_id === 'custom_button' ? ' awsm-last-visible-group' : ' awsm-hide'; ?>">
			<div class="awsm-pro-application-form-option-custom_button awsm-pro-application-form-option">
				<label for="awsm-pro-application-custom-button-target"><input type="checkbox" name="awsm_jobs_custom_application_form[button][target]" id="awsm-pro-application-custom-button-target" class="awsm-pro-application-custom-button" value="_blank"<?php echo ( isset( $button['target'] ) && $button['target'] === '_blank' ) ? ' checked' : ''; ?> /> <?php esc_html_e( 'Open in new tab', 'pro-pack-for-wp-job-openings' ); ?></label>
			</div>
		</div>

		<div class="awsm-wpjo-form-group<?php echo $form_id === 'custom_form' ? ' awsm-last-visible-group' : ' awsm-hide'; ?>">
			<div class="awsm-pro-application-form-option-custom_form awsm-pro-application-form-option">
				<textarea name="awsm_jobs_custom_application_form[shortcode]" cols="25" rows="3" placeholder="<?php esc_attr_e( 'Add your code here. Supports form shortcodes and iframe embedding.', 'pro-pack-for-wp-job-openings' ); ?>"<?php echo $form_id === 'custom_form' ? ' required' : ''; ?>><?php echo isset( $form['shortcode'] ) ? esc_textarea( $form['shortcode'] ) : ''; ?></textarea>
			</div>
		</div>
	</div>

	<?php
		/**
		 * Fires after job display options.
		 *
		* @since 3.2.0
		*
		* @param int|string $post_id The Post ID.
		 */
		do_action( 'after_awsm_job_display_options' );
	?>
</div>

