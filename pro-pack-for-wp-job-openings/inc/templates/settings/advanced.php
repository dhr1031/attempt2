<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="settings-awsm-settings-advanced" class="awsm-admin-settings">
	<?php do_action( 'awsm_settings_form_elem_start', 'advanced' ); ?>

	<form method="POST" action="options.php" id="advanced_settings_form">
		<?php
		   settings_fields( 'awsm-jobs-advanced-settings' );
		?>
		<div class="awsm-sub-options-container awsm-job-settings-advaced-container awsm-jobs-loading-container" id="awsm-advanced-general-options-container">
			<?php
				$options = AWSM_Job_Openings_Pro_Main::get_application_status();
			?>
			<div id="settings-awsm-settings-manage-status">
				<?php
					/**
					 * Fires before the manage application status settings.
					 *
					 * @since 3.2.0
					 */
					do_action( 'before_awsm_advanced_manage_status_settings' );
				?>

				<table class="form-table awsm-manage-application-status-table">
					<tbody>
						<tr>
							<th scope="row" colspan="2" class="awsm-form-head-title">
								<h2 id="awsm-form-options-title"><?php esc_html_e( 'Manage Application Status', 'pro-pack-for-wp-job-openings' ); ?></h2>
							</th>
						</tr>
						<tr>
							<td scope="row" colspan="2" class="awsm-form-table-acc-section">
								<div class="awsm-form-section-main awsm-acc-section-main" id="awsm-repeatable-application-status" data-next="<?php echo ( ! empty( $options ) ) ? count( $options ) : 1; ?>">
									<div class="awsm-form-section awsm-acc-secton awsm-application-status-acc-section">
										<?php
										foreach ( $options as $index => $status_option ) {
											$this->status_template( $index, $status_option );
										}
										?>
										<div class="awsm-jobs-delete-status-modal-popup-wrapper awsm-hide">
											<div id="awsm-jobs-delete-status-modal" class="awsm-jobs-delete-status-modal awsm-jobs-loading-container">
												<div class="awsm-jobs-delete-status-modal-content">
													<div class="awsm-jobs-delete-status-modal-head">
														<h2 class="hndle"><?php echo esc_html__( 'Delete Status?', 'pro-pack-for-wp-job-openings' ); ?></h2>
														<button title="<?php esc_html_e( 'Close', 'pro-pack-for-wp-job-openings' ); ?>" type="button" class="awsm-jobs-delete-status-popup-close awsm-jobs-status-modal-dismiss"></button>
													</div>
													<div class="awsm-jobs-delete-status-modal-body">
														<div class="awsm-jobs-delete-status-confirm-msg">
															<p><?php echo esc_html__( 'Are you sure you want to delete the application status ', 'pro-pack-for-wp-job-openings' ); ?></p>
														</div>
														<div class="awsm-jobs-no-more-status-content-in awsm-hide">
															<div class="inside">
																<p><?php echo esc_html__( 'Please add new status', 'pro-pack-for-wp-job-openings' ); ?></p>
															</div>
														</div>
														<div class="awsm-jobs-delete-status-continue-content-in awsm-hide">
															<div class="inside">
																<div class="awsm-jobs-delete-status-confirm">
																	<button type="button" class="button button-primary button-large awsm-jobs-continue-status-btn" id="awsm-jobs-continue-status-btn"><?php esc_html_e( 'Continue', 'pro-pack-for-wp-job-openings' ); ?></button>
																	<a class="button button-large awsm-jobs-status-modal-dismiss" href="#"><?php esc_html_e( 'Cancel', 'pro-pack-for-wp-job-openings' ); ?></a>
																</div>
															</div>
														</div>
														<div class="awsm-jobs-delete-move-status-content-in awsm-hide">
															<div class="awsm-jobs-move-status-modal-content">
																<p><?php echo esc_html__( 'Select the new status you want to move the existing job under ', 'pro-pack-for-wp-job-openings' ); ?></p>
															</div>
															<div class="inside">
																<ul class="awsm-jobs-move-status-list">
																	<?php
																		$options = AWSM_Job_Openings_Pro_Main::get_application_status();
																	foreach ( $options as $index => $status_option ) {
																		if ( $index !== 'publish' && $index !== 'trash' ) {
																			?>
																												
																				<li><input type="radio" id="awsm_application_md_status-<?php echo esc_attr( $index ); ?>" name="awsm_application_status" value="<?php echo esc_attr( $index ); ?>" class="awsm-application-md-status" data-status="<?php echo esc_attr( $index ); ?>" /><label for="awsm_application_md_status-<?php echo esc_attr( $index ); ?>"><?php echo esc_html( $status_option['label'] ); ?>
																				</label></li>
																			<?php
																		}
																	}
																	?>
																	<input type="hidden" id="awsm-application-old-status" value="">
																	<input type="hidden" id="awsm-application-new-status-label" value="<?php echo esc_html( $status_option['label'] ); ?>">
																</ul>
															<button type="button" data-old-status="<?php echo esc_attr( $index ); ?>"  class="button button-primary button-large awsm-jobs-move-and-delete-status awsm-job-status-handle" data-action="move_status" id="awsm-jobs-move-and-delete-status"><?php esc_html_e( 'Move and Delete Status', 'pro-pack-for-wp-job-openings' ); ?></button>
															<a class="button button-large awsm-jobs-status-modal-dismiss" href="#"><?php esc_html_e( 'Cancel', 'pro-pack-for-wp-job-openings' ); ?></a>
															</div>
														</div>
														<div class="awsm-jobs-delete-status-confirm-content-in awsm-hide">
															<div class="inside">
															<div class="awsm-jobs-delete-status-confirm-actions">
																<button type="button" class="button button-primary button-large awsm-jobs-confirm-delete-status-btn awsm-job-status-handle" data-action="delete_status" id="awsm-jobs-confirm-delete-status-btn" ><?php esc_html_e( 'Confirm', 'pro-pack-for-wp-job-openings' ); ?></button>
																<a class="button button-large awsm-jobs-status-modal-dismiss" href="#"><?php esc_html_e( 'Cancel', 'pro-pack-for-wp-job-openings' ); ?></a>
															</div>
															</div>
														</div>
														<div class="awsm-select-status-message"></div>
													</div>
												</div>
											</div>
										</div>
									</div><!-- .awsm-form-section -->
								</div><!-- .awsm-form-section-main -->
								<p><a class="button awsm-add-application-status" href="#"><?php esc_html_e( 'Add new status', 'pro-pack-for-wp-job-openings' ); ?></a></p>
							</td>
						</tr>
					</tbody>
				</table>

				<!-- manage-application-status template -->
				<script type="text/html" id="tmpl-awsm-manage-application-status-settings">
					<?php $this->status_template( '{{data.index}}' ); ?>
				</script>
				<!-- /manage-application-status template -->

				<?php
					/**
					 * Fires after the manage application status settings.
					 *
					 * @since 3.2.0
					 */
					do_action( 'after_awsm_advanced_manage_status_settings' );
				?>

			</div><!-- #settings-awsm-settings-manage-status -->
		</div><!-- .awsm-sub-options-container -->
	</form>

	<?php do_action( 'awsm_settings_form_elem_end', 'advanced' ); ?>
</div><!-- .awsm-admin-settings -->
