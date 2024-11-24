<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$job_id = $post->ID;
$cc     = get_post_meta( $job_id, 'awsm_job_cc_email_addresses', true );
?>

<div class="awsm-form-section">
	<?php
		/**
		 * Fires before cc email notifications meta box content.
		 *
		 * @since 3.1.0
		 *
		 * @param int $job_id The Job ID.
		 */
		do_action( 'before_awsm_job_cc_mb_content', $job_id );
	?>
	<div class="awsm-pro-forward-mail-container">
		<label for="awsm-cc-email-notification"><?php esc_html_e( 'Emails to CC	the application notifications', 'pro-pack-for-wp-job-openings' ); ?></label>
		<input type="text" name="awsm_cc_email_notification" class="widefat" id="awsm-cc-email-notification" value="<?php echo esc_attr( $cc ); ?>" />
		<p class="description"><?php esc_html_e( 'A copy of notifications for this application will be sent to the emails you submit.', 'pro-pack-for-wp-job-openings' ); ?></p>
	</div>
	<?php
		/**
		 * Fires after cc email notifications meta box content.
		 *
		 * @since 3.1.0
		 *
		 * @param int $job_id The Job ID.
		 */
		do_action( 'after_awsm_job_cc_mb_content', $job_id );
	?>
</div>
