<div class="awsm-form-section-main awsm-sub-options-container" id="awsm-builder-form-options-container" style="display: none;">
	<?php
		$default_form_id              = AWSM_Job_Openings_Pro_Form::get_default_form_id();
		$default_form_builder_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $default_form_id );

		// handle default form.
	if ( empty( $default_form_id ) ) {
		$form_id = wp_insert_post(
			array(
				'post_title'     => esc_html__( 'Default Form', 'pro-pack-for-wp-job-openings' ),
				'post_content'   => '',
				'post_status'    => 'publish',
				'comment_status' => 'closed',
				'post_type'      => 'awsm_job_form',
			)
		);
		if ( ! empty( $form_id ) && ! is_wp_error( $form_id ) ) {
			$form_builder_other_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( 'default', true );
			update_post_meta( $form_id, 'awsm_jobs_form_builder', $default_form_builder_options );
			update_post_meta( $form_id, 'awsm_jobs_form_builder_other_options', $form_builder_other_options );
			update_option(
				'awsm_jobs_forms',
				array(
					'default' => $form_id,
				)
			);
			$default_form_id = $form_id;
		}
	}

		$default_form_title = get_post_field( 'post_title', $default_form_id, 'raw' );
		$default_form_title = ! empty( $default_form_title ) ? $default_form_title : __( 'Default Form', 'pro-pack-for-wp-job-openings' );
		$default_edit_link  = get_edit_post_link( $default_form_id );
	?>
	<div class="awsm-jobs-settings-subtitle">
		<h2><?php esc_html_e( 'Manage Forms', 'pro-pack-for-wp-job-openings' ); ?></h2>
	</div>

	<div class="awsm-jobs-forms">

		<?php do_action( 'before_awsm_form_builder_settings' ); ?>

		<table class="awsm-jobs-forms-list-table widefat">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'pro-pack-for-wp-job-openings' ); ?></th>
					<th><?php esc_html_e( 'Name', 'pro-pack-for-wp-job-openings' ); ?></th>
					<th><?php esc_html_e( 'Fields', 'pro-pack-for-wp-job-openings' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr data-id="<?php echo esc_attr( $default_form_id ); ?>">
					<td><?php echo esc_html( $default_form_id ); ?></td>
					<td>
						<div class="awsm-jobs-forms-list-title">
							<a href="<?php echo esc_url( $default_edit_link ); ?>"><?php echo esc_html( $default_form_title ); ?></a>
						</div>
						<div class="awsm-jobs-forms-list-actions">
							<a href="<?php echo esc_url( $default_edit_link ); ?>" class="awsm-jobs-forms-list-edit-action"><?php esc_html_e( 'Edit', 'pro-pack-for-wp-job-openings' ); ?></a>
							<a href="#duplicate" class="awsm-jobs-forms-list-duplicate-action"><?php esc_html_e( 'Duplicate', 'pro-pack-for-wp-job-openings' ); ?></a>
						</div>
					</td>
					<td>
						<?php
							/* translators: %s: form fields count */
							printf( esc_html__( '%s fields', 'pro-pack-for-wp-job-openings' ), count( $default_form_builder_options ) );
						?>
					</td>
				</tr>
				<?php
					$forms = AWSM_Job_Openings_Pro_Form::get_forms(
						array(
							'post_status' => array( 'publish', 'future', 'draft', 'pending', 'private' ),
							'orderby'     => 'title ID',
							'order'       => 'ASC',
							'exclude'     => array( $default_form_id ),
						)
					);
					if ( ! empty( $forms ) ) :
						foreach ( $forms as $form ) :
							$edit_link            = get_edit_post_link( $form->ID );
							$form_builder_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form->ID );
							?>
					<tr data-id="<?php echo esc_attr( $form->ID ); ?>">
						<td><?php echo esc_html( $form->ID ); ?></td>
						<td>
							<div class="awsm-jobs-forms-list-title">
								<a href="<?php echo esc_url( $edit_link ); ?>"><?php echo esc_html( get_the_title( $form->ID ) ); ?></a>
								<?php
								if ( $form->post_status !== 'publish' ) {
									$post_status_data = get_post_status_object( $form->post_status );
									if ( ! empty( $post_status_data ) ) {
										printf( '<span class="awsm-jobs-forms-list-status"> &mdash; %s</span>', esc_html( $post_status_data->label ) );
									}
								}
								?>
							</div>
							<div class="awsm-jobs-forms-list-actions">
								<a href="<?php echo esc_url( $edit_link ); ?>" class="awsm-jobs-forms-list-edit-action"><?php esc_html_e( 'Edit', 'pro-pack-for-wp-job-openings' ); ?></a>
								<a href="#duplicate" class="awsm-jobs-forms-list-duplicate-action"><?php esc_html_e( 'Duplicate', 'pro-pack-for-wp-job-openings' ); ?></a>
								<a href="#delete" class="awsm-jobs-forms-list-delete-action"><?php esc_html_e( 'Delete', 'pro-pack-for-wp-job-openings' ); ?></a>
							</div>
						</td>
						<td>
							<?php
								/* translators: %s: form fields count */
								printf( esc_html__( '%s fields', 'pro-pack-for-wp-job-openings' ), count( $form_builder_options ) );
							?>
						</td>
					</tr>
							<?php
						endforeach;
					endif;
					?>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3">
						<div class="awsm-jobs-forms-list-buttons">
							<a class="button" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=awsm_job_form' ) ); ?>">+ <?php esc_html_e( 'New Form', 'pro-pack-for-wp-job-openings' ); ?></a>
						</div>
					</td>
				</tr>
			</tfoot>
		</table>

		<script type="text/html" id="tmpl-awsm-pro-fb-actions">
			<tr data-id="{{data.id}}">
				<td>{{data.id}}</td>
				<td>
					<div class="awsm-jobs-forms-list-title">
						<a href="{{data.editLink}}">{{data.title}}</a>
						<# if( data.status !== 'publish' ) { #>
							<span class="awsm-jobs-forms-list-status"> &mdash; {{data.statusText}}</span>
						<# } #>
					</div>
					<div class="awsm-jobs-forms-list-actions">
						<a href="{{data.editLink}}" class="awsm-jobs-forms-list-edit-action"><?php esc_html_e( 'Edit', 'pro-pack-for-wp-job-openings' ); ?></a>
						<a href="#duplicate" class="awsm-jobs-forms-list-duplicate-action"><?php esc_html_e( 'Duplicate', 'pro-pack-for-wp-job-openings' ); ?></a>
						<a href="#delete" class="awsm-jobs-forms-list-delete-action"><?php esc_html_e( 'Delete', 'pro-pack-for-wp-job-openings' ); ?></a>
					</div>
				</td>
				<td>
					<?php
						/* translators: %s: form fields count */
						printf( esc_html__( '%s fields', 'pro-pack-for-wp-job-openings' ), '{{data.fieldsCount}}' );
					?>
				</td>
			</tr>
		</script>

		<?php do_action( 'after_awsm_form_builder_settings' ); ?>

	</div><!-- .awsm-jobs-form-builder-main -->
</div><!-- #awsm-builder-form-options-container -->
