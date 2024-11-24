<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$confirmation_types = array(
	'message'      => __( 'Show default message', 'pro-pack-for-wp-job-openings' ),
	'page'         => __( 'Show page', 'pro-pack-for-wp-job-openings' ),
	'redirect_url' => __( 'Redirect to a URL', 'pro-pack-for-wp-job-openings' ),
);

$form_confirmation    = AWSM_Job_Openings_Pro_Form::get_form_confirmation();
$selected_page_id     = intval( $form_confirmation['page'] );
$selected_page_status = get_post_status( $selected_page_id );
$page_exists          = $selected_page_status === 'publish' ? true : false;
$args                 = array(
	'echo'     => false,
	'id'       => 'awsm-jobs-form-submit-comfirmation-show-page',
	'name'     => 'awsm_jobs_form_confirmation_type[page]',
	'selected' => $selected_page_id,
);
if ( ! $page_exists ) {
	$args['selected']         = '';
	$args['show_option_none'] = esc_html__( 'Select page', 'pro-pack-for-wp-job-openings' );
}
?>

<div class="awsm-pro-form-submit-confirmation-section">
	<div class="awsm-pro-form-submit-confirmation-container">
		<div class="awsm-wpjo-form-group awsm-pro-form-submit-group">
			<p>
				<select id="awsm-pro-form-submit-confirmation-type" name="awsm_jobs_form_confirmation_type[type]">
					<?php
					foreach ( $confirmation_types as $confirmation_type => $confirmation_type_label ) {
						printf( '<option value="%2$s" %3$s>%1$s</option>', esc_html( $confirmation_type_label ), esc_attr( $confirmation_type ), selected( $form_confirmation['type'], $confirmation_type, false ) );
					}
					?>
				</select>
			</p>
		</div>

		<div class="awsm-wpjo-form-group awsm-form-submit-confirmation-option<?php echo $form_confirmation['type'] !== 'message' ? ' awsm-hide' : ''; ?>" id="awsm-form-submit-confirmation-option-message">
			<textarea name="awsm_jobs_form_confirmation_type[message]" cols="25" rows="3" id="awsm-jobs-form-confirmation-type-default-msg" placeholder="<?php esc_attr_e( 'Add your message here.', 'pro-pack-for-wp-job-openings' ); ?>" required><?php echo esc_textarea( $form_confirmation['message'] ); ?></textarea>
		</div>

		<div class="awsm-wpjo-form-group awsm-form-submit-confirmation-option<?php echo $form_confirmation['type'] !== 'page' ? ' awsm-hide' : ''; ?>" id="awsm-form-submit-confirmation-option-page" style="width: 200px;">
			<?php echo wp_dropdown_pages( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<div class="awsm-wpjo-form-group awsm-form-submit-confirmation-option<?php echo $form_confirmation['type'] !== 'redirect_url' ? ' awsm-hide' : ''; ?>" id="awsm-form-submit-confirmation-option-redirect_url">
			<label for="awsm-pro-submit-form-confirmation-redirect-url"><?php esc_html_e( 'Enter URL', 'pro-pack-for-wp-job-openings' ); ?></label>
			<input type="url" name="awsm_jobs_form_confirmation_type[redirect_url]" id="awsm-pro-submit-form-confirmation-redirect-url" class="awsm-pro-submit-form-confirmation-redirect-url" value="<?php echo esc_url( $form_confirmation['redirect_url'] ); ?>" />
		</div>
	</div>
</div>

