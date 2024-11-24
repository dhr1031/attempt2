<?php
	$user_id        = get_current_user_id();
	$application_id = $post->ID;
	$applicant_mail = get_post_meta( $application_id, 'awsm_applicant_email', true );
	$applicant_cc   = get_post_meta( $application_id, 'awsm_mail_meta_applicant_cc', true );
	$ets_data       = get_option( 'awsm_jobs_pro_mail_templates' );
	$html_template  = get_user_meta( $user_id, 'awsm_applicant_mail_html_template', true );
?>

<div class="awsm-applicant-meta-mail-container">
	<div class="awsm-applicant-meta-mail-main posttypediv">
		<ul class="category-tabs awsm-applicant-meta-mail-tabs">
			<li class="tabs"><a href="#awsm-applicant-meta-new-mail"><?php esc_html_e( 'New Mail', 'pro-pack-for-wp-job-openings' ); ?></a></li>
			<li class="hide-if-no-js"><a href="#awsm-applicant-meta-sent-mails"><?php esc_html_e( 'Sent Mails', 'pro-pack-for-wp-job-openings' ); ?></a></li>
		</ul>
		<div id="awsm-applicant-meta-new-mail" class="tabs-panel awsm-applicant-meta-mail-tabs-panel">
			<div class="awsm-form-section-main">
				<div class="awsm-form-section">
					<div class="awsm-row">
						<div class="awsm-col awsm-form-group awsm-col-half">
							<label for="awsm_mail_meta_applicant_template"><?php esc_html_e( 'Email template', 'pro-pack-for-wp-job-openings' ); ?></label>
							<select name="awsm_mail_meta_applicant_template" id="awsm_mail_meta_applicant_template" class="awsm-form-control">
									<option value="" selected="selected"><?php echo esc_html_e( 'No template', 'pro-pack-for-wp-job-openings' ); ?></option>
									<?php
									if ( ! empty( $ets_data ) ) :
										foreach ( $ets_data as $et_data ) :
											?>
												<option value="<?php echo esc_attr( $et_data['key'] ); ?>"><?php echo esc_html( $et_data['name'] ); ?></option>
											<?php
											endforeach;
										endif;
									?>
							</select>
						</div><!-- .col -->
					</div><!-- row -->
					<div class="awsm-row" id="awsm_application_mail_ele">
						<?php
							/**
							 * Fires before main form fields in applicant emails meta box.
							 *
							 * @since 3.1.0
							 *
							 * @param int $application_id The Application ID.
							 */
							do_action( 'before_awsm_job_applicant_emails_mb_main_fields', $application_id );
						?>
						<div class="awsm-col awsm-form-group awsm-col-half">
							<label for="awsm_mail_meta_applicant_email"><?php esc_html_e( 'Applicant', 'pro-pack-for-wp-job-openings' ); ?></label>
								<input type="text" class="awsm-form-control" id="awsm_mail_meta_applicant_email" value="<?php echo esc_attr( $applicant_mail ); ?>" disabled />
						</div><!-- .col -->
						<div class="awsm-col awsm-form-group awsm-col-half">
							<label for="awsm_mail_meta_applicant_cc"><?php esc_html_e( 'CC:', 'wp-job-openings' ); ?></label>
								<input type="text" class="awsm-form-control awsm-applicant-mail-field" name="awsm_mail_meta_applicant_cc" id="awsm_mail_meta_applicant_cc" value="" />
						</div><!-- .col -->
						<div class="awsm-col awsm-form-group awsm-col-full">
							<label for="awsm_mail_meta_applicant_subject"><?php esc_html_e( 'Subject ', 'wp-job-openings' ); ?></label>
								<input type="text" class="awsm-form-control wide-fat awsm-applicant-mail-field" id="awsm_mail_meta_applicant_subject" name="awsm_mail_meta_applicant_subject" value="" />
						</div><!-- .col -->
						<div class="awsm-col awsm-form-group awsm-col-full">
							<label for="awsm_mail_meta_applicant_content"><?php esc_html_e( 'Content ', 'wp-job-openings' ); ?></label>
							<?php
							if ( function_exists( 'awsm_jobs_wp_editor' ) ) :
								awsm_jobs_wp_editor( '', 'awsm_mail_meta_applicant_content' );
								else :
									?>
									<textarea class="awsm-form-control awsm-applicant-mail-field" id="awsm_mail_meta_applicant_content" name="awsm_mail_meta_applicant_content" rows="5" cols="50"></textarea>
									<?php
								endif;
								?>
						</div><!-- .col -->
						<div class="awsm-col awsm-form-group awsm-col-full">
							<label for="awsm_mail_meta_applicant_html_template"><input type="checkbox" name="awsm_mail_meta_applicant_html" id="awsm_mail_meta_applicant_html_template" class="awsm-form-control awsm-applicant-mail-field" value="enable" <?php checked( $html_template, 'enable' ); ?>><?php esc_html_e( 'Use HTML Template', 'wp-job-openings' ); ?></label>
						</div><!-- .col -->
						<?php
							/**
							 * Fires after main form fields in applicant emails meta box.
							 *
							 * @since 3.1.0
							 *
							 * @param int $application_id The Application ID.
							 */
							do_action( 'after_awsm_job_applicant_emails_mb_main_fields', $application_id );
						?>
					</div>
					<ul class="awsm-list-inline">
						<li>
							<button type="button" name="awsm_applicant_mail_btn" class="button button-large" id="awsm_applicant_mail_btn" data-response-text="<?php esc_html_e( 'Sending...', 'pro-pack-for-wp-job-openings' ); ?>"><?php esc_html_e( 'Send', 'pro-pack-for-wp-job-openings' ); ?></button>
						</li>
					</ul>
					<div class="awsm-applicant-mail-message"></div>
				</div><!-- .awsm-form-section -->
			</div><!-- .awsm-form-section-main -->
		</div>
		<div id="awsm-applicant-meta-sent-mails" class="tabs-panel awsm-applicant-meta-mail-tabs-panel" style="display: none;">
			<div id="awsm-jobs-applicant-mails-container">
				<?php
					$mail_details = get_post_meta( $application_id, 'awsm_application_mails', true );
				if ( ! empty( $mail_details ) && is_array( $mail_details ) ) {
					$mail_details = array_reverse( $mail_details );
					foreach ( $mail_details as $mail_detail ) {
						$author_name = $mail_detail['send_by'] === 0 ? esc_html__( 'System', 'pro-pack-for-wp-job-openings' ) : $this->get_username( $mail_detail['send_by'] );
						if ( ! is_numeric( $mail_detail['mail_date'] ) ) {
							$mail_detail['mail_date'] = mysql2date( 'U', $mail_detail['mail_date'] );
						}
						$mail_date = date_i18n( __( 'M j, Y @ H:i', 'default' ), $mail_detail['mail_date'] );
						$this->applicant_mail_template(
							array(
								'author'    => $author_name,
								'date_i18n' => esc_html( $mail_date ),
								'subject'   => $mail_detail['subject'],
								'content'   => wpautop( $mail_detail['mail_content'] ),
							)
						);
					}
				} else {
					printf( '<div id="awsm_jobs_no_mail_wrapper"><p>%s</p></div>', esc_html__( 'No mails to show!', 'pro-pack-for-wp-job-openings' ) );
				}
				?>
			</div>
		</div>
	</div>
</div>

<script type="text/html" id="tmpl-awsm-pro-applicant-mail">
	<?php $this->applicant_mail_template(); ?>
</script>
