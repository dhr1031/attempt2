<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AWSM_Job_Openings_Pro_Main {
	private static $instance = null;

	protected $cpath = null;

	public static $listing_strings = array();

	public static $job_detail_strings = array();

	public function __construct() {
		$this->cpath = untrailingslashit( plugin_dir_path( __FILE__ ) );
		add_action( 'init', array( $this, 'init_actions' ) );
		add_action( 'wp_loaded', array( $this, 'remove_hooks' ) );
		add_action( 'save_post', array( $this, 'save_awsm_jobs_posts' ), 100, 2 );
		add_action( 'transition_post_status', array( $this, 'status_notification_handler' ), 10, 3 );
		add_action( 'manage_awsm_job_application_posts_custom_column', array( $this, 'manage_job_application_posts_custom_column' ), 10, 2 );

		add_filter( 'post_updated_messages', array( $this, 'post_updated_messages' ) );
		add_filter( 'manage_awsm_job_application_posts_columns', array( $this, 'manage_job_application_posts_columns' ) );
		add_filter( 'views_edit-awsm_job_application', array( $this, 'awsm_job_application_edit_views' ), 100 );
		add_filter( 'awsm_applicant_photo', array( $this, 'applicant_photo' ) );
		add_filter( 'awsm_job_template_tags', array( $this, 'template_tags' ) );
		add_filter( 'awsm_jobs_mail_template_tags', array( $this, 'mail_template_tags' ), 11, 2 );

		add_filter( 'awsm_is_job_filters_visible', array( $this, 'is_job_filters_visible' ), 10, 2 );
		add_filter( 'awsm_active_job_filters', array( $this, 'active_job_filters' ), 10, 2 );
		add_filter( 'awsm_jobs_featured_image_content', array( $this, 'featured_image_content' ), 10, 2 );

		add_shortcode( 'awsmjobs_stats', array( $this, 'jobs_stats_shortcode' ) );
		add_shortcode( 'awsmjob_specs', array( $this, 'job_specs_shortcode' ) );

		$this->customize_listing_strings();
		$this->customize_job_detail_strings();
	}

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init_actions() {
		$this->register_post_types();
		$this->register_application_status();
	}

	public function remove_hooks() {
		remove_filter( 'views_edit-awsm_job_application', array( AWSM_Job_Openings::init(), 'awsm_job_application_action_links' ) );
	}

	public static function get_application_status( $force_defaults = false ) {
		$status = array(
			'publish'   => array(
				'label'       => _x( 'New', 'post status', 'pro-pack-for-wp-job-openings' ),
				/* translators: %s: posts count with publish status */
				'label_count' => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>', 'pro-pack-for-wp-job-openings' ),
				'color'       => '#e3c600',
			),
			'trash'     => array(
				'label'       => _x( 'Trashed', 'post status', 'pro-pack-for-wp-job-openings' ),
				/* translators: %s: posts count with trash status */
				'label_count' => _n_noop( 'Trash <span class="count">(%s)</span>', 'Trash <span class="count">(%s)</span>', 'default' ),
				'color'       => '#a92222',
			),
			'progress'  => array(
				'label'       => _x( 'In Progress', 'post status', 'pro-pack-for-wp-job-openings' ),
				/* translators: %s: posts count with progress status */
				'label_count' => _n_noop( 'In Progress <span class="count">(%s)</span>', 'In Progress <span class="count">(%s)</span>', 'pro-pack-for-wp-job-openings' ),
				'color'       => '#ca6e0a',
			),
			'shortlist' => array(
				'label'       => _x( 'Shortlisted', 'post status', 'pro-pack-for-wp-job-openings' ),
				/* translators: %s: posts count with shortlisted status */
				'label_count' => _n_noop( 'Shortlisted <span class="count">(%s)</span>', 'Shortlisted <span class="count">(%s)</span>', 'pro-pack-for-wp-job-openings' ),
				'color'       => '#06d7ad',
			),
			'reject'    => array(
				'label'       => _x( 'Rejected', 'post status', 'pro-pack-for-wp-job-openings' ),
				/* translators: %s: posts count with rejected status */
				'label_count' => _n_noop( 'Rejected <span class="count">(%s)</span>', 'Rejected <span class="count">(%s)</span>', 'pro-pack-for-wp-job-openings' ),
				'color'       => '#a92222',
			),
			'select'    => array(
				'label'       => _x( 'Selected', 'post status', 'pro-pack-for-wp-job-openings' ),
				/* translators: %s: posts count with selected status */
				'label_count' => _n_noop( 'Selected <span class="count">(%s)</span>', 'Selected <span class="count">(%s)</span>', 'pro-pack-for-wp-job-openings' ),
				'color'       => '#1ea508',
			),
		);

		if ( ! $force_defaults ) {
			$options = get_option( 'awsm_jobs_application_status' );
			if ( ! empty( $options ) && is_array( $options ) && isset( $options['publish'] ) && isset( $options['trash'] ) ) {
				$status = $options;
			}
		}
		/**
		 * Filters the job application status array.
		 *
		 * @since 2.0.0
		 *
		 * @param array $status Application status array.
		 */
		$status = apply_filters( 'awsm_jobs_pro_application_status', $status );

		// Protect core status.
		$status['publish']['default'] = true;
		$status['trash']['default']   = true;

		return $status;
	}

	public function manage_job_application_posts_columns( $columns ) {
		$columns['awsm-application-status'] = esc_attr__( 'Status', 'pro-pack-for-wp-job-openings' );
		if ( current_user_can( 'edit_others_applications' ) ) {
			$columns['awsm-application-notes']  = esc_attr__( 'Notes', 'pro-pack-for-wp-job-openings' );
			$columns['awsm-application-rating'] = esc_attr__( 'Rating', 'pro-pack-for-wp-job-openings' );
		}
		return $columns;
	}

	public function manage_job_application_posts_custom_column( $columns, $post_id ) {
		switch ( $columns ) {
			case 'awsm-application-status':
				$post_status      = get_post_status( $post_id );
				$available_status = self::get_application_status();
				$label            = isset( $available_status[ $post_status ] ) ? $available_status[ $post_status ]['label'] : $available_status['publish']['label'];
				$class_name       = "awsm-application-status-label awsm-application-{$post_status}-status";
				printf( '<span class="%2$s">%1$s</span>', esc_html( $label ), esc_attr( $class_name ) );
				break;

			case 'awsm-application-notes':
				$notes = get_post_meta( $post_id, 'awsm_application_notes', true );
				if ( current_user_can( 'edit_others_applications' ) && ! empty( $notes ) && is_array( $notes ) ) {
					$total_notes = count( $notes );
					/* translators: %s: Job application notes count */
					printf( '<p><span class="dashicons dashicons-admin-comments"></span> %s</p>', esc_html( sprintf( _n( '%s Note', '%s Notes', $total_notes, 'pro-pack-for-wp-job-openings' ), $total_notes ) ) );
				} else {
					echo '<span aria-hidden="true">—</span>';
				}
				break;

			case 'awsm-application-rating':
				$rating = get_post_meta( $post_id, 'awsm_application_rating', true );
				$rating = ! empty( $rating ) ? $rating : 0;
				if ( current_user_can( 'edit_others_applications' ) ) {
					wp_star_rating(
						array(
							'rating' => (int) $rating,
							'type'   => 'rating',
						)
					);
				} else {
					echo '<span aria-hidden="true">—</span>';
				}
				break;
		}
	}

	public function register_post_types() {

		if ( post_type_exists( 'awsm_job_form' ) ) {
			return;
		}

		$labels = array(
			'name'                     => __( 'Forms', 'pro-pack-for-wp-job-openings' ),
			'singular_name'            => __( 'Form', 'pro-pack-for-wp-job-openings' ),
			'add_new'                  => __( 'New Form', 'pro-pack-for-wp-job-openings' ),
			'add_new_item'             => __( 'Add New Form', 'pro-pack-for-wp-job-openings' ),
			'edit_item'                => __( 'Edit Form', 'pro-pack-for-wp-job-openings' ),
			'new_item'                 => __( 'New Form', 'pro-pack-for-wp-job-openings' ),
			'search_items'             => __( 'Search Form', 'pro-pack-for-wp-job-openings' ),
			'not_found'                => __( 'No Forms found', 'pro-pack-for-wp-job-openings' ),
			'not_found_in_trash'       => __( 'No Forms found in Trash', 'pro-pack-for-wp-job-openings' ),
			'parent_item_colon'        => __( 'Parent Form :', 'pro-pack-for-wp-job-openings' ),
			'menu_name'                => __( 'Forms', 'pro-pack-for-wp-job-openings' ),
			'view_item'                => __( 'View Form', 'pro-pack-for-wp-job-openings' ),
			'view_items'               => __( 'View Forms', 'pro-pack-for-wp-job-openings' ),
			'item_published'           => __( 'Form published.', 'pro-pack-for-wp-job-openings' ),
			'item_published_privately' => __( 'Form published privately.', 'pro-pack-for-wp-job-openings' ),
			'item_reverted_to_draft'   => __( 'Form reverted to draft.', 'pro-pack-for-wp-job-openings' ),
			'item_scheduled'           => __( 'Form scheduled.', 'pro-pack-for-wp-job-openings' ),
			'item_updated'             => __( 'Form updated.', 'pro-pack-for-wp-job-openings' ),
		);

		/**
		 * Filters 'awsm_job_form' post type arguments.
		 *
		 * @since 3.0.0
		 *
		 * @param array $args arguments.
		 */
		$args = apply_filters(
			'awsm_job_form_args',
			array(
				'labels'            => $labels,
				'public'            => false,
				'show_ui'           => true,
				'show_in_menu'      => false,
				'show_in_admin_bar' => false,
				'show_in_rest'      => false,
				'map_meta_cap'      => true,
				'capability_type'   => 'job',
				'supports'          => array( 'title' ),
				'rewrite'           => false,
			)
		);

		register_post_type( 'awsm_job_form', $args );
	}

	public function post_updated_messages( $messages ) {
		global $post;

		$scheduled_date = date_i18n( get_awsm_jobs_date_format( 'scheduled-date' ) . ' @ ' . get_awsm_jobs_time_format( 'scheduled-date' ), strtotime( $post->post_date ) );

		$messages['awsm_job_form'] = array(
			0  => '', // Unused. Messages start at index 1.
			1  => __( 'Form updated.', 'pro-pack-for-wp-job-openings' ),
			2  => __( 'Custom field updated.', 'default' ),
			3  => __( 'Custom field deleted.', 'default' ),
			4  => __( 'Form updated.', 'pro-pack-for-wp-job-openings' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Form restored to revision from %s.', 'pro-pack-for-wp-job-openings' ), wp_post_revision_title( intval( $_GET['revision'] ), false ) ) : false,
			6  => __( 'Form published.', 'pro-pack-for-wp-job-openings' ),
			7  => __( 'Form saved.', 'pro-pack-for-wp-job-openings' ),
			8  => __( 'Form submitted.', 'pro-pack-for-wp-job-openings' ),
			/* translators: %s: scheduled date */
			9  => sprintf( __( 'Form scheduled for: %s.', 'pro-pack-for-wp-job-openings' ), '<strong>' . $scheduled_date . '</strong>' ),
			10 => __( 'Form draft updated.', 'pro-pack-for-wp-job-openings' ),
		);
		return $messages;
	}

	public function register_application_status() {
		$status = self::get_application_status();
		foreach ( $status as $name => $args ) {
			$default = isset( $args['default'] ) ? $args['default'] : false;
			if ( $default === false ) {
				register_post_status(
					$name,
					array(
						'label'                     => $args['label'],
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						'label_count'               => $args['label_count'],
					)
				);
			}
		}
	}

	public function awsm_job_application_edit_views( $views ) {
		$remove_views = array( 'mine', 'future', 'sticky', 'draft', 'pending' );
		foreach ( $remove_views as $view ) {
			if ( isset( $views[ $view ] ) ) {
				unset( $views[ $view ] );
			}
		}
		if ( isset( $views['publish'] ) ) {
			$status_options   = self::get_application_status();
			$views['publish'] = str_replace( esc_html__( 'Published', 'default' ), $status_options['publish']['label'], $views['publish'] );
		}
		return $views;
	}

	public function save_awsm_jobs_posts( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['awsm_jobs_posts_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['awsm_jobs_posts_nonce'], 'awsm_save_post_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/**
		 * Fires once a post has been saved.
		 *
		 * @since 3.2.0
		 *
		 * @param int $post_id Post ID.
		 * @param WP_Post $post Post object.
		 */
		do_action( 'awsm_jobs_pro_save_post', $post_id, $post );

		if ( $post->post_type === 'awsm_job_openings' ) {
			$cc_addresses = isset( $_POST['awsm_cc_email_notification'] ) ? sanitize_text_field( $_POST['awsm_cc_email_notification'] ) : '';
			update_post_meta( $post_id, 'awsm_job_cc_email_addresses', $cc_addresses );

			if ( isset( $_POST['awsm_jobs_custom_application_form'] ) && is_array( $_POST['awsm_jobs_custom_application_form'] ) ) {
				$form      = $_POST['awsm_jobs_custom_application_form'];
				$form_data = self::sanitize_custom_form_data( $form );
				update_post_meta( $post_id, 'awsm_pro_application_form', $form_data );
				$job_options = array( 'awsm_pro_exclude_job', 'awsm_job_filled' );
				foreach ( $job_options as $job_option ) {
					$option_value = isset( $_POST[ $job_option ] ) ? sanitize_text_field( $_POST[ $job_option ] ) : '';
					update_post_meta( $post_id, $job_option, $option_value );
				}
			}
		}

		if ( $post->post_type === 'awsm_job_application' ) {
			$user_id      = get_current_user_id();
			$current_time = current_time( 'timestamp' );

			if ( isset( $_POST['awsm_job_move_application'] ) ) {
				$new_job_id    = intval( $_POST['awsm_job_move_application'] );
				$updated       = update_post_meta( $post_id, 'awsm_job_id', $new_job_id );
				$old_job       = get_post_meta( $post->ID, 'awsm_apply_for', true );
				$new_job_title = html_entity_decode( esc_html( get_the_title( $new_job_id ) ) );
				update_post_meta( $post_id, 'awsm_apply_for', $new_job_title );
				$post_data = array(
					'ID'          => $post_id,
					'post_parent' => $new_job_id,
				);

				remove_action( 'save_post', array( $this, 'save_awsm_jobs_posts' ), 100 );
				wp_update_post( $post_data );
				add_action( 'save_post', array( $this, 'save_awsm_jobs_posts' ), 100, 2 );

				if ( $updated ) {
					$activities   = get_post_meta( $post_id, 'awsm_application_activity_log', true );
					$activities   = ! empty( $activities ) && is_array( $activities ) ? $activities : array();
					$activities[] = array(
						'user'          => $user_id,
						'activity_date' => $current_time,
						'old_job'       => $old_job,
						'new_job'       => $new_job_title,
					);
					update_post_meta( $post_id, 'awsm_application_activity_log', $activities );
				}
			}

			if ( isset( $_POST['awsm_application_rating'] ) ) {
				$rating = intval( $_POST['awsm_application_rating'] );
				if ( $rating ) {
					$updated = update_post_meta( $post_id, 'awsm_application_rating', $rating );
					if ( $updated ) {
						$activities   = get_post_meta( $post_id, 'awsm_application_activity_log', true );
						$activities   = ! empty( $activities ) && is_array( $activities ) ? $activities : array();
						$activities[] = array(
							'user'          => $user_id,
							'activity_date' => $current_time,
							'rating'        => $rating,
						);
						update_post_meta( $post_id, 'awsm_application_activity_log', $activities );
					}
				}
			}

			if ( isset( $_POST['awsm_application_notes'] ) && ! empty( $_POST['awsm_application_notes'] ) ) {
				$notes   = get_post_meta( $post_id, 'awsm_application_notes', true );
				$notes   = ! empty( $notes ) && is_array( $notes ) ? $notes : array();
				$notes[] = array(
					'author_id'     => $user_id,
					'notes_date'    => $current_time,
					'notes_content' => sanitize_text_field( $_POST['awsm_application_notes'] ),
				);
				update_post_meta( $post_id, 'awsm_application_notes', $notes );
			}

			$status         = get_post_status( $post_id );
			$status_options = self::get_application_status();
			unset( $status_options['publish'], $status_options['trash'] );
			if ( in_array( $status, array_keys( $status_options ), true ) ) {
				$activities        = get_post_meta( $post_id, 'awsm_application_activity_log', true );
				$activities        = ! empty( $activities ) && is_array( $activities ) ? $activities : array();
				$is_status_changed = true;
				$status_activity   = array();
				foreach ( $activities as $activity ) {
					if ( isset( $activity['status'] ) ) {
						$status_activity[] = $activity['status'];
					}
				}
				if ( ! empty( $status_activity ) ) {
					$last_status = end( $status_activity );
					if ( $status === $last_status ) {
						$is_status_changed = false;
					}
				}
				if ( $is_status_changed ) {
					$activities[] = array(
						'user'          => $user_id,
						'activity_date' => $current_time,
						'status'        => $status,
					);
					update_post_meta( $post_id, 'awsm_application_activity_log', $activities );
				}
			}
		}

		if ( $post->post_type === 'awsm_job_form' ) {
			$awsm_jobs_settings         = AWSM_Job_Openings_Pro_Settings::init();
			$form_builder_options       = $awsm_jobs_settings->form_builder_handler( $_POST['awsm_jobs_form_builder'], $post_id );
			$form_builder_other_options = $awsm_jobs_settings->form_builder_other_options_handler( $_POST['awsm_jobs_form_builder_other_options'] );
			update_post_meta( $post_id, 'awsm_jobs_form_builder', $form_builder_options );
			update_post_meta( $post_id, 'awsm_jobs_form_builder_other_options', $form_builder_other_options );

			$default_form_id = AWSM_Job_Openings_Pro_Form::get_default_form_id();
			if ( $post_id === $default_form_id ) {
				update_option( 'awsm_jobs_form_builder', $form_builder_options );
				update_option( 'awsm_jobs_form_builder_other_options', $form_builder_other_options );
			}

			// Notifications.
			$notifications_types        = array( 'applicant', 'admin' );
			$notifications_options_keys = array( 'acknowledgement', 'enable', 'from', 'reply_to', 'to', 'cc', 'subject', 'content', 'html_template' );
			foreach ( $notifications_types as $notifications_type ) {
				if ( isset( $_POST[ "awsm_jobs_form_{$notifications_type}_notification" ] ) ) {
					$notification_options = $_POST[ "awsm_jobs_form_{$notifications_type}_notification" ];
					if ( is_array( $notification_options ) ) {
						foreach ( $notification_options as $option_key => $option_val ) {
							if ( in_array( $option_key, $notifications_options_keys ) ) {
								if ( $option_key === 'from' ) {
									$notification_options[ $option_key ] = method_exists( $awsm_jobs_settings, 'sanitize_from_email_id' ) ? $awsm_jobs_settings->sanitize_from_email_id( $option_val ) : sanitize_email( $option_val );
								} elseif ( $option_key === 'content' ) {
									if ( empty( $option_val ) ) {
										if ( $notifications_type === 'admin' ) {
											$option_val = AWSM_Job_Openings_Settings::get_default_settings( 'awsm_jobs_admin_notification_content' );
										} else {
											$option_val = AWSM_Job_Openings_Settings::get_default_settings( 'awsm_jobs_notification_content' );
										}
									}
									$notification_options[ $option_key ] = wp_kses_post( $option_val );
								} else {
									$notification_options[ $option_key ] = sanitize_text_field( $option_val );
								}
							} else {
								unset( $notification_options[ $option_key ] );
							}
						}
						update_post_meta( $post_id, "awsm_jobs_form_{$notifications_type}_notification", $notification_options );
					}
				}
			}
			// Set the Form subtab to Form Builder.
			update_option( 'awsm_current_form_subtab', 'awsm-builder-form-nav-subtab' );
		}
	}

	public function status_notification_handler( $new_status, $old_status, $post ) {
		if ( $new_status !== 'publish' && $new_status !== $old_status && $post->post_type === 'awsm_job_application' ) {
			$status_options = get_option( 'awsm_jobs_application_status' );
			if ( is_array( $status_options ) ) {
				$post_id            = $post->ID;
				$awsm_jobs_pro_meta = AWSM_Job_Openings_Pro_Meta::init();
				$tags               = $awsm_jobs_pro_meta->get_mail_template_tags_from_meta( $post_id );
				$tag_names          = array_keys( $tags );
				$tag_values         = array_values( $tags );
				$templates          = get_option( 'awsm_jobs_pro_mail_templates' );
				if ( ! empty( $templates ) && isset( $status_options[ $new_status ] ) ) {
					$status_option = $status_options[ $new_status ];
					if ( isset( $status_option['notification'] ) && $status_option['notification'] === 'yes' && ! empty( $status_option['mail_template'] ) ) {
						$mail_template = array();
						foreach ( $templates as $template ) {
							if ( $template['key'] === $status_option['mail_template'] ) {
								$mail_template = $template;
								break;
							}
						}
						if ( ! empty( $mail_template ) ) {
							$wpml_reg_name        = 'Notification Template: ' . $mail_template['name'];
							$subject              = str_replace( $tag_names, $tag_values, apply_filters( 'wpml_translate_single_string', $mail_template['subject'], 'pro-pack-for-wp-job-openings', $wpml_reg_name . ' - Subject', AWSM_Job_Openings::get_current_language() ) );
							$notification_content = str_replace( $tag_names, $tag_values, apply_filters( 'wpml_translate_single_string', $mail_template['content'], 'pro-pack-for-wp-job-openings', $wpml_reg_name . ' - Content', AWSM_Job_Openings::get_current_language() ) );
							$hr_email             = get_option( 'awsm_hr_email_address' );
							$cc                   = get_option( 'awsm_jobs_hr_notification', $hr_email );
							$html_template        = get_option( 'awsm_jobs_notification_mail_template' );
							$mail_data            = array(
								'send_by'       => get_current_user_id(),
								'mail_date'     => current_time( 'timestamp' ),
								'cc'            => $cc,
								'subject'       => $subject,
								'mail_content'  => $notification_content,
								'html_template' => $html_template,
							);
							$is_sent              = $awsm_jobs_pro_meta->applicant_notification( $post_id, $mail_data );

							if ( $is_sent ) {
								AWSM_Job_Openings_Pro_Meta::update_application_mails( $post_id, $mail_data );
							}
						}
					}
				}
			}
		}
	}

	public static function sanitize_custom_form_data( $form ) {
		$form_data = array(
			'id' => 'default',
		);
		$form_id   = isset( $form['id'] ) ? sanitize_text_field( $form['id'] ) : '';
		if ( ! empty( $form_id ) ) {
			$update_data = false;
			if ( is_numeric( $form_id ) ) {
				if ( get_post_type( absint( $form_id ) ) === 'awsm_job_form' ) {
					$update_data = true;
				}
			} if ( $form_id === 'custom_button' ) {
				if ( isset( $form['button'] ) && isset( $form['button']['url'], $form['button']['text'] ) ) {
					$url  = $form['button']['url'];
					$text = $form['button']['text'];
					if ( ! empty( $url ) && ! empty( $text ) ) {
						$form_data['button'] = array(
							'url'    => esc_url_raw( $url ),
							'text'   => wp_kses(
								$text,
								array(
									'i'      => array(
										'class' => array(),
									),
									'span'   => array(
										'class' => array(),
									),
									'strong' => array(
										'class' => array(),
									),
								)
							),
							'target' => isset( $form['button']['target'] ) ? sanitize_text_field( $form['button']['target'] ) : '',
						);

						$update_data = true;
					}
				}
			} elseif ( $form_id === 'custom_form' ) {
				if ( isset( $form['shortcode'] ) && ! empty( $form['shortcode'] ) ) {
					$update_data            = true;
					$form_data['shortcode'] = AWSM_Job_Openings_Pro_Form::sanitize_custom_form_content( $form['shortcode'] );
				}
			} elseif ( $form_id === 'disable' ) {
				$update_data = true;
			}
			if ( $update_data ) {
				$form_data['id'] = $form_id;
			}
		}
		return $form_data;
	}

	public static function get_applicant_photo_data_uri( $photo_id, $is_remote = false ) {
		$data_uri = false;
		$file     = '';
		if ( is_numeric( $photo_id ) ) {
			$file = get_attached_file( $photo_id );
		} else {
			$file = $photo_id;
		}
		if ( ! empty( $file ) ) {
			$mimetype     = false;
			$file_content = false;
			if ( ! $is_remote ) {
				if ( is_numeric( $photo_id ) ) {
					$mimetype = wp_get_image_mime( $file );
				}
				$file_content = @file_get_contents( $file );
			} else {
				$image = wp_remote_get( $file );
				if ( ! is_wp_error( $image ) ) {
					$mimetype = wp_remote_retrieve_header( $image, 'content-type' );
					if ( ! empty( $mimetype ) && wp_remote_retrieve_response_code( $image ) === 200 ) {
						$file_content = wp_remote_retrieve_body( $image );
					}
				}
			}

			if ( ! empty( $mimetype ) && ! empty( $file_content ) ) {
				$data_uri = sprintf( 'data:%1$s;base64,%2$s', $mimetype, base64_encode( $file_content ) );
			}
		}
		return $data_uri;
	}

	public function applicant_photo( $avatar ) {
		global $post;
		if ( isset( $post ) ) {
			$custom_fields = get_post_meta( $post->ID, 'awsm_applicant_custom_fields', true );
			$photo_id      = isset( $custom_fields['awsm_applicant_photo'] ) ? $custom_fields['awsm_applicant_photo']['value'] : '';
			if ( ! empty( $photo_id ) ) {
				$photo_url = '';
				if ( get_option( 'awsm_hide_uploaded_files' ) === 'hide_files' ) {
					$photo_url = self::get_applicant_photo_data_uri( $photo_id );
				} else {
					$photo_url = wp_get_attachment_url( $photo_id );
				}
				if ( ! empty( $photo_url ) ) {
					$attrs_content = 'class="avatar photo avatar-%1$s" width="%1$s"';
					$attrs         = sprintf( $attrs_content, 32 );
					$screen        = get_current_screen();
					if ( ! empty( $screen ) ) {
						if ( $screen->base === 'post' ) {
							$attrs = sprintf( $attrs_content, 130 );
						}
					}
					$avatar = sprintf( '<img src="%s" %s />', $photo_url, $attrs );
				}
			}
		}
		return $avatar;
	}

	public function template_tags( $tags ) {
		$awsm_filters = get_option( 'awsm_jobs_filter' );
		if ( ! empty( $awsm_filters ) ) {
			foreach ( $awsm_filters as $awsm_filter ) {
				$tag          = '{' . $awsm_filter['taxonomy'] . '}';
				$tags[ $tag ] = $awsm_filter['filter'] . ':';
			}
		}

		return $tags;
	}

	public function mail_template_tags( $tags, $applicant_details ) {
		$job_id       = $applicant_details['awsm_job_id'];
		$awsm_filters = get_option( 'awsm_jobs_filter' );
		if ( ! empty( $awsm_filters ) ) {
			$spec_keys = wp_list_pluck( $awsm_filters, 'taxonomy' );
			foreach ( $spec_keys as $spec_key ) {
				$tag          = '{' . $spec_key . '}';
				$tags[ $tag ] = '';
				$spec_terms   = wp_get_post_terms( $job_id, $spec_key );
				if ( ! is_wp_error( $spec_terms ) && is_array( $spec_terms ) ) {
					$labels = wp_list_pluck( $spec_terms, 'name' );
					if ( ! empty( $labels ) ) {
						$tags[ $tag ] = implode( ', ', $labels ); // if there are multiple specifications, then it will be separated by a comma.
					}
				}
			}
		}

		return $tags;
	}

	public static function get_translated_strings( $option_name, $defaults, $translate ) {
		$custom_strings = get_option( $option_name );
		$mod_strings    = wp_parse_args( $custom_strings, $defaults );
		if ( empty( $custom_strings ) ) {
			$mod_strings['_default'] = true;
		} else {
			if ( $translate ) {
				foreach ( $mod_strings as $key => $mod_string ) {
					$mod_strings[ $key ] = apply_filters( 'wpml_translate_single_string', $mod_string, "admin_texts_{$option_name}", "[{$option_name}]{$key}" );
				}
			}
		}
		return $mod_strings;
	}

	public static function get_listing_strings( $translate = false ) {
		$defaults            = array(
			'filter_prefix'      => _x( 'All', 'job filter', 'wp-job-openings' ),
			'filter_suffix'      => '',
			'search_placeholder' => _x( 'Search', 'job filter', 'wp-job-openings' ),
			'more_details'       => __( 'More Details', 'wp-job-openings' ),
			'load_more'          => __( 'Load more...', 'wp-job-openings' ),
			'loading'            => __( 'Loading...', 'wp-job-openings' ),
			'no_filtered_jobs'   => __( 'Sorry! No jobs to show.', 'wp-job-openings' ),
		);
		$mod_listing_strings = self::get_translated_strings( 'awsm_jobs_customize_job_listing_strings', $defaults, $translate );
		return $mod_listing_strings;
	}

	public static function get_job_detail_strings( $translate = false ) {
		$defaults           = array(
			'expired_job'      => __( 'Sorry! This job has expired.', 'wp-job-openings' ),
			'position_filled'  => __( 'This job is no longer accepting applications.', 'pro-pack-for-wp-job-openings' ),
			'back_to_listings' => __( 'Back to listings', 'pro-pack-for-wp-job-openings' ),
		);
		$mod_detail_strings = self::get_translated_strings( 'awsm_jobs_customize_job_detail_strings', $defaults, $translate );
		return $mod_detail_strings;
	}

	public function customize_listing_strings() {
		self::$listing_strings = self::get_listing_strings( true );
		if ( ! isset( self::$listing_strings['_default'] ) ) {
			add_filter( 'awsm_filter_label', array( $this, 'filter_label' ) );
			add_filter( 'awsm_jobs_search_field_placeholder', array( $this, 'search_field_placeholder' ) );
			add_filter( 'awsm_jobs_listing_details_link', array( $this, 'listing_details_link' ) );
			add_filter( 'awsm_jobs_load_more_content', array( $this, 'load_more_content' ) );
			add_filter( 'awsm_no_filtered_jobs_content', array( $this, 'no_filtered_jobs_content' ) );
		}
	}

	public function customize_job_detail_strings() {
		self::$job_detail_strings = self::get_job_detail_strings( true );
		if ( ! isset( self::$job_detail_strings['_default'] ) ) {
			add_filter( 'awsm_job_expired_content', array( $this, 'expired_job_content' ), 10, 4 );
		}
	}

	public function filter_label( $label ) {
		$label = str_replace( esc_html_x( 'All', 'job filter', 'wp-job-openings' ), esc_html( self::$listing_strings['filter_prefix'] ), $label );
		if ( ! empty( self::$listing_strings['filter_suffix'] ) ) {
			$label .= ' ' . esc_html( self::$listing_strings['filter_suffix'] );
		}
		return $label;
	}

	public function search_field_placeholder() {
		return self::$listing_strings['search_placeholder'];
	}

	public function listing_details_link( $more_dtls_link ) {
		$more_dtls_link = str_replace( esc_html__( 'More Details', 'wp-job-openings' ), esc_html( self::$listing_strings['more_details'] ), $more_dtls_link );
		return $more_dtls_link;
	}

	public function load_more_content( $load_more_content ) {
		$load_more_content = str_replace( esc_html__( 'Load more...', 'wp-job-openings' ), esc_html( self::$listing_strings['load_more'] ), $load_more_content );
		return $load_more_content;
	}

	public function no_filtered_jobs_content( $no_jobs_content ) {
		$no_jobs_content = str_replace( esc_html__( 'Sorry! No jobs to show.', 'wp-job-openings' ), esc_html( self::$listing_strings['no_filtered_jobs'] ), $no_jobs_content );
		return $no_jobs_content;
	}

	public function expired_job_content( $content, $msg, $before, $after ) {
		$msg     = self::$job_detail_strings['expired_job'];
		$content = $before . $msg . $after;
		return $content;
	}

	public function is_job_filters_visible( $display_filters, $shortcode_atts ) {
		if ( isset( $shortcode_atts['filters'] ) && ! empty( $shortcode_atts['specs'] ) && $shortcode_atts['filters'] === 'partial' ) {
			$display_filters = true;
		}
		return $display_filters;
	}

	public function active_job_filters( $available_filters, $shortcode_atts ) {
		if ( isset( $shortcode_atts['filters'] ) && ! empty( $shortcode_atts['specs'] ) && $shortcode_atts['filters'] === 'partial' ) {
			$specs            = explode( ',', $shortcode_atts['specs'] );
			$inactive_filters = array();
			foreach ( $specs as $spec ) {
				if ( strpos( $spec, ':' ) !== false ) {
					list( $taxonomy )   = explode( ':', $spec );
					$inactive_filters[] = trim( $taxonomy );
				}
			}
			$available_filters = array_values( array_diff( $available_filters, $inactive_filters ) );
		}
		return $available_filters;
	}

	public function featured_image_content( $content, $post_thumbnail_id ) {
		if ( ! empty( $content ) ) {
			$image_size = get_option( 'awsm_jobs_listing_featured_image_size' );
			if ( ! empty( $image_size ) && $image_size !== 'thumbnail' && ! is_singular( 'awsm_job_openings' ) ) {
				$content = wp_get_attachment_image( $post_thumbnail_id, $image_size );
			}
		}
		return $content;
	}

	public function jobs_stats_shortcode( $atts ) {
		$pairs          = array(
			'status' => 'default',
		);
		$shortcode_atts = shortcode_atts( $pairs, $atts, 'awsmjobs_stats' );

		$args = array(
			'post_type'   => 'awsm_job_openings',
			'numberposts' => -1,
			'fields'      => 'ids',
		);

		if ( $shortcode_atts['status'] === 'default' ) {
			$hide_expired_jobs = get_option( 'awsm_jobs_expired_jobs_listings' );
			if ( $hide_expired_jobs === 'expired' ) {
				$args['post_status'] = array( 'publish' );
			} else {
				$args['post_status'] = array( 'publish', 'expired' );
			}
		} else {
			$args['post_status'] = sanitize_text_field( $shortcode_atts['status'] );
		}

		$args['meta_query'][] = AWSM_Job_Openings_Pro_Pack::get_job_meta_query( 'awsm_pro_exclude_job', 'exclude' );
		$hide_filled          = get_option( 'awsm_jobs_filled_jobs_listings' );
		if ( $hide_filled === 'filled' ) {
			$args['meta_query'][] = AWSM_Job_Openings_Pro_Pack::get_job_meta_query( 'awsm_job_filled', 'filled' );
		}

		$jobs_ids          = get_posts( $args );
		$jobs_count        = count( $jobs_ids );
		$shortcode_content = (string) $jobs_count;

		/**
		 * Filters the jobs stats shortcode output content.
		 *
		 * @since 3.1.0
		 *
		 * @param string $shortcode_content Shortcode content.
		 * @param array $shortcode_atts Combined and filtered shortcode attribute list.
		 */
		return apply_filters( 'awsm_jobs_pro_jobs_stats_shortcode_output_content', $shortcode_content, $shortcode_atts );
	}

	public function job_specs_shortcode( $atts ) {
		$pairs          = array(
			'id' => false,
		);
		$shortcode_atts = shortcode_atts( $pairs, $atts, 'awsmjob_specs' );

		$job_id = intval( $shortcode_atts['id'] );
		if ( ! $job_id ) {
			$job_id = get_the_ID();
		}
		$shortcode_content = '';
		if ( $job_id && get_post_type( $job_id ) === 'awsm_job_openings' ) {
			$specs_content = AWSM_Job_Openings::get_specifications_content( $job_id, true );
			if ( ! empty( $specs_content ) ) {
				$shortcode_content = sprintf( '<div class="awsm-job-specifications-container"><div class="awsm-job-specifications-row">%1$s</div></div>', $specs_content );
			}
		}

		/**
		 * Filters the job specifications shortcode output content.
		 *
		 * @since 3.1.0
		 *
		 * @param string $shortcode_content Shortcode content.
		 * @param array $shortcode_atts Combined and filtered shortcode attribute list.
		 */
		return apply_filters( 'awsm_jobs_pro_job_specs_shortcode_output_content', $shortcode_content, $shortcode_atts );
	}
}

AWSM_Job_Openings_Pro_Main::init();
