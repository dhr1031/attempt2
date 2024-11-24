<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'AWSM_Job_Openings_Meta' ) ) :

	class AWSM_Job_Openings_Pro_Meta extends AWSM_Job_Openings_Meta {
		private static $instance = null;

		public function __construct() {
			$this->cpath = untrailingslashit( plugin_dir_path( __FILE__ ) );
			add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ), 10, 2 );
			add_filter( 'awsm_jobs_applicant_meta', array( $this, 'applicant_meta' ), 10, 2 );
			add_filter( 'awsm_jobs_applicant_meta_content', array( $this, 'applicant_meta_content' ), 10, 3 );
			add_action( 'after_awsm_job_applicant_mb_details_list', array( $this, 'application_print_controls' ) );
			add_action( 'wp_ajax_awsm_applicant_mail', array( $this, 'ajax_mail_handle' ) );
			add_action( 'wp_ajax_awsm_job_et_data', array( $this, 'ajax_mail_templates_handle' ) );
			add_action( 'wp_ajax_awsm_job_pro_notes', array( $this, 'ajax_notes_handle' ) );
			add_action( 'wp_ajax_awsm_job_pro_remove_note', array( $this, 'ajax_remove_note_handle' ) );
			add_action( 'wp_ajax_awsm_job_print_data', array( $this, 'ajax_print_applicant_data' ) );
			add_filter( 'awsm_job_status_mb_data_rows', array( $this, 'awsm_job_status_mb_data_rows' ), 100, 3 );

			add_filter( 'awsm_jobs_applicant_meta_details_list', array( $this, 'applicant_meta_details_custom_list' ), 10, 2 );
		}

		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function register_meta_boxes( $post_type, $post ) {
			// Job openings related meta boxes.
			add_meta_box( 'awsm-application-form-widget', esc_html__( 'Job Display Options', 'pro-pack-for-wp-job-openings' ), array( $this, 'awsm_job_application_form_handler' ), 'awsm_job_openings', 'side', 'default' );
			add_meta_box( 'awsm-job-cc-email-meta', esc_html__( 'CC Email Notifications', 'pro-pack-for-wp-job-openings' ), array( $this, 'email_notification_meta_handler' ), 'awsm_job_openings', 'side', 'low' );

			// Job application related meta boxes.
			add_meta_box( 'awsm-application-actions-meta', esc_html__( 'Actions', 'pro-pack-for-wp-job-openings' ), array( $this, 'application_actions_meta_handler' ), 'awsm_job_application', 'side', 'high' );
			add_meta_box( 'awsm-application-notes-meta', esc_html__( 'Notes', 'pro-pack-for-wp-job-openings' ), array( $this, 'application_notes_handler' ), 'awsm_job_application', 'side', 'low' );
			add_meta_box( 'awsm-application-activity-log-meta', esc_html__( 'Activity Log', 'pro-pack-for-wp-job-openings' ), array( $this, 'activity_log_handler' ), 'awsm_job_application', 'side', 'low' );
			add_meta_box( 'awsm-application-mail-meta', esc_html__( 'Emails', 'pro-pack-for-wp-job-openings' ), array( $this, 'awsm_job_application_email_handler' ), 'awsm_job_application', 'normal', 'low' );

			// Forms related meta boxes.
			add_meta_box( 'awsm-job-forms-meta', esc_html__( 'Form Builder', 'pro-pack-for-wp-job-openings' ), array( $this, 'awsm_job_form_handler' ), 'awsm_job_form', 'normal', 'high' );
			if ( $post_type === 'awsm_job_form' && class_exists( 'AWSM_Job_Openings_Pro_Form' ) && method_exists( 'AWSM_Job_Openings_Form', 'get_notification_options' ) ) {
				$default_form_id = AWSM_Job_Openings_Pro_Form::get_default_form_id();
				if ( $post->ID !== $default_form_id ) {
					add_meta_box( 'awsm-job-forms-notifications-meta', esc_html__( 'Notifications', 'pro-pack-for-wp-job-openings' ), array( $this, 'awsm_job_form_notifications_handler' ), 'awsm_job_form', 'normal', 'low' );
				}
			}
		}

		public function email_notification_meta_handler( $post ) {
			include $this->cpath . '/templates/meta/forward-mail.php';
		}

		public function application_actions_meta_handler( $post ) {
			include $this->cpath . '/templates/meta/application-actions.php';
		}

		public function activity_log_handler( $post ) {
			include $this->cpath . '/templates/meta/activity-log.php';
		}

		public function application_notes_handler( $post ) {
			include $this->cpath . '/templates/meta/application-notes.php';
		}

		public function awsm_job_application_email_handler( $post ) {
			include $this->cpath . '/templates/meta/application-email.php';
		}

		public function awsm_job_form_handler( $post ) {
			include_once $this->cpath . '/templates/meta/form-builder.php';
		}

		public function awsm_job_form_notifications_handler( $post ) {
			include_once $this->cpath . '/templates/meta/form-notifications.php';
		}

		public function awsm_job_application_form_handler( $post ) {
			$post_id = $post->ID;
			include $this->cpath . '/templates/generic/custom-form.php';
		}

		public function applicant_meta( $meta_details, $post_id ) {
			$form_id    = get_post_meta( $post_id, 'awsm_job_form_id', true );
			$fb_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form_id );
			// handle default fields
			if ( ! empty( $fb_options ) ) {
				foreach ( $fb_options as $fb_option ) {
					if ( $fb_option['default_field'] === true && $fb_option['field_type'] !== 'resume' ) {
						$meta_name = $fb_option['name'];
						if ( ! empty( $fb_option['label'] ) ) {
							$meta_details[ $meta_name ]['label'] = $fb_option['label'];
						}
					}
				}
			}
			// handle custom fields
			$custom_fields = get_post_meta( $post_id, 'awsm_applicant_custom_fields', true );
			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $key => $custom_field ) {
					if ( $custom_field['type'] !== 'photo' ) {
						$meta_details[ $key ] = array(
							'label' => $custom_field['label'],
							'value' => $custom_field['value'],
						);
						if ( $custom_field['type'] === 'textarea' ) {
							$meta_details[ $key ]['multi-line'] = true;
						}
						if ( $custom_field['type'] === 'repeater' || $custom_field['type'] === 'file' || $custom_field['type'] === 'url' ) {
							$meta_details[ $key ]['type'] = $custom_field['type'];
							if ( $custom_field['type'] === 'file' && is_array( $custom_field['value'] ) ) {
								$meta_details[ $key ]['multiple'] = true;
								$meta_details[ $key ]['value']    = implode( '_', $custom_field['value'] );
							}
							if ( $custom_field['type'] === 'repeater' ) {
								$meta_details[ $key ]['group'] = true;
								if ( is_array( $meta_details[ $key ]['value'] ) && count( $meta_details[ $key ]['value'] ) === 0 ) {
									$meta_details[ $key ]['value'] = '';
								}
							}
						}
					}
				}
			}
			// now order these fields
			if ( ! empty( $fb_options ) ) {
				$ordered_details = array();
				foreach ( $fb_options as $fb_option ) {
					$key = $fb_option['name'];
					if ( isset( $meta_details[ $key ] ) ) {
						$ordered_details[ $key ] = $meta_details[ $key ];
					}
				}
				$meta_details = array_merge( $ordered_details, $meta_details );
			}
			return $meta_details;
		}

		public function applicant_meta_content( $meta_content, $meta_key, $applicant_meta ) {
			if ( isset( $applicant_meta[ $meta_key ]['type'] ) && $applicant_meta[ $meta_key ]['type'] === 'repeater' && is_array( $applicant_meta[ $meta_key ]['value'] ) ) {
				$repeater_data = $applicant_meta[ $meta_key ]['value'];
				$meta_content  = '<div class="awsm-applicant-details-repeater-group">';
				foreach ( $repeater_data as $repeater_value ) {
					$meta_content .= '<ul class="awsm-applicant-details-list">';
					foreach ( $repeater_value as $item_value ) {
						$meta_content .= sprintf(
							'<li><label>%1$s</label><span>%2$s</span></li>',
							esc_html( $item_value['label'] ),
							wp_kses(
								nl2br( $item_value['value'] ),
								array(
									'br' => array(),
								)
							)
						);
					}
					$meta_content .= '</ul>';
				}
				$meta_content .= '</div>';
			}
			return $meta_content;
		}

		public function applicant_meta_details_custom_list( $list, $applicant_meta ) {
			if ( ! empty( $applicant_meta ) && is_array( $applicant_meta ) ) {
				foreach ( $applicant_meta as $meta_options ) {
					if ( isset( $meta_options['type'] ) && $meta_options['type'] === 'file' && ! empty( $meta_options['value'] ) ) {
						$field_value      = $meta_options['value'];
						$label            = isset( $meta_options['label'] ) ? $meta_options['label'] : '';
						$field_values     = isset( $meta_options['multiple'] ) && $meta_options['multiple'] ? explode( '_', $field_value ) : array( $field_value );
						$new_meta_content = '';
						foreach ( $field_values as $key => $attachment_id ) {
							$uid               = $key + 1;
							$file_path         = get_attached_file( $attachment_id );
							$full_file_name    = get_post_meta( $attachment_id, 'awsm_actual_file_name', true );
							$actual_file_name  = pathinfo( $full_file_name, PATHINFO_FILENAME );
							$file_label        = ! empty( $actual_file_name ) ? esc_html( $actual_file_name ) : esc_html__( 'Download File', 'wp-job-openings' );
							$file_size         = $file_path ? size_format( filesize( $file_path ) ) : 'Unknown size';
							$file_type         = $file_path ? wp_check_filetype( $file_path )['ext'] : 'Unknown type';
							$new_meta_content .= sprintf(
								'<p><a href="%2$s" rel="nofollow"><strong>%1$s</strong></a> (%3$s, %4$s)</p>',
								$file_label,
								$this->get_attached_file_download_url( $attachment_id, 'file', $label . '-' . $uid ),
								esc_html( strtoupper( $file_type ) ),
								esc_html( $file_size )
							);
						}

						if ( isset( $meta_options['multiple'] ) && $meta_options['multiple'] ) {
							$new_meta_content = sprintf( '<div class="awsm-pro-applicant-multi-row-wrapper">%s</div>', $new_meta_content );
						}

						$meta_content = sprintf( '<a href="%2$s" rel="nofollow"><strong>%1$s</strong></a>', esc_html__( 'Download File', 'wp-job-openings' ), $this->get_attached_file_download_url( $field_value, 'file', $label ) );
						$list         = str_replace( $meta_content, $new_meta_content, $list );
					}
				}
			}
			return $list;
		}

		public function application_print_controls( $application_id ) {
			?>
				<ul class="awsm-applicant-print-controls awsm-applicant-details-list">
					<li>
						<button type="button" data-application="<?php echo esc_attr( $application_id ); ?>" class="button awsm-applicant-print-control" data-action="download" id="awsm-download-apps"><?php esc_html_e( 'Download', 'pro-pack-for-wp-job-openings' ); ?></button>
						<button type="button" data-application="<?php echo esc_attr( $application_id ); ?>" class="button awsm-applicant-print-control" data-action="print" id="awsm-print-apps"><?php esc_html_e( 'Print', 'pro-pack-for-wp-job-openings' ); ?></button>
						<div class="awsm-applicant-print-message awsm-error-message awsm-hidden"><p><?php esc_html_e( 'Error in fetching the application details!', 'pro-pack-for-wp-job-openings' ); ?></p></div>
					</li>
				</ul>
			<?php
		}

		public function ajax_print_applicant_data() {
			$data = array(
				'error' => array(),
			);

			$generic_err_msg = esc_html__( 'Error in fetching the application details!', 'pro-pack-for-wp-job-openings' );

			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) ) {
				$data['error'][] = $generic_err_msg;
			}

			$application_id = intval( $_POST['awsm_application_id'] );

			$data['error'] = array_merge( $data['error'], $this->get_job_application_error( $application_id ) );

			if ( count( $data['error'] ) === 0 ) {
				$data = array(
					'options' => array(
						'logo'            => '',
						'font'            => 'NotoSans',
						'title_font_size' => 15,
						'font_size'       => 11,
						'title_color'     => '#1d2327',
						'subtitle_color'  => '#72777c',
						'text_color'      => '#23282d',
						'show_lines'      => true,
						'line_color'      => '#dadfe5',
						'line_width'      => 0.25,
					),
					'label'   =>
					array(
						'awsm_application_id'   => esc_html__( 'Application ID', 'pro-pack-for-wp-job-openings' ),
						'awsm_job_id'           => esc_html__( 'Job ID', 'pro-pack-for-wp-job-openings' ),
						'awsm_apply_for'        => esc_html__( 'Job Title', 'pro-pack-for-wp-job-openings' ),
						'awsm_application_date' => esc_html__( 'Applied on', 'pro-pack-for-wp-job-openings' ),
					),
				);

				$form_id    = get_post_meta( $application_id, 'awsm_job_form_id', true );
				$fb_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form_id );

				$data['label']['application_status'] = esc_html__( 'Status', 'pro-pack-for-wp-job-openings' );

				$applicant_details = array();
				// Fixed fields.
				$applicant_details['awsm_application_id'] = $application_id;
				$general_fields                           = array( 'awsm_applicant_name', 'awsm_applicant_email', 'awsm_job_id', 'awsm_apply_for' );
				foreach ( $general_fields as $general_field ) {
					$applicant_details[ $general_field ] = get_post_meta( $application_id, $general_field, true );
				}

				/* translators: %1$s: Application ID, %2$s: Applied Job */
				$title = esc_html( sprintf( __( 'Application #%1$s for %2$s', 'pro-pack-for-wp-job-openings' ), $application_id, get_post_meta( $application_id, 'awsm_apply_for', true ) ) );
				$applicant_details['awsm_applicant_pdf_title'] = $title;
				/* translators: %s: Application submission date */
				$applicant_details['awsm_application_date']    = sprintf( __( 'Submitted on %s', 'wp-job-openings' ), date_i18n( __( 'g:ia, j F Y', 'default' ), get_post_time( 'U', false, $application_id ) ) );
				$user_ip                                       = get_post_meta( $application_id, 'awsm_applicant_ip', true );
				$applicant_details['awsm_application_user_ip'] = esc_html( __( ' from IP ', 'pro-pack-for-wp-job-openings' ) . $user_ip );

				// Custom fields.
				$custom_fields = get_post_meta( $application_id, 'awsm_applicant_custom_fields', true );
				foreach ( $fb_options as $option_index => $fb_option ) {
					$field_type = $fb_option['field_type'];
					if ( $fb_option['super_field'] !== true && $field_type !== 'section' ) {
						$field_value = '';
						$name        = $fb_option['name'];
						if ( $fb_option['default_field'] !== true ) {
							if ( isset( $custom_fields[ $name ]['value'] ) ) {
								if ( isset( $custom_fields[ $name ]['type'] ) && $custom_fields[ $name ]['type'] === 'repeater' ) {
									unset( $fb_options[ $option_index ] );
									continue;
								}
								$field_value = $custom_fields[ $name ]['value'];
								if ( $field_type === 'photo' || $field_type === 'file' ) {
									if ( $field_type === 'file' && is_array( $field_value ) ) {
										$multi_attachment_url = array();
										foreach ( $field_value as $file_id ) {
											$file_url = wp_get_attachment_url( $file_id );
											if ( ! empty( $file_url ) ) {
												$full_file_name         = get_post_meta( $file_id, 'awsm_actual_file_name', true );
												$actual_file_name       = pathinfo( $full_file_name, PATHINFO_FILENAME );
												$file_label             = ! empty( $actual_file_name ) ? esc_html( $actual_file_name ) : esc_html__( 'Download File', 'wp-job-openings' );
												$multi_attachment_url[] = '{link}:' . $file_url . ':{label}:' . $file_label;

											}
										}
										$field_value = $multi_attachment_url;
									} else {
										if ( $field_type === 'photo' ) {
											$field_value = AWSM_Job_Openings_Pro_Main::get_applicant_photo_data_uri( $field_value );
										} else {
											$full_file_name   = get_post_meta( $field_value, 'awsm_actual_file_name', true );
											$actual_file_name = pathinfo( $full_file_name, PATHINFO_FILENAME );
											$file_label       = ! empty( $actual_file_name ) ? esc_html( $actual_file_name ) : '';
											$file_url         = wp_get_attachment_url( $field_value );
											$field_value      = ! empty( $field_value ) ? '{link}:' . $file_url . ':{label}:' . $file_label : '';
										}
									}
								}
							}
						} else {
							if ( $field_type === 'resume' ) {
								$attachment_id    = get_post_meta( $application_id, 'awsm_attachment_id', true );
								$full_file_name   = get_post_meta( $attachment_id, 'awsm_actual_file_name', true );
								$actual_file_name = pathinfo( $full_file_name, PATHINFO_FILENAME );
								$file_label       = ! empty( $actual_file_name ) ? esc_html( $actual_file_name ) : esc_html__( 'Download File', 'wp-job-openings' );
								$file_url         = wp_get_attachment_url( $attachment_id );
								$field_value      = ! empty( $attachment_id ) ? '{link}:' . $file_url . ':{label}:' . $file_label : '';
							} else {
								$field_value = get_post_meta( $application_id, $name, true );
							}
						}
						if ( empty( $field_value ) ) {
							$field_value = '';
						}
						$applicant_details[ $name ] = $field_value;
					}
				}

				foreach ( $fb_options as $fb_option ) {
					if ( $fb_option['field_type'] !== 'section' ) {
						$field_name = $fb_option['name'];
						if ( isset( $applicant_details[ $field_name ] ) && empty( $applicant_details[ $field_name ] ) ) {
							unset( $applicant_details[ $field_name ] );
						} else {
							$data['label'][ $field_name ] = $fb_option['label'];
						}
					}
				}
				$post_status = get_post_status( $application_id );
				if ( method_exists( 'AWSM_Job_Openings_Pro_Main', 'get_application_status' ) ) {
					$available_status = AWSM_Job_Openings_Pro_Main::get_application_status();
					if ( isset( $available_status[ $post_status ] ) ) {
						$post_status = $available_status[ $post_status ]['label'];
					}
				}
				/* translators: %s: post status */
				$applicant_details['application_status'] = esc_html( sprintf( __( 'Application status: %s', 'pro-pack-for-wp-job-openings' ), $post_status ) );
				$notes                                   = get_post_meta( $application_id, 'awsm_application_notes', true );
				$applicant_notes                         = array();
				if ( ! empty( $notes ) && is_array( $notes ) ) {
					$notes       = array_reverse( $notes );
					$total_notes = count( $notes );
					foreach ( $notes as $key => $note ) {
						$author_name       = $this->get_username( $note['author_id'] );
						$index             = $total_notes - ( $key + 1 );
						$applicant_notes[] = array(
							'index'     => $index,
							'time'      => $note['notes_date'],
							'date_i18n' => esc_html( date_i18n( __( 'M j, Y @ H:i', 'default' ), $note['notes_date'] ) ),
							'author'    => $author_name,
							'content'   => $note['notes_content'],
						);
					}
				}
				$data['value']           = $applicant_details;
				$data['label']['notes']  = esc_html__( 'Notes', 'pro-pack-for-wp-job-openings' );
				$data['value']['notes']  = $applicant_notes;
				$rating                  = intval( get_post_meta( $application_id, 'awsm_application_rating', true ) );
				$data['label']['rating'] = esc_html__( 'Rating', 'pro-pack-for-wp-job-openings' );
				$data['value']['rating'] = $rating;
				$applicant_email         = esc_attr( get_post_meta( $application_id, 'awsm_applicant_email', true ) );

				if ( ! isset( $data['value']['awsm_applicant_photo'] ) || empty( $data['value']['awsm_applicant_photo'] ) ) {
					$avatar_url      = get_avatar_url( $applicant_email, array( 'size' => 130 ) );
					$avatar_data_uri = AWSM_Job_Openings_Pro_Main::get_applicant_photo_data_uri( $avatar_url, true );
					if ( empty( $avatar_data_uri ) ) {
						$url_args = array(
							's' => '130',
							'd' => 'mm',
						);

						$avatar_data_uri = add_query_arg(
							rawurlencode_deep( $url_args ),
							'https://www.gravatar.com/avatar/' . md5( strtolower( trim( $applicant_email ) ) )
						);
					}
					$data['value']['awsm_applicant_photo'] = $avatar_data_uri;
				}
			}

			/**
			 * Filters the applicant print data array.
			 *
			 * @since 2.0.1
			 *
			 * @param array $data Print data array.
			 */
			$data = apply_filters( 'awsm_jobs_pro_applicant_print_data', $data );

			wp_send_json( $data );
		}

		public function get_job_application_error( $application_id ) {
			$error = array();
			if ( get_post_type( $application_id ) !== 'awsm_job_application' ) {
				$error[] = esc_html__( 'Invalid Job Application ID', 'pro-pack-for-wp-job-openings' );
			}
			if ( ! current_user_can( 'edit_post', $application_id ) ) {
				$error[] = esc_html__( 'You do not have sufficient permissions to edit job applications!', 'pro-pack-for-wp-job-openings' );
			}
			return $error;
		}

		public function get_view_activity() {
			$user_id = get_current_user_id();
			return array(
				'user'          => $user_id,
				'activity_date' => current_time( 'timestamp' ),
				'viewed'        => true,
			);
		}

		public function is_applicant_viewed( $activities ) {
			$current_user_id = get_current_user_id();
			$is_viewed       = false;
			foreach ( $activities as $activity ) {
				$user_id = intval( $activity['user'] );
				if ( $user_id && $user_id === $current_user_id && isset( $activity['viewed'] ) ) {
					$is_viewed = true;
					break;
				}
			}
			return $is_viewed;
		}

		public function get_username( $user_id, $user_data = null ) {
			$user_info = empty( $user_data ) ? get_userdata( $user_id ) : $user_data;
			$user      = $user_info->display_name;
			if ( empty( $user ) ) {
				$user = $user_info->user_login;
			}
			return $user;
		}

		public function get_applicant_meta_details( $application_id ) {
			$applicant_details = array();
			$meta_keys         = array( 'awsm_applicant_name', 'awsm_applicant_email', 'awsm_applicant_phone', 'awsm_applicant_letter', 'awsm_job_id', 'awsm_apply_for', 'awsm_attachment_id' );
			foreach ( $meta_keys as $meta_key ) {
				$applicant_details[ $meta_key ] = get_post_meta( $application_id, $meta_key, true );
			}
			$applicant_details['application_id'] = $application_id;
			$applicant_details['custom_fields']  = get_post_meta( $application_id, 'awsm_applicant_custom_fields', true );
			return $applicant_details;
		}

		public function get_mail_template_tags_from_meta( $post_id ) {
			$tags = array();
			if ( class_exists( 'AWSM_Job_Openings_Form' ) ) {
				$form           = AWSM_Job_Openings_Form::init();
				$applicant_meta = $this->get_applicant_meta_details( $post_id );
				if ( ! class_exists( 'AWSM_Job_Openings_Settings' ) ) {
					require_once AWSM_JOBS_PLUGIN_DIR . '/admin/class-awsm-job-openings-settings.php';
				}
				$default_from_email = AWSM_Job_Openings_Settings::awsm_from_email();
				$tags               = $form->get_mail_template_tags(
					$applicant_meta,
					array(
						'default_from_email' => $default_from_email,
					)
				);
			}
			return $tags;
		}

		public function ajax_mail_templates_handle() {
			$response          = array(
				'subject' => '',
				'content' => '',
				'error'   => array(),
			);
			$post_id           = intval( $_GET['awsm_application_id'] );
			$response['error'] = $this->get_job_application_error( $post_id );
			if ( count( $response['error'] ) === 0 && isset( $_GET['awsm_template_key'] ) ) {
				$template_key = sanitize_text_field( $_GET['awsm_template_key'] );
				$templates    = get_option( 'awsm_jobs_pro_mail_templates' );
				if ( ! empty( $templates ) ) {
					$tags       = $this->get_mail_template_tags_from_meta( $post_id );
					$tag_names  = array_keys( $tags );
					$tag_values = array_values( $tags );
					foreach ( $templates as $template ) {
						if ( $template['key'] === $template_key ) {
							$wpml_reg_name       = 'Notification Template: ' . $template['name'];
							$response['subject'] = str_replace( $tag_names, $tag_values, apply_filters( 'wpml_translate_single_string', $template['subject'], 'pro-pack-for-wp-job-openings', $wpml_reg_name . ' - Subject', AWSM_Job_Openings::get_current_language() ) );
							$response['content'] = str_replace( $tag_names, $tag_values, apply_filters( 'wpml_translate_single_string', $template['content'], 'pro-pack-for-wp-job-openings', $wpml_reg_name . ' - Content', AWSM_Job_Openings::get_current_language() ) );
							$response['content'] = wpautop( $response['content'] );
						}
					}
				}
			}
			wp_send_json( $response );
		}

		public static function update_application_mails( $post_id, $mail_data ) {
			$mail_data['mail_content'] = wp_kses(
				$mail_data['mail_content'],
				array(
					'p' => array(),
				)
			);

			$mails_meta = get_post_meta( $post_id, 'awsm_application_mails', true );
			$mails      = ! empty( $mails_meta ) && is_array( $mails_meta ) ? $mails_meta : array();
			$mails[]    = $mail_data;
			$updated    = update_post_meta( $post_id, 'awsm_application_mails', $mails );
			if ( $updated ) {
				// set user preference for HTML template.
				update_user_meta( $mail_data['send_by'], 'awsm_applicant_mail_html_template', $mail_data['html_template'] );
				// update activity log.
				$activities   = get_post_meta( $post_id, 'awsm_application_activity_log', true );
				$activities   = ! empty( $activities ) && is_array( $activities ) ? $activities : array();
				$activities[] = array(
					'user'          => $mail_data['send_by'],
					'activity_date' => $mail_data['mail_date'],
					'mail'          => true,
				);
				update_post_meta( $post_id, 'awsm_application_activity_log', $activities );
			}
			return $updated;
		}

		public function ajax_mail_handle() {
			$response        = array(
				'content' => '',
				'success' => array(),
				'error'   => array(),
			);
			$generic_err_msg = esc_html__( 'Error sending mail!', 'pro-pack-for-wp-job-openings' );

			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) ) {
				$response['error'][] = $generic_err_msg;
			}
			$post_id           = intval( $_POST['awsm_application_id'] );
			$response['error'] = array_merge( $response['error'], $this->get_job_application_error( $post_id ) );

			$cc            = sanitize_text_field( wp_unslash( $_POST['awsm_mail_meta_applicant_cc'] ) );
			$subject       = sanitize_text_field( wp_unslash( $_POST['awsm_mail_meta_applicant_subject'] ) );
			$mail_content  = wp_kses_post( wp_unslash( $_POST['awsm_mail_meta_applicant_content'] ) );
			$html_template = isset( $_POST['awsm_mail_meta_applicant_html'] ) ? sanitize_text_field( $_POST['awsm_mail_meta_applicant_html'] ) : '';
			if ( empty( $subject ) || empty( $mail_content ) ) {
				$response['error'][] = esc_html__( 'Subject and mail content required!', 'pro-pack-for-wp-job-openings' );
			}

			if ( count( $response['error'] ) === 0 ) {
				$user_id      = get_current_user_id();
				$current_time = current_time( 'timestamp' );
				$mail_data    = array(
					'send_by'       => $user_id,
					'mail_date'     => $current_time,
					'cc'            => $cc,
					'subject'       => $subject,
					'mail_content'  => $mail_content,
					'html_template' => $html_template,
				);
				$is_sent      = $this->applicant_notification( $post_id, $mail_data );

				if ( $is_sent ) {
					$updated = self::update_application_mails( $post_id, $mail_data );
					if ( $updated ) {
						// send the response.
						$response['success'][] = esc_html__( 'Your message has been successfully sent to the applicant.', 'pro-pack-for-wp-job-openings' );
						$response['content']   = array(
							'author'    => $this->get_username( $mail_data['send_by'] ),
							'date_i18n' => esc_html( date_i18n( __( 'M j, Y @ H:i', 'default' ), $mail_data['mail_date'] ) ),
							'subject'   => $mail_data['subject'],
							'content'   => wpautop( $mail_data['mail_content'] ),
						);
					} else {
						$response['error'][] = $generic_err_msg;
					}
				} else {
					$response['error'][] = $generic_err_msg;
				}
			}
			wp_send_json( $response );
		}

		public function applicant_notification( $post_id, $mail_data, $attachments = array(), $br = true ) {
			if ( ! class_exists( 'AWSM_Job_Openings_Settings' ) ) {
				require_once AWSM_JOBS_PLUGIN_DIR . '/admin/class-awsm-job-openings-settings.php';
			}
			$default_from_email = AWSM_Job_Openings_Settings::awsm_from_email();
			$from_email         = get_option( 'awsm_jobs_from_email_notification', $default_from_email );
			$company_name       = get_option( 'awsm_job_company_name', '' );
			$from               = ! empty( $company_name ) ? $company_name : get_option( 'blogname' );
			$user_info          = get_userdata( $mail_data['send_by'] );
			$to                 = get_post_meta( $post_id, 'awsm_applicant_email', true );
			$cc                 = $mail_data['cc'];
			$subject            = $mail_data['subject'];
			$message            = $mail_data['mail_content'];
			$admin_email        = get_option( 'admin_email' );
			$hr_mail            = get_option( 'awsm_hr_email_address' );
			$job_id             = get_post_meta( $post_id, 'awsm_job_id', true );
			$author_id          = get_post_field( 'post_author', $job_id );
			$author_email       = get_the_author_meta( 'user_email', intval( $author_id ) );

			if ( $br ) {
				$message = nl2br( $message );
			}
			$html_template = $mail_data['html_template'];

			$tags             = $this->get_mail_template_tags_from_meta( $post_id );
			$tag_names        = array_keys( $tags );
			$tag_values       = array_values( $tags );
			$email_tag_names  = array( '{admin-email}', '{hr-email}', '{author-email}', '{default-from-email}' );
			$email_tag_values = array( $admin_email, $hr_mail, $author_email, $default_from_email );
			$from_email       = str_replace( $email_tag_names, $email_tag_values, $from_email );

			if ( $html_template === 'enable' ) {
				// Header mail template.
				ob_start();
				include AWSM_Job_Openings::get_template_path( 'header.php', 'mail' );
				$header_template  = ob_get_clean();
				$header_template .= '<div style="padding: 0 15px; font-size: 16px; max-width: 512px; margin: 0 auto;">';

				// Footer mail template.
				ob_start();
				include AWSM_Job_Openings::get_template_path( 'footer.php', 'mail' );
				$footer_template  = ob_get_clean();
				$footer_template .= '</div>';

				$template = $header_template . $message . $footer_template;
				/**
				 * Filters the custom applicant notification mail template.
				 *
				 * @since 2.0.0
				 *
				 * @param string $template Mail template.
				 * @param array $template_data Mail template data.
				 * @param int $post_id The Application ID.
				 */
				$message = apply_filters(
					'awsm_jobs_pro_applicant_notification_mail_template',
					$template,
					array(
						'header' => $header_template,
						'main'   => $message,
						'footer' => $footer_template,
					),
					$post_id
				);
				$message = str_replace( $tag_names, $tag_values, $message );
			}

			// Additional headers.
			$headers   = array();
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$headers[] = apply_filters( 'awsm_jobs_pro_applicant_notification_mail_from', sprintf( 'From: %1$s <%2$s>', $from, $from_email ), $from );
			$headers[] = apply_filters( 'awsm_jobs_pro_applicant_notification_mail_reply_to', sprintf( 'Reply-To: %1$s <%2$s>', $this->get_username( $mail_data['send_by'], $user_info ), $user_info->user_email ), $mail_data['send_by'] );
			if ( ! empty( $cc ) ) {
				$headers[] = 'Cc: ' . $cc;
			}

			add_filter( 'wp_mail_content_type', 'awsm_jobs_pro_mail_content_type' );
			$is_sent = wp_mail( $to, $subject, $message, $headers, $attachments );
			remove_filter( 'wp_mail_content_type', 'awsm_jobs_pro_mail_content_type' );
			return $is_sent;
		}

		public function ajax_notes_handle() {
			$response        = array(
				'update'     => false,
				'notes_data' => '',
				'error'      => array(),
			);
			$generic_err_msg = esc_html__( 'Error in submitting notes!', 'pro-pack-for-wp-job-openings' );
			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) ) {
				$response['error'][] = $generic_err_msg;
			}
			$post_id           = intval( $_POST['awsm_application_id'] );
			$response['error'] = array_merge( $response['error'], $this->get_job_application_error( $post_id ) );
			$notes_content     = isset( $_POST['awsm_application_notes'] ) ? sanitize_text_field( $_POST['awsm_application_notes'] ) : '';
			if ( count( $response['error'] ) === 0 && ! empty( $notes_content ) ) {
				$user_id    = get_current_user_id();
				$notes_time = current_time( 'timestamp' );
				$notes      = get_post_meta( $post_id, 'awsm_application_notes', true );
				$notes      = ( ! empty( $notes ) && is_array( $notes ) ) ? $notes : array();
				$notes_data = array(
					'author_id'     => $user_id,
					'notes_date'    => $notes_time,
					'notes_content' => $notes_content,
				);
				$notes[]    = $notes_data;

				$updated = update_post_meta( $post_id, 'awsm_application_notes', $notes );
				if ( $updated ) {
					$response['update']     = true;
					$keys                   = array_keys( $notes );
					$index                  = max( $keys );
					$author_name            = $this->get_username( $user_id );
					$data                   = array(
						'index'     => $index,
						'username'  => $author_name,
						'time'      => $notes_time,
						'date_i18n' => esc_html( date_i18n( __( 'M j, Y @ H:i', 'default' ), $notes_time ) ),
					);
					$response['notes_data'] = $data;
				} else {
					$response['error'][] = $generic_err_msg;
				}
			}
			wp_send_json( $response );
		}

		public function ajax_remove_note_handle() {
			$response        = array(
				'delete' => false,
				'error'  => array(),
			);
			$generic_err_msg = esc_html__( 'Error in deleting notes!', 'pro-pack-for-wp-job-openings' );
			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) ) {
				$response['error'][] = $generic_err_msg;
			}
			$post_id           = intval( $_POST['awsm_application_id'] );
			$response['error'] = array_merge( $response['error'], $this->get_job_application_error( $post_id ) );
			if ( isset( $_POST['awsm_note_key'] ) && isset( $_POST['awsm_note_time'] ) ) {
				$key = $_POST['awsm_note_key'];
				if ( ! is_numeric( $key ) ) {
					$response['error'][] = esc_html__( 'Invalid key supplied!', 'pro-pack-for-wp-job-openings' );
				} else {
					$key = intval( $key );
				}
				$supplied_time = intval( $_POST['awsm_note_time'] );
				if ( ! $supplied_time ) {
					$response['error'][] = esc_html__( 'Invalid timestamp supplied!', 'pro-pack-for-wp-job-openings' );
				}
				$notes = get_post_meta( $post_id, 'awsm_application_notes', true );
				if ( empty( $notes ) || ! is_array( $notes ) ) {
					$response['error'][] = esc_html__( 'No notes to delete!', 'pro-pack-for-wp-job-openings' );
				}
				if ( count( $response['error'] ) === 0 ) {
					$time = intval( $notes[ $key ]['notes_date'] );
					if ( $time === $supplied_time ) {
						array_splice( $notes, $key, 1 );
						$updated = update_post_meta( $post_id, 'awsm_application_notes', $notes );
						if ( $updated ) {
							$response['key']    = $key;
							$response['delete'] = true;
						} else {
							$response['error'][] = $generic_err_msg;
						}
					} else {
						$response['error'][] = $generic_err_msg;
					}
				}
			}
			wp_send_json( $response );
		}

		public function applicant_mail_template( $data = array() ) {
			$template_data = wp_parse_args(
				$data,
				array(
					'author'    => '{{data.author}}',
					'date_i18n' => '{{data.date_i18n}}',
					'subject'   => '{{data.subject}}',
					'content'   => '{{{data.content}}}',
				)
			);
			?>
			<div class="awsm-jobs-applicant-mail">
				<div class="awsm-jobs-applicant-mail-header">
					<h3><?php echo esc_html( $template_data['subject'] ); ?></h3>
					<p class="awsm-jobs-applicant-mail-meta">
						<span><?php echo esc_html( $template_data['author'] ); ?></span>
						<span><?php echo esc_html( $template_data['date_i18n'] ); ?></span>
					</p>
				</div>
				<div class="awsm-jobs-applicant-mail-content">
					<?php
						echo wp_kses(
							$template_data['content'],
							array(
								'p'  => array(),
								'br' => array(),
							)
						);
					?>
				</div>
			</div>
			<?php
		}

		public function notes_template( $data = array() ) {
			$template_data = wp_parse_args(
				$data,
				array(
					'index'     => '{{data.index}}',
					'time'      => '{{data.time}}',
					'date_i18n' => '{{data.date_i18n}}',
					'author'    => '{{data.author}}',
					'content'   => '{{data.content}}',
				)
			);
			?>
			<li class="awsm-jobs-note" data-index="<?php echo esc_attr( $template_data['index'] ); ?>" data-time="<?php echo esc_attr( $template_data['time'] ); ?>">
				<div class="awsm-jobs-note-content-wrapper">
					<span class="awsm-jobs-note-content">
						<?php echo esc_html( $template_data['content'] ); ?>
					</span>

					<span class="awsm-jobs-note-remove">
						<button type="button" class="awsm-jobs-note-remove-btn ntdelbutton">
							<span class="remove-tag-icon" aria-hidden="true"></span>
							<span class="screen-reader-text"><?php esc_html_e( 'Remove Note', 'pro-pack-for-wp-job-openings' ); ?></span>
						</button>
					</span>
				</div>
				<div class="awsm-jobs-note-details">
					<p class="description"><span><?php echo esc_html( $template_data['author'] ); ?></span>, <span><?php echo esc_html( $template_data['date_i18n'] ); ?></span></p>
				</div>
			</li>
			<?php
		}

		public function awsm_job_status_mb_data_rows( $data_rows, $job_id, $post_id ) {
			global $post_type;
			if ( $post_type === 'awsm_job_application' ) {
				$svg_icon_url = esc_url( AWSM_JOBS_PRO_PLUGIN_URL . '/assets/img/exchange.svg' );
				$svg_icon     = '<img src="' . $svg_icon_url . '" alt="Icon" style="width: 16px; height: 16px; vertical-align: middle;" />';
				if ( isset( $data_rows['job_title'] ) ) {
					$data_rows['job_title'][1] .= ' <a href="#" class="awsm-job-move-job-apllication">' . $svg_icon . '</a>';
				}

				$application_id = $post_id;
				$custom_posts   = array(
					'posts_per_page'   => -1,
					'post_type'        => 'awsm_job_openings',
					'post_status'      => array( 'publish', 'expired' ),
					'suppress_filters' => false,
				);
				$job_posts      = get_posts( $custom_posts );
				$current_job_id = intval( get_post_meta( $application_id, 'awsm_job_id', true ) );
				$options        = "<option value=''>" . esc_html__( 'Select job', 'pro-pack-for-wp-job-openings' ) . '</option>';

				foreach ( $job_posts as $jobs ) {
					$job_id     = intval( $jobs->ID );
					$post_title = esc_html( $jobs->post_title );
					$selected   = ( $current_job_id === $job_id ) ? ' selected' : '';
					$options   .= sprintf( '<option value="%1$d"%3$s>%2$s</option>', esc_attr( $job_id ), esc_html( $post_title ), esc_attr( $selected ) );
				}

				if ( isset( $data_rows['date_of_expiry'] ) ) {
					$move_applicants_html = sprintf(
						'<div id="awsm-job-move-application-wrapper" class="awsm-job-move-application-wrapper">
						<label for="awsm-job-move-application">%s</label>
							<div class="awsm-job-move-application-container">
								<select id="awsm-job-move-application" class="awsm-job-move-application-select" name="awsm_job_move_application">
									%s
								</select>
								<button class="button button-primary button-large awsm-job-move-application-btn" id="awsm-job-move-application-btn">%s</button>
							</div>
						</div>',
						esc_html__( 'Move application to another job', 'pro-pack-for-wp-job-openings' ),
						$options,
						esc_html__( 'Move', 'pro-pack-for-wp-job-openings' )
					);

					$move_applicants_section = array(
						'move_applicants' => array( $move_applicants_html ),
					);

					$position  = array_search( 'date_of_expiry', array_keys( $data_rows ) );
					$data_rows = array_slice( $data_rows, 0, $position + 1, true ) + $move_applicants_section + array_slice( $data_rows, $position + 1, null, true );
				}
			}

			return $data_rows;
		}
	}

	AWSM_Job_Openings_Pro_Meta::init();

endif; // end of class check
