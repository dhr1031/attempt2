<?php
	$form_id            = $post->ID;
	$default_form_id    = AWSM_Job_Openings_Pro_Form::get_default_form_id();
	$awsm_jobs_settings = AWSM_Job_Openings_Pro_Settings::init();
?>
<div class="awsm-job-form-builder-settings-link-container">
	<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=awsm_job_openings&page=awsm-jobs-settings&tab=form' ) ); ?>" title="<?php esc_html_e( 'Go to Form Settings', 'pro-pack-for-wp-job-openings' ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
</div>
<div class="awsm-job-form-builder-container" id="<?php echo esc_attr( sprintf( 'awsm-job-form-builder-container-%s', $form_id === $default_form_id ? 'default' : $form_id ) ); ?>">
	<?php
		// form builder fields options.
		$form_builder_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form_id );

		// form builder other options.
		$other_options    = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form_id, true );
		$form_title       = isset( $other_options['form_title'] ) ? $other_options['form_title'] : '';
		$form_description = isset( $other_options['form_description'] ) ? $other_options['form_description'] : '';
		$btn_text         = isset( $other_options['btn_text'] ) ? $other_options['btn_text'] : '';
	?>
	<div class="awsm-jobs-form-builder-main">
		<?php
			/**
			 * Fires before the form builder content.
			 *
			 * @since 3.1.0
			 *
			 * @param int $form_id The Form ID.
			 */
			do_action( 'before_awsm_jobs_fb_mb_content', $form_id );
		?>

		<div class="awsm-jobs-form-builder-error awsm-jobs-error-container awsm-hidden">
			<div class="awsm-jobs-error">
				<p>
					<strong><?php esc_html_e( 'Invalid form field settings. Please enter valid values and submit again.', 'pro-pack-for-wp-job-openings' ); ?></strong>
				</p>
			</div>
		</div>

		<div class="awsm-jobs-form-builder-head">
			<p>
			<abbr title="<?php esc_html_e( 'Click to edit', 'pro-pack-for-wp-job-openings' ); ?>"><input type="text" placeholder="<?php esc_html_e( 'Form Title', 'pro-pack-for-wp-job-openings' ); ?>" class="regular-text awsm-jobs-form-builder-control" name="awsm_jobs_form_builder_other_options[form_title]" value="<?php echo esc_attr( $form_title ); ?>" /></abbr>
			</p>
			<p>
			<abbr title="<?php esc_html_e( 'Click to edit', 'pro-pack-for-wp-job-openings' ); ?>"><textarea name="awsm_jobs_form_builder_other_options[form_description]" cols="25" rows="2" class="awsm-jobs-form-builder-control" placeholder="<?php esc_html_e( '(Optional description)', 'pro-pack-for-wp-job-openings' ); ?>"><?php echo esc_textarea( $form_description ); ?></textarea></abbr>
			</p>
		</div><!-- .awsm-jobs-form-builder-head -->

		<div class="awsm-jobs-form-builder" id="awsm-jobs-form-builder" data-next="<?php echo ( ! empty( $form_builder_options ) ) ? count( $form_builder_options ) : 1; ?>">
			<?php
			if ( empty( $form_builder_options ) ) {
				$index = 0;
				$awsm_jobs_settings->fb_template( $index );
			} else {
				foreach ( $form_builder_options as $key => $form_builder_option ) {
					$awsm_jobs_settings->fb_template( $key, $form_builder_option );
				}
			}
			?>
		</div><!-- .awsm-jobs-form-builder -->

		<!-- fb-templates -->
		<script type="text/html" id="tmpl-awsm-pro-fb-settings">
			<?php $awsm_jobs_settings->fb_template( '{{data.index}}' ); ?>
		</script>

		<script type="text/html" id="tmpl-awsm-pro-fb-field-options">
			<?php $awsm_jobs_settings->fb_field_options_template( '{{data.index}}' ); ?>
		</script>

		<script type="text/html" id="tmpl-awsm-pro-fb-file-options">
			<?php $awsm_jobs_settings->fb_file_type_options_template( '{{data.index}}' ); ?>
		</script>

		<script type="text/html" id="tmpl-awsm-pro-fb-template-tag">
			<?php $awsm_jobs_settings->fb_field_tag_template( '{{data.index}}' ); ?>
		</script>

		<script type="text/html" id="tmpl-awsm-pro-fb-section-field-options">
			<?php $awsm_jobs_settings->fb_section_field_options_template( '{{data.index}}' ); ?>
		</script>

		<script type="text/html" id="tmpl-awsm-pro-fb-placeholder-field-options">
			<?php $awsm_jobs_settings->fb_placeholder_field_options_template( '{{data.index}}' ); ?>
		</script>

		<script type="text/html" id="tmpl-awsm-pro-fb-iti-options">
			<?php $awsm_jobs_settings->fb_iti_options_template( '{{data.index}}' ); ?>
		</script>

		<script type="text/html" id="tmpl-awsm-pro-fb-error">
			<div class="awsm-jobs-error-container">
				<div class="awsm-jobs-error">
					<p>
						<strong>
							<# if( data.isFieldType ) { #>
								<?php
									/* translators: %s: form field type */
									printf( esc_html__( 'Sorry! You can only have one %s field.', 'pro-pack-for-wp-job-openings' ), '{{data.fieldType}}' );
								?>
							<# } #>
							<# if( data.invalidKey ) { #>
								<?php
									echo esc_html__( 'The template tag should only contain alphanumeric, latin characters separated by hyphen/underscore', 'pro-pack-for-wp-job-openings' );
								?>
							<# } #>
						</strong>
					</p>
				</div>
			</div>
		</script>
		<!-- /fb-templates -->

		<div class="awsm-jobs-form-builder-footer">
			<div class="awsm-jobs-form-element-main">
				<div class="awsm-jobs-form-element-head">
					<div class="awsm-jobs-form-element-head-title">
						<h3>
							<span class="awm-jobs-form-builder-title">
								<?php echo esc_html( $btn_text ); ?>
							</span>
							<span class="awm-jobs-form-builder-input-type">
								<?php esc_html_e( 'Submit Button', 'pro-pack-for-wp-job-openings' ); ?>
							</span>
						</h3>
					</div>
				</div><!-- .awsm-jobs-form-element-head -->

				<div class="awsm-jobs-form-element-content">
					<div class="awsm-jobs-form-element-content-in">
						<p>
							<label for="awsm-job-form-submit-btn-txt" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Label:', 'pro-pack-for-wp-job-openings' ); ?></label>
							<input type="text" class="widefat" id="awsm-job-form-submit-btn-txt" name="awsm_jobs_form_builder_other_options[btn_text]" value="<?php echo esc_attr( $btn_text ); ?>" required />
						</p>
						<p>
							<button type="button" class="button-link awsm-jobs-form-element-close"><?php esc_html_e( 'Close', 'pro-pack-for-wp-job-openings' ); ?></button>
						</p>
					</div><!-- .awsm-jobs-form-element-content-in -->
				</div><!-- .awsm-jobs-form-element-content -->
			</div><!-- .awsm-jobs-form-element-main -->

			<p>
				<?php wp_nonce_field( 'awsm_save_post_meta', 'awsm_jobs_posts_nonce' ); ?>

				<a class="button awsm-add-form-field-row" href="#"><?php esc_html_e( 'Add new Field', 'pro-pack-for-wp-job-openings' ); ?></a>
			</p>
		</div><!-- .awsm-jobs-form-builder-footer -->

		<?php
			/**
			 * Fires after the form builder content.
			 *
			 * @since 3.1.0
			 *
			 * @param int $form_id The Form ID.
			 */
			do_action( 'after_awsm_jobs_fb_mb_content', $form_id );
		?>
	</div><!-- .awsm-jobs-form-builder-main -->
</div><!-- .awsm-job-form-builder-container -->
