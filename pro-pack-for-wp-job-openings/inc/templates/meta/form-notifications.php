<?php
	$form_id              = $post->ID;
	$awsm_jobs_settings   = AWSM_Job_Openings_Pro_Settings::init();
	$applicant_options    = AWSM_Job_Openings_Pro_Form::get_notification_options_by_form_id( $form_id, 'applicant' );
	$admin_options        = AWSM_Job_Openings_Pro_Form::get_notification_options_by_form_id( $form_id, 'admin' );
	$from_email           = $applicant_options['from'];
	$admin_from_email     = $admin_options['from'];
	$from_email_error_msg = __( "The provided 'From' email address does not belong to this site domain and may lead to issues in email delivery.", 'wp-job-openings' );
?>

<div id="awsm-jobs-settings-section" class="awsm-job-form-notifications-container">
	<div id="awsm-job-form-notifications-meta-container" class="awsm-admin-settings">
		<div class="awsm-settings-col-left">
			<div class="awsm-sub-options-container">
				<div class="awsm-form-section-main awsm-acc-section-main">
					<div class="awsm-form-section awsm-acc-secton" id="settings-notification">
						<?php
							/**
							 * Fires before the form builder notifications content.
							 *
							 * @since 3.1.0
							 *
							 * @param int $form_id The Form ID.
							 */
							do_action( 'before_awsm_jobs_fb_notifications_mb_content', $form_id );
						?>
						<div class="awsm-acc-main awsm-acc-form-switch">
							<div class="awsm-acc-head on">
								<h3><?php echo esc_html__( 'Application Received - Applicant Notification', 'wp-job-openings' ); ?></h3>
								<label for="awsm_jobs_acknowledgement" class="awsm-toggle-switch">
									<input type="checkbox" class="awsm-form-notifications-switch" id="awsm_jobs_acknowledgement" name="awsm_jobs_form_applicant_notification[acknowledgement]" data-metakey="awsm_jobs_form_applicant_notification" data-formid="<?php echo esc_attr( $form_id ); ?>" value="acknowledgement" <?php checked( $applicant_options['acknowledgement'], 'acknowledgement' ); ?> />
									<span class="awsm-ts-label" data-on="<?php esc_html_e( 'ON', 'wp-job-openings' ); ?>" data-off="<?php esc_html_e( 'OFF', 'wp-job-openings' ); ?>"></span>
								<span class="awsm-ts-inner"></span>
								</label>
							</div><!-- .awsm-acc-head -->
							<div class="awsm-acc-content">
								<div class="awsm-row">
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_from_email_notification"><?php esc_html_e( 'From', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_form_applicant_notification[from]" id="awsm_jobs_from_email_notification" value="<?php echo esc_attr( $from_email ); ?>" required />
											<?php
											if ( $awsm_jobs_settings->validate_from_email_id( $from_email ) === false ) {
												printf( '<p class="description awsm-jobs-invalid">%s</p>', esc_html( $from_email_error_msg ) );
											}
											?>
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_reply_to_notification"><?php esc_html_e( 'Reply-To', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_form_applicant_notification[reply_to]" id="awsm_jobs_reply_to_notification" value="<?php echo esc_attr( $applicant_options['reply_to'] ); ?>" />
									</div><!-- .col -->
								</div>
								<div class="awsm-row">
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_applicant_notification"><?php esc_html_e( 'To', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_applicant_notification" id="awsm_jobs_applicant_notification" value="<?php echo esc_attr( '{applicant-email}' ); ?>" disabled />
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_hr_notification"><?php esc_html_e( 'CC:', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_form_applicant_notification[cc]" id="awsm_jobs_hr_notification" value="<?php echo esc_attr( $applicant_options['cc'] ); ?>" />
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-full">
										<label for="awsm-notification-subject"><?php esc_html_e( 'Subject ', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" id="awsm-notification-subject" name="awsm_jobs_form_applicant_notification[subject]" value="<?php echo esc_attr( $applicant_options['subject'] ); ?>" required />
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-full">
										<label for="awsm_jobs_notification_content"><?php esc_html_e( 'Content ', 'wp-job-openings' ); ?></label>
										<?php
										if ( function_exists( 'awsm_jobs_wp_editor' ) ) :
											awsm_jobs_wp_editor(
												$applicant_options['content'],
												'awsm-notification-content',
												array(
													'textarea_name' => 'awsm_jobs_form_applicant_notification[content]',
												)
											);
											else :
												?>
												<textarea class="awsm-form-control" id="awsm-notification-content" name="awsm_jobs_form_applicant_notification[content]" rows="5" cols="50" required><?php echo esc_textarea( $applicant_options['content'] ); ?></textarea>
												<?php
											endif;
											?>
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-full">
										<label for="awsm_jobs_notification_mail_template"><input type="checkbox" name="awsm_jobs_form_applicant_notification[html_template]" id="awsm_jobs_notification_mail_template" value="enable" <?php checked( $applicant_options['html_template'], 'enable' ); ?>><?php esc_html_e( 'Use HTML Template', 'wp-job-openings' ); ?></label>
									</div><!-- .col -->
								</div><!-- row -->
							</div><!-- .awsm-acc-content -->
						</div><!-- .awsm-acc-main -->
						<div class="awsm-acc-main awsm-acc-form-switch">
							<div class="awsm-acc-head">
								<h3><?php esc_html_e( 'Application Received - Admin Notification', 'wp-job-openings' ); ?></h3>
								<label for="awsm_jobs_enable_admin_notification" class="awsm-toggle-switch">
									<input type="checkbox" class="awsm-form-notifications-switch" id="awsm_jobs_enable_admin_notification" name="awsm_jobs_form_admin_notification[enable]" data-metakey="awsm_jobs_form_admin_notification" data-formid="<?php echo esc_attr( $form_id ); ?>" value="enable" <?php checked( $admin_options['enable'], 'enable' ); ?> />
									<span class="awsm-ts-label" data-on="<?php esc_html_e( 'ON', 'wp-job-openings' ); ?>" data-off="<?php esc_html_e( 'OFF', 'wp-job-openings' ); ?>"></span>
								<span class="awsm-ts-inner"></span>
								</label>
							</div><!-- .awsm-acc-head -->
							<div class="awsm-acc-content">
								<div class="awsm-row">
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_admin_from_email_notification"><?php esc_html_e( 'From', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_form_admin_notification[from]" id="awsm_jobs_admin_from_email_notification" value="<?php echo esc_attr( $admin_from_email ); ?>" required />
											<?php
											if ( $awsm_jobs_settings->validate_from_email_id( $admin_from_email ) === false ) {
												printf( '<p class="description awsm-jobs-invalid">%s</p>', esc_html( $from_email_error_msg ) );
											}
											?>
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_admin_reply_to_notification"><?php esc_html_e( 'Reply-To', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_form_admin_notification[reply_to]" id="awsm_jobs_admin_reply_to_notification" value="<?php echo esc_attr( $admin_options['reply_to'] ); ?>" />
									</div><!-- .col -->
								</div>
								<div class="awsm-row">
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_admin_to_notification"><?php esc_html_e( 'To', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_form_admin_notification[to]" id="awsm_jobs_admin_to_notification" value="<?php echo esc_attr( $admin_options['to'] ); ?>" placeholder="<?php esc_html__( 'Admin Email', 'wp-job-openings' ); ?>" required />
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-half">
										<label for="awsm_jobs_admin_hr_notification"><?php esc_html_e( 'CC:', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" name="awsm_jobs_form_admin_notification[cc]" id="awsm_jobs_admin_hr_notification" value="<?php echo esc_attr( $admin_options['cc'] ); ?>" />
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-full">
										<label for="awsm_jobs_admin_notification_subject"><?php esc_html_e( 'Subject ', 'wp-job-openings' ); ?></label>
											<input type="text" class="awsm-form-control" id="awsm_jobs_admin_notification_subject" name="awsm_jobs_form_admin_notification[subject]" value="<?php echo esc_attr( $admin_options['subject'] ); ?>" required />
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-full">
										<label for="awsm_jobs_admin_notification_content"><?php esc_html_e( 'Content ', 'wp-job-openings' ); ?></label>
											<?php
											if ( function_exists( 'awsm_jobs_wp_editor' ) ) :
												awsm_jobs_wp_editor(
													$admin_options['content'],
													'awsm_jobs_admin_notification_content',
													array(
														'textarea_name' => 'awsm_jobs_form_admin_notification[content]',
													)
												);
											else :
												?>
												<textarea class="awsm-form-control" id="awsm_jobs_admin_notification_content" name="awsm_jobs_form_admin_notification[content]" rows="5" cols="50" required><?php echo esc_textarea( $admin_options['content'] ); ?></textarea>
												<?php
											endif;
											?>
									</div><!-- .col -->
									<div class="awsm-col awsm-form-group awsm-col-full">
										<label for="awsm_jobs_notification_admin_mail_template"><input type="checkbox" name="awsm_jobs_form_admin_notification[html_template]" id="awsm_jobs_notification_admin_mail_template" value="enable" <?php checked( $admin_options['html_template'], 'enable' ); ?>><?php esc_html_e( 'Use HTML Template', 'wp-job-openings' ); ?></label>
									</div><!-- .col -->
								</div><!-- row -->
							</div><!-- .awsm-acc-content -->
						</div><!-- .awsm-acc-main -->
						<?php
							/**
							 * Fires after the form builder notifications content.
							 *
							 * @since 3.1.0
							 *
							 * @param int $form_id The Form ID.
							 */
							do_action( 'after_awsm_jobs_fb_notifications_mb_content', $form_id );
						?>
					</div><!-- .awsm-form-section -->
				</div><!-- .awsm-form-section-main -->
			</div><!-- #awsm-job-notification-options-container -->
		</div><!-- .awsm-settings-col-left -->

		<?php $template_tags = $awsm_jobs_settings->get_template_tags(); ?>

		<div class="awsm-settings-col-right">
			<div class="awsm-settings-aside">
				<h3><?php echo esc_html__( 'Template Tags', 'wp-job-openings' ); ?></h3>
				<ul class="awsm-job-template-tag-list">
					<?php
					foreach ( $template_tags as $template_tag => $tag_label ) {
						printf( '<li><span>%s</span><span>%s</span></li>', esc_html( $tag_label ), esc_html( $template_tag ) );
					}
					?>
				</ul>
			</div><!-- .awsm-settings-aside -->
		</div><!-- .awsm-settings-col-right -->
	</div><!-- .awsm-admin-settings -->
</div>
