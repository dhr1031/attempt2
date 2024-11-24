<div class="awsm-sub-options-container" id="awsm-templates-notification-options-container" style="display: none;">
	<?php
		$options = get_option( 'awsm_jobs_pro_mail_templates' );
	?>
	<div id="settings-awsm-settings-notification-templates">
		<?php
			/**
			 * Fires before the notification templates settings.
			 *
			 * @since 3.1.0
			 */
			do_action( 'before_awsm_notification_templates_settings' );
		?>

		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" colspan="2" class="awsm-form-head-title">
						<h2 id="awsm-form-options-title"><?php esc_html_e( 'Manage Templates', 'pro-pack-for-wp-job-openings' ); ?></h2>
					</th>
				</tr>
				<tr>
					<td scope="row" colspan="2" class="awsm-form-table-acc-section">
						<div class="awsm-form-section-main awsm-acc-section-main" id="awsm-repeatable-mail-templates" data-next="<?php echo ( ! empty( $options ) ) ? count( $options ) : 1; ?>">
							<div class="awsm-form-section awsm-acc-secton awsm-mail-templates-acc-section">
								<?php
								if ( empty( $options ) ) {
									$index = 0;
									$this->mail_template( $index );
								} else {
									foreach ( $options as $index => $template ) {
										$this->mail_template( $index, $template );
									}
								}
								?>
							</div><!-- .awsm-form-section -->
						</div><!-- .awsm-form-section-main -->

						<p><a class="button awsm-add-mail-templates" href="#"><?php esc_html_e( 'Add new template', 'pro-pack-for-wp-job-openings' ); ?></a></p>
					</td>
				</tr>
			</tbody>
		</table>

		 <!-- notification-templates -->
		 <script type="text/html" id="tmpl-awsm-pro-notification-settings">
			<?php $this->mail_template( '{{data.index}}' ); ?>
		</script>
		<!-- /notification-templates -->

		<?php
			/**
			 * Fires after the notification templates settings.
			 *
			 * @since 3.1.0
			 */
			do_action( 'after_awsm_notification_templates_settings' );
		?>

		<div class="awsm-form-footer">
			<?php echo apply_filters( 'awsm_job_settings_submit_btn', get_submit_button(), 'notification' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div><!-- .awsm-form-footer -->
	</div><!-- #settings-awsm-settings-notification-templates -->
</div><!-- .awsm-templates-notification-options-container -->
