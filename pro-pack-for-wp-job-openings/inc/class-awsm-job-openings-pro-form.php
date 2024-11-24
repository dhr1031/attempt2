<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'AWSM_Job_Openings_Form' ) ) :

	class AWSM_Job_Openings_Pro_Form extends AWSM_Job_Openings_Form {
		private static $instance = null;

		protected static $temp_dir_name = 'temp';

		public static $default_form_id = null;

		public static $form_strings = array();

		public static $validation_notices = array();

		public function __construct() {
			$this->cpath = untrailingslashit( plugin_dir_path( __FILE__ ) );
			add_action( 'wp_loaded', array( $this, 'remove_hooks' ) );
			add_action( 'wp', array( $this, 'application_form_handle' ) );
			$this->handle_default_form();

			add_action( 'awsm_application_form_init', array( $this, 'position_filled_handler' ), 100 );
			add_filter( 'awsm_application_form_fields_order', array( $this, 'pro_form_fields_order' ), 100, 2 );
			add_filter( 'awsm_application_form_fields', array( $this, 'pro_form_fields' ), 100, 2 );
			add_filter( 'awsm_application_form_title', array( $this, 'application_form_title' ), 100, 2 );
			add_action( 'awsm_application_form_description', array( $this, 'application_form_description' ), 100 );
			add_filter( 'awsm_application_form_submit_btn_text', array( $this, 'application_btn_text' ), 100, 2 );
			add_action( 'before_awsm_job_details', array( $this, 'fallback_handle' ) );
			add_action( 'wp_ajax_awsm_applicant_form_submission', array( $this, 'ajax_handle' ) );
			add_action( 'wp_ajax_nopriv_awsm_applicant_form_submission', array( $this, 'ajax_handle' ) );
			add_action( 'wp_ajax_awsm_applicant_attachments_handler', array( $this, 'applicant_attachments_handler' ) );
			add_action( 'wp_ajax_nopriv_awsm_applicant_attachments_handler', array( $this, 'applicant_attachments_handler' ) );

			add_action( 'wp_ajax_awsm_applicant_form_file_upload', array( $this, 'ajax_file_upload' ) );
			add_action( 'wp_ajax_nopriv_awsm_applicant_form_file_upload', array( $this, 'ajax_file_upload' ) );
			add_action( 'wp_ajax_awsm_applicant_form_remove_file', array( $this, 'ajax_remove_uploaded_file' ) );
			add_action( 'wp_ajax_nopriv_awsm_applicant_form_remove_file', array( $this, 'ajax_remove_uploaded_file' ) );
			add_action( 'wp_ajax_form_notifications_switch', array( $this, 'form_notifications_switch' ) );

			add_filter( 'intermediate_image_sizes_advanced', array( $this, 'intermediate_image_sizes_handler' ), 100, 2 );
			add_filter( 'get_attached_file', array( $this, 'get_attached_file_handler' ), 100, 2 );
			add_filter( 'wp_check_filetype_and_ext', array( $this, 'check_filetype_and_ext' ), 10, 5 );
			add_filter( 'awsm_jobs_admin_notification_mail_attachments', array( $this, 'admin_notification_mail_attachments' ), 10, 2 );
			add_filter( 'awsm_jobs_admin_notification_mail_headers', array( $this, 'job_forward_notification_email' ), 10, 2 );
			add_filter( 'awsm_jobs_mail_template_tags', array( $this, 'pro_mail_template_tags' ), 10, 2 );
			add_filter( 'awsm_application_dynamic_form_field_content', array( $this, 'form_field_content' ), 10, 3 );
			add_filter( 'awsm_application_dynamic_form_section_field_content', array( $this, 'section_field_content' ), 10, 2 );
			add_filter( 'awsm_application_dynamic_form_file_field_content', array( $this, 'file_field_content' ), 10, 2 );
			add_filter( 'awsm_application_dynamic_form_tel_field_content', array( $this, 'tel_field_content' ), 10, 2 );
			add_filter( 'awsm_application_form_is_recaptcha_visible', array( $this, 'is_recaptcha_visible' ) );

			add_shortcode( 'awsm_application_form', array( $this, 'generic_form_shortcode' ) );

			$this->customize_form_strings();
			$this->customize_validation_notices();
		}

		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function remove_hooks() {
			remove_action( 'before_awsm_job_details', array( AWSM_Job_Openings_Form::init(), 'insert_application' ) );
			remove_action( 'wp_ajax_awsm_applicant_form_submission', array( AWSM_Job_Openings_Form::init(), 'ajax_handle' ) );
			remove_action( 'wp_ajax_nopriv_awsm_applicant_form_submission', array( AWSM_Job_Openings_Form::init(), 'ajax_handle' ) );
		}

		public function handle_default_form() {
			self::$default_form_id = self::get_default_form_id();
			if ( ! empty( self::$default_form_id ) ) {
				add_filter( 'wp_list_table_show_post_checkbox', array( $this, 'forms_list_table_show_checkbox' ), 10, 2 );
				add_filter( 'post_row_actions', array( $this, 'form_row_actions' ), 10, 2 );
				add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_form_data' ), 10, 2 );
				add_filter( 'pre_delete_post', array( $this, 'pre_delete_form' ), 10, 2 );
			}
		}

		public function forms_list_table_show_checkbox( $show, $post ) {
			if ( get_post_type( $post ) === 'awsm_job_form' ) {
				if ( $post->ID === self::$default_form_id ) {
					$show = false;
				}
			}
			return $show;
		}

		public function form_row_actions( $actions, $post ) {
			if ( get_post_type( $post ) === 'awsm_job_form' ) {
				if ( $post->ID === self::$default_form_id ) {
					unset( $actions['inline hide-if-no-js'] );
					unset( $actions['trash'] );
				}
			}
			return $actions;
		}

		public function wp_insert_form_data( $data, $postarr ) {
			if ( isset( $data['post_type'] ) && $data['post_type'] === 'awsm_job_form' && isset( $postarr['ID'] ) ) {
				if ( $postarr['ID'] === self::$default_form_id ) {
					$data['post_status'] = 'publish';
				}
			}
			return $data;
		}

		public function pre_delete_form( $delete, $post ) {
			if ( get_post_type( $post ) === 'awsm_job_form' ) {
				if ( $post->ID === self::$default_form_id ) {
					$delete = false;
				}
			}
			return $delete;
		}

		public static function get_default_form_id() {
			$default_form_id = 0;
			$forms           = get_option( 'awsm_jobs_forms' );
			if ( is_array( $forms ) && isset( $forms['default'] ) ) {
				$default_form_id = intval( $forms['default'] );
			}
			return $default_form_id;
		}

		public static function get_forms( $args = null ) {
			$defaults                 = array(
				'posts_per_page'   => -1,
				'post_status'      => 'publish',
				'suppress_filters' => false,
				'order'            => 'ASC',
				'orderby'          => 'ID',
			);
			$parsed_args              = wp_parse_args( $args, $defaults );
			$parsed_args['post_type'] = 'awsm_job_form';
			$forms                    = get_posts( $parsed_args );
			return $forms;
		}

		public static function get_form_builder_options( $form_id, $other_options = false ) {
			$options = array();
			if ( ! empty( $form_id ) && is_numeric( $form_id ) ) {
				$meta_key = ! $other_options ? 'awsm_jobs_form_builder' : 'awsm_jobs_form_builder_other_options';
				$options  = get_post_meta( $form_id, $meta_key, true );
			}
			if ( empty( $options ) ) {
				if ( ! $other_options ) {
					$options = get_option( 'awsm_jobs_form_builder', self::default_form_fields_options() );
				} else {
					$options = get_option(
						'awsm_jobs_form_builder_other_options',
						array(
							'form_title' => esc_html__( 'Apply for this position', 'wp-job-openings' ),
							'btn_text'   => esc_html__( 'Submit', 'wp-job-openings' ),
						)
					);
				}
			}
			/**
			 * Filters the form builder options.
			 *
			 * @since 3.2.0
			 *
			 * @param array $options Form builder options.
			 * @param int $form_id The Form ID.
			 * @param array $other_options Form builder other options.
			 */
			$options = apply_filters( 'awsm_jobs_fb_options', $options, $form_id, $other_options );
			return $options;
		}

		public static function get_job_form_builder_options( $job_id = false, $other_options = false ) {
			if ( ! $job_id ) {
				global $post;
				$job_id = $post->ID;
			}
			$form_data = self::get_custom_form_data( $job_id );
			$form_id   = isset( $form_data['id'] ) ? $form_data['id'] : false;
			$options   = array(
				'id'     => $form_id,
				'fields' => self::get_form_builder_options( $form_id, $other_options ),
			);
			return $options;
		}

		public static function get_custom_form_data( $post_id ) {
			$data = get_option( 'awsm_jobs_custom_application_form' );
			if ( $post_id !== 'option' ) {
				$meta_data = get_post_meta( $post_id, 'awsm_pro_application_form', true );
				if ( is_array( $meta_data ) && isset( $meta_data['id'] ) ) {
					$data = $meta_data;
				}
			}

			if ( is_array( $data ) && isset( $data['id'] ) ) {
				return $data;
			} else {
				return false;
			}
		}

		public function application_form_handle() {
			global $post;
			if ( is_singular( 'awsm_job_openings' ) ) {
				$is_filled = AWSM_Job_Openings_Pro_Pack::is_position_filled( $post->ID );
				$form_data = self::get_custom_form_data( $post->ID );
				if ( $is_filled || ( ! empty( $form_data ) && ( $form_data['id'] === 'disable' || $form_data['id'] === 'custom_form' || $form_data['id'] === 'custom_button' ) ) ) {
					remove_action( 'awsm_application_form_init', array( AWSM_Job_Openings_Form::init(), 'application_form' ) );

					if ( ( ! $is_filled ) && ( $form_data['id'] === 'custom_form' || $form_data['id'] === 'custom_button' ) ) {
						$this->initialize_form();
					}
				}
			}
		}

		public function initialize_form() {
			add_action( 'awsm_application_form_init', array( $this, 'custom_form_handler' ) );
		}

		public function get_custom_form_content( $job_id ) {
			$custom_content = '';
			$form_data      = self::get_custom_form_data( $job_id );
			if ( $form_data['id'] === 'custom_button' ) {
				$button = $form_data['button'];
				if ( ! empty( $button['url'] ) && ! empty( $button['text'] ) ) {
					$target         = isset( $button['target'] ) ? $button['target'] : '';
					$custom_content = sprintf( '<button type="button" class="awsm-jobs-pro-application-form-btn" data-url="%2$s" data-target="%3$s">%1$s</button>', wp_kses( $button['text'], 'post' ), esc_url( $button['url'] ), esc_attr( $target ) );
				}
			} elseif ( $form_data['id'] === 'custom_form' ) {
				$custom_content = do_shortcode( $form_data['shortcode'] );
			}
			if ( ( ! empty( $custom_content ) ) || $form_data['id'] === 'disable' ) {
				$custom_content = sprintf( '<div class="awsm-jobs-pro-custom-form-content%2$s">%1$s</div>', $custom_content, $form_data['id'] === 'disable' ? ' awsm-job-hide' : '' );
				/**
				 * Filters the custom form content in the job detail page.
				 *
				 * @since 2.0.0
				 *
				 * @param string $custom_content Custom form content.
				 * @param array $form_data The form data.
				 */
				$custom_content = apply_filters( 'awsm_jobs_pro_custom_form_content', $custom_content, $form_data );
			}
			return $custom_content;
		}

		public function custom_form_handler() {
			global $post;
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->get_custom_form_content( $post->ID );
		}

		public static function is_multiple_submission( $job_id, $fields = array() ) {
			$multiple = true;
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( empty( $fields ) ) {
				$fields['email'] = isset( $_POST['awsm_applicant_email'] ) ? sanitize_email( $_POST['awsm_applicant_email'] ) : '';
				$fields['phone'] = isset( $_POST['awsm_applicant_phone'] ) ? sanitize_text_field( $_POST['awsm_applicant_phone'] ) : '';
			}
			// phpcs:enable

			if ( ! empty( $fields['email'] ) ) {
				$meta_query = array(
					array(
						'key'   => 'awsm_applicant_email',
						'value' => $fields['email'],
					),
				);
				if ( ! empty( $fields['phone'] ) ) {
					$meta_query['relation'] = 'OR';
					$meta_query[]           = array(
						array(
							'key'   => 'awsm_applicant_phone',
							'value' => $fields['phone'],
						),
					);
				}
				$args = array(
					'post_parent' => $job_id,
					'post_type'   => 'awsm_job_application',
					'meta_query'  => $meta_query,
					'numberposts' => -1,
					'fields'      => 'ids',
				);
				/**
				 * Filters the arguments for the applications query used for restricting multiple submission.
				 *
				 * @since 3.1.0
				 *
				 * @param array $args arguments.
				 * @param int $job_id The Job ID.
				 * @param array $fields Fields used for restricting multiple submission.
				 */
				$args = apply_filters( 'awsm_jobs_restrict_application_form_query_args', $args, $job_id, $fields );

				$applications = get_children( $args );
				$multiple     = count( $applications ) > 0;
			}

			return $multiple;
		}

		public function position_filled_handler() {
			if ( AWSM_Job_Openings_Pro_Pack::is_position_filled() ) {
				$job_detail_strings = AWSM_Job_Openings_Pro_Main::$job_detail_strings;
				printf( '<div class="awsm-job-position-filled awsm-job-form-inner">%s</div>', esc_html( $job_detail_strings['position_filled'] ) );
			}
		}

		public function pro_form_field_init( $form_attrs = array() ) {
			ob_start();
			$this->form_field_init( $form_attrs );
			$field_content = ob_get_clean();
			if ( ! empty( $field_content ) ) {
				$default_req_attr   = sprintf( 'data-msg-required="%s"', esc_attr__( 'This field is required.', 'wp-job-openings' ) );
				$new_req_attr       = sprintf( 'data-msg-required="%s"', esc_attr( self::$validation_notices['required'] ) );
				$default_email_attr = sprintf( 'data-msg-email="%s"', esc_attr__( 'Please enter a valid email address.', 'wp-job-openings' ) );
				$new_email_attr     = sprintf( 'data-msg-email="%s"', esc_attr( self::$validation_notices['email'] ) );
				$field_content      = str_replace( array( $default_req_attr, $default_email_attr ), array( $new_req_attr, $new_email_attr ), $field_content );
				echo $field_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		public function generate_repeater_fields( $form_fields ) {
			$fb_fields = array();
			foreach ( $form_fields as $form_field ) {
				$fb_fields[] = $form_field;
				if ( isset( $form_field['repeater'] ) && $form_field['repeater']['fields'] ) {
					$field_name      = $form_field['name'];
					$repeater_fields = $form_field['repeater']['fields'];
					$fields_count    = count( $repeater_fields );
					$last_index      = $fields_count - 1;
					foreach ( $repeater_fields as $index => $repeater_field ) {
						$repeater_fields[ $index ]['name'] = $field_name . '[0][' . $repeater_field['name'] . ']';
						if ( ! isset( $repeater_fields[ $index ]['active'] ) ) {
							$repeater_fields[ $index ]['active'] = 'active';
						}
						$repeater_fields[ $index ]['super_field']   = false;
						$repeater_fields[ $index ]['default_field'] = false;
					}
					$repeater_fields[ $last_index ]['repeater'] = array(
						'break' => true,
					);

					$fb_fields = array_merge( $fb_fields, $repeater_fields );
				}
			}
			$form_fields = $fb_fields;
			return $form_fields;
		}

		public function pro_form_fields_order( $form_fields_order, $form_attrs = array() ) {
			$job_id = false;
			if ( isset( $form_attrs['job_id'] ) ) {
				$job_id = $form_attrs['job_id'];
			}
			$fb_options  = self::get_job_form_builder_options( $job_id );
			$form_fields = $fb_options['fields'];
			if ( ! empty( $form_fields ) ) {
				$form_fields       = $this->generate_repeater_fields( $form_fields );
				$form_fields_order = array();
				foreach ( $form_fields as $form_field ) {
					$form_fields_order[] = $form_field['name'];
				}
			}
			return $form_fields_order;
		}

		public function get_field_options( $options_list ) {
			$field_options = array();
			if ( ! empty( $options_list ) ) {
				$options = explode( ',', $options_list );
				foreach ( $options as $option ) {
					$option          = trim( $option );
					$field_options[] = $option;
				}
			}
			return $field_options;
		}

		public static function get_valid_file_types( $list ) {
			$list        = str_replace( array( ' ', '.' ), '', strtolower( $list ) );
			$file_types  = explode( ',', $list );
			$valid_types = array_filter( $file_types );
			return $valid_types;
		}

		public static function default_form_fields_options() {
			$default_options = array(
				array(
					'super_field' => true,
					'name'        => 'awsm_applicant_name',
					'label'       => esc_html__( 'Full Name', 'wp-job-openings' ),
					'field_type'  => 'text',
				),
				array(
					'super_field' => true,
					'name'        => 'awsm_applicant_email',
					'label'       => esc_html__( 'Email', 'wp-job-openings' ),
					'field_type'  => 'email',
				),
				array(
					'name'       => 'awsm_applicant_phone',
					'label'      => esc_html__( 'Phone', 'wp-job-openings' ),
					'field_type' => 'tel',
				),
				array(
					'name'       => 'awsm_applicant_letter',
					'label'      => esc_html__( 'Cover Letter', 'wp-job-openings' ),
					'field_type' => 'textarea',
				),
				array(
					'name'       => 'awsm_file',
					'label'      => esc_html__( 'Upload CV/Resume', 'wp-job-openings' ),
					'field_type' => 'resume',
				),
			);
			foreach ( $default_options as $key => $default_option ) {
				$default_options[ $key ]['super_field']   = isset( $default_option['super_field'] ) ? $default_option['super_field'] : false;
				$default_options[ $key ]['required']      = 'required';
				$default_options[ $key ]['active']        = 'active';
				$default_options[ $key ]['default_field'] = true;
			}
			return $default_options;
		}

		public function pro_form_fields( $fields, $form_attrs = array() ) {
			$job_id = false;
			if ( isset( $form_attrs['job_id'] ) ) {
				$job_id = $form_attrs['job_id'];
			}
			$fb_options  = self::get_job_form_builder_options( $job_id );
			$form_fields = $fb_options['fields'];
			if ( ! empty( $form_fields ) ) {
				$form_fields = $this->generate_repeater_fields( $form_fields );
				foreach ( $form_fields as $form_field ) {
					$name       = $form_field['name'];
					$field_type = $form_field['field_type'];

					$fields[ $name ]['key'] = $name;
					if ( $field_type !== 'section' ) {
						$fields[ $name ]['label'] = apply_filters( 'wpml_translate_single_string', $form_field['label'], 'pro-pack-for-wp-job-openings', 'Application Form: Label for ' . $name );
					} else {
						$fields[ $name ]['label'] = apply_filters( 'wpml_translate_single_string', $form_field['label'], 'pro-pack-for-wp-job-openings', 'Application Form: Section Title for ' . $name );
					}
					// Handle IDs for application form shortcode.
					if ( ! empty( $form_attrs ) ) {
						if ( isset( $form_attrs['single_form'] ) && ! $form_attrs['single_form'] ) {
							$field_id              = isset( $fields[ $name ]['id'] ) ? $fields[ $name ]['id'] : $name;
							$fields[ $name ]['id'] = $field_id . '-' . $form_attrs['job_id'];
						}
					}

					if ( ! $form_field['super_field'] ) {
						$fields[ $name ]['show_field'] = $form_field['active'] === 'active' ? true : false;
						$fields[ $name ]['required']   = isset( $form_field['required'] ) && $form_field['required'] === 'required' ? true : false;
						if ( $form_field['default_field'] === false || $field_type === 'resume' ) {
							if ( $field_type === 'section' ) {
								$fields[ $name ]['field_type']['tag'] = 'section';
								$fields[ $name ]['description']       = isset( $form_field['misc_options'] ) && isset( $form_field['misc_options']['section_description'] ) ? $form_field['misc_options']['section_description'] : '';
								if ( ! empty( $fields[ $name ]['description'] ) ) {
									$fields[ $name ]['description'] = apply_filters( 'wpml_translate_single_string', $fields[ $name ]['description'], 'pro-pack-for-wp-job-openings', 'Application Form: Section Description for ' . $name );
								}
							} elseif ( $field_type === 'textarea' ) {
								$fields[ $name ]['field_type']['tag'] = 'textarea';
							} elseif ( $field_type === 'select' ) {
								$fields[ $name ]['field_type']['tag'] = 'select';
							} else {
								$fields[ $name ]['field_type']['tag'] = 'input';
								if ( $field_type === 'resume' || $field_type === 'file' || $field_type === 'photo' ) {
									$fields[ $name ]['field_type']['utype'] = $field_type;
									$field_type                             = 'file';
									$file_types                             = isset( $form_field['misc_options'] ) && isset( $form_field['misc_options']['file_types'] ) ? $form_field['misc_options']['file_types'] : '';
									if ( empty( $file_types ) ) {
										if ( $fields[ $name ]['field_type']['utype'] === 'resume' ) {
											$file_types = self::get_resume_default_allowed_types();
										} elseif ( $fields[ $name ]['field_type']['utype'] === 'photo' ) {
											$file_types = 'jpg,png';
										}
									}

									if ( ! empty( $file_types ) ) {
										$allowed_file_types   = self::get_valid_file_types( $file_types );
										$allowed_file_content = '';
										if ( is_array( $allowed_file_types ) && ! empty( $allowed_file_types ) ) {
											$allowed_file_types = '.' . join( ', .', $allowed_file_types );
											/* translators: %1$s: allowed file types */
											$allowed_file_content                    = '<small>' . sprintf( self::$form_strings['allowed_file_types'], $allowed_file_types ) . '</small>';
											$fields[ $name ]['field_type']['accept'] = $allowed_file_types;
											$fields[ $name ]['content']              = $allowed_file_content;
										}
									}

									$drag_and_drop = isset( $form_field['misc_options'] ) && isset( $form_field['misc_options']['drag_and_drop'] ) ? $form_field['misc_options']['drag_and_drop'] : '';
									if ( $drag_and_drop === 'enable' ) {
										$fields[ $name ]['field_type']['drag_and_drop'] = true;
									}

									$wp_max_file_size = wp_max_upload_size();
									$max_file_size    = isset( $form_field['misc_options'] ) && isset( $form_field['misc_options']['max_file_size'] ) ? intval( $form_field['misc_options']['max_file_size'] ) : $wp_max_file_size;
									$max_files        = isset( $form_field['misc_options'] ) && isset( $form_field['misc_options']['multi_upload'] ) ? absint( $form_field['misc_options']['multi_upload'] ) : 1;

									$fields[ $name ]['field_type']['max_file_size'] = $max_file_size;
									$fields[ $name ]['field_type']['max_files']     = $field_type === 'file' && $max_files ? $max_files : 1;

									if ( ! isset( $fields[ $name ]['field_type']['drag_and_drop'] ) || ! $fields[ $name ]['field_type']['drag_and_drop'] ) {
										$field_description = isset( $fields[ $name ]['content'] ) ? $fields[ $name ]['content'] : '';
										if ( $field_type === 'file' && $max_files > 1 ) {
											/* translators: %1$s: files limit, %2$s: maximum upload size */
											$fields[ $name ]['content'] = sprintf( '<small>%1$s</small> %2$s', sprintf( esc_html( self::$form_strings['multi_upload_maximum_allowed_file_size'] ), $max_files, size_format( $fields[ $name ]['field_type']['max_file_size'] ) ), $field_description );
										} else {
											if ( $max_file_size < $wp_max_file_size ) {
												/* translators: %s: maximum upload size */
												$fields[ $name ]['content'] = sprintf( '<small>%1$s</small> %2$s', sprintf( self::$form_strings['maximum_allowed_file_size'], size_format( $fields[ $name ]['field_type']['max_file_size'] ) ), $field_description );
											}
										}
									}
								}
								$fields[ $name ]['field_type']['type'] = $field_type;
							}

							if ( isset( $form_field['repeater'] ) ) {
								$fields[ $name ]['repeater'] = $form_field['repeater'];
							}

							if ( $form_field['default_field'] === false ) {
								$fields[ $name ]['class'] = array( 'awsm-job-form-control' );
								if ( $field_type === 'section' ) {
									$fields[ $name ]['class'] = array();
								} elseif ( $field_type === 'photo' || $field_type === 'file' ) {
									$fields[ $name ]['class'][] = 'awsm-form-file-control';
								} elseif ( $field_type === 'select' || $field_type === 'checkbox' || $field_type === 'radio' ) {
									$options_list = isset( $form_field['field_options'] ) ? $form_field['field_options'] : '';
									if ( ! empty( $options_list ) ) {
										$options_list = apply_filters( 'wpml_translate_single_string', $options_list, 'pro-pack-for-wp-job-openings', 'Application Form: Field Options for ' . $name );
									}

									$fields[ $name ]['field_type']['options'] = $this->get_field_options( $options_list );
									if ( $field_type === 'select' ) {
										$fields[ $name ]['class'][] = 'awsm-job-select-control';
									} else {
										$fields[ $name ]['class'] = array( 'awsm-job-form-options-control' );
									}
								}
							}
						}
					}
					if ( $field_type === 'text' || $field_type === 'email' || $field_type === 'number' || $field_type === 'tel' || $field_type === 'textarea' ) {
						$field_misc_options = isset( $form_field['misc_options'] ) ? $form_field['misc_options'] : array();
						$placeholder        = isset( $field_misc_options['placeholder'] ) ? $field_misc_options['placeholder'] : '';
						if ( ! empty( $placeholder ) ) {
							$placeholder = apply_filters( 'wpml_translate_single_string', $placeholder, 'pro-pack-for-wp-job-openings', 'Application Form: Placeholder for ' . $name );
						}
						$fields[ $name ]['placeholder'] = $placeholder;

						if ( $field_type === 'tel' ) {
							$country_enable = isset( $field_misc_options['country_input'] ) ? $field_misc_options['country_input'] : '';
							if ( $country_enable === 'enable' ) {
								$fields[ $name ]['field_type']['country_input']   = $country_enable;
								$fields[ $name ]['field_type']['default_country'] = isset( $field_misc_options['default_country'] ) ? $field_misc_options['default_country'] : '';
							}
						}
					}
				}
			}
			return $fields;
		}

		public function application_form_title( $title, $form_attrs = array() ) {
			$job_id = false;
			if ( isset( $form_attrs['job_id'] ) ) {
				$job_id = $form_attrs['job_id'];
			}
			$fb_options = self::get_job_form_builder_options( $job_id, true );
			$fields     = $fb_options['fields'];
			if ( ! empty( $fields ) && isset( $fields['form_title'] ) ) {
				$title = $fields['form_title'];
				if ( ! empty( $title ) ) {
					$title = apply_filters( 'wpml_translate_single_string', $title, 'pro-pack-for-wp-job-openings', 'Application Form: Form Title' );
				}
			}
			return $title;
		}

		public function application_form_description( $form_attrs = array() ) {
			$job_id = false;
			if ( isset( $form_attrs['job_id'] ) ) {
				$job_id = $form_attrs['job_id'];
			}
			$fb_options = self::get_job_form_builder_options( $job_id, true );
			$fields     = $fb_options['fields'];
			if ( ! empty( $fields ) && isset( $fields['form_description'] ) && ! empty( $fields['form_description'] ) ) {
				$form_description = apply_filters( 'wpml_translate_single_string', $fields['form_description'], 'pro-pack-for-wp-job-openings', 'Application Form: Form Description' );
				printf(
					'<div class="awsm-job-form-description">%s</div>',
					wp_kses( wpautop( $form_description ), 'post' )
				);
			}
		}

		public function form_field_content( $field_output, $field_type, $field_args ) {
			if ( ! empty( $field_type ) ) {
				$group_class = isset( $field_args['group_class'] ) && is_array( $field_args['group_class'] ) ? $field_args['group_class'] : array();
				array_unshift( $group_class, 'awsm-job-form-group', "awsm-job-form-{$field_type}-group" );
				$class_name   = implode( ' ', $group_class );
				$field_output = str_replace( 'awsm-job-form-group', $class_name, $field_output );
			}
			if ( isset( $field_args['placeholder'] ) ) {
				if ( $field_type === 'text' || $field_type === 'email' || $field_type === 'number' || $field_type === 'tel' ) {
					$field_output = str_replace( 'type="' . $field_type . '"', 'type="' . $field_type . '" placeholder="' . esc_attr( $field_args['placeholder'] ) . '"', $field_output );
				} elseif ( $field_type === 'textarea' ) {
					$field_output = str_replace( '<textarea', '<textarea placeholder="' . esc_attr( $field_args['placeholder'] ) . '"', $field_output );
				}
			}
			if ( isset( $field_args['repeater'] ) ) {
				if ( isset( $field_args['repeater']['fields'] ) && is_array( $field_args['repeater']['fields'] ) ) {
					$required          = false;
					$entry_field_names = array();
					foreach ( $field_args['repeater']['fields'] as $repeater_field ) {
						if ( ! isset( $repeater_field['entry_item'] ) || $repeater_field['entry_item'] ) {
							$entry_field_names[] = $repeater_field['name'];
						}
						if ( isset( $repeater_field['required'] ) && $repeater_field['required'] === 'required' ) {
							$required = true;
						}
					}
					$data_attrs = array(
						'entries'  => esc_attr( implode( ',', $entry_field_names ) ),
						'required' => $required ? 'true' : 'false',
					);
					if ( isset( $field_args['repeater']['max_items'] ) ) {
						$data_attrs['max'] = $field_args['repeater']['max_items'];
					}
					$attrs_content = '';
					foreach ( $data_attrs as $data_attr => $data_attr_val ) {
						$attrs_content .= sprintf( ' data-%1$s="%2$s"', esc_attr( $data_attr ), esc_attr( $data_attr_val ) );
					}
					$field_output = sprintf( '<div class="awsm-job-form-repeater-group awsm-job-form-%2$s-group"%4$s><h4>%1$s</h4><div class="awsm-job-form-repeater-entries"></div><div class="awsm-job-form-repeater-item" id="awsm-job-form-repeater-item-%3$s-0" data-name="%3$s" data-index="0">', esc_html( $field_args['label'] ), esc_attr( $field_type ), esc_attr( $field_args['key'] ), $attrs_content );
				} elseif ( isset( $field_args['repeater']['break'] ) ) {
					$add_button    = '<button type="button" class="awsm-job-form-repeater-add-control awsm-jobs-primary-button">' . esc_html_x( 'Add', 'repeater', 'pro-pack-for-wp-job-openings' ) . '</button>';
					$cancel_button = '<button type="button" class="awsm-job-form-repeater-cancel-control awsm-jobs-primary-button awsm-job-hide">' . esc_html_x( 'Cancel', 'repeater', 'pro-pack-for-wp-job-openings' ) . '</button>';
					$field_output  = $field_output . '<div class="awsm-job-form-repeater-controls">' . $add_button . $cancel_button . '</div></div></div>';
				}
			}
			return $field_output;
		}

		public function section_field_content( $field_content, $field_args ) {
			$title         = $field_args['label'];
			$description   = wpautop( $field_args['description'] );
			$field_content = sprintf( '<h3>%1$s</h3><div class="awsm-job-form-section-description">%2$s</div>', esc_html( $title ), wp_kses( $description, 'post' ) );
			return $field_content;
		}

		public function file_field_content( $field_content, $field_args ) {
			if ( isset( $field_args['field_type']['utype'] ) && $field_args['field_type']['utype'] === 'file' && isset( $field_args['field_type']['max_files'] ) && $field_args['field_type']['max_files'] > 1 ) {
				$name_attr     = sprintf( 'name="%s"', esc_attr( $field_args['key'] ) );
				$mod_attr      = sprintf( 'name="%s[]" multiple', esc_attr( $field_args['key'] ) );
				$field_content = str_replace( $name_attr, $mod_attr, $field_content );
			}
			if ( isset( $field_args['field_type']['drag_and_drop'] ) && $field_args['field_type']['drag_and_drop'] ) {
				$data_attrs    = '';
				$required      = isset( $field_args['required'] ) && $field_args['required'];
				$max_files     = isset( $field_args['field_type']['max_files'] ) && $field_args['field_type']['max_files'] ? $field_args['field_type']['max_files'] : 1;
				$max_file_size = isset( $field_args['field_type']['max_file_size'] ) && $field_args['field_type']['max_file_size'] ? $field_args['field_type']['max_file_size'] : wp_max_upload_size();

				$data = array(
					'required'  => $required ? 1 : 0,
					'accept'    => isset( $field_args['field_type']['accept'] ) ? $field_args['field_type']['accept'] : '',
					'max-files' => $max_files,
					'file-size' => $max_file_size,
				);
				foreach ( $data as $data_key => $data_value ) {
					$data_attrs .= sprintf( ' data-%s="%s"', esc_attr( $data_key ), esc_attr( $data_value ) );
				}

				$field_label_req   = $required ? ' <span class="awsm-job-form-error">*</span>' : '';
				$field_content     = sprintf( '<label for="%1$s">%2$s</label><input type="hidden" name="%1$s" />', $field_args['key'], $field_args['label'] . $field_label_req );
				$display_file_size = size_format( $max_file_size );
				/* translators: %s: maximum upload size */
				$field_description = sprintf( self::$form_strings['maximum_allowed_file_size'], $display_file_size );
				if ( $max_files > 1 ) {
					/* translators: %1$s: files limit, %2$s: maximum upload size */
					$field_description = sprintf( self::$form_strings['multi_upload_maximum_allowed_file_size'], $max_files, $display_file_size );
				}

				$dz_content    = sprintf( '<div class="awsm-form-drag-and-drop-file-control"%3$s><div class="dz-message"><span class="awsm-form-drag-and-drop-file-title">%1$s</span><span class="awsm-form-drag-and-drop-file-description">%2$s</span></div></div>', esc_html( self::$form_strings['field_description_drag_and_drop'] ), esc_html( $field_description ), $data_attrs );
				$field_content = $field_content . $dz_content;
			}
			return $field_content;
		}

		public function tel_field_content( $field_content, $field_args ) {
			if ( isset( $field_args['field_type']['country_input'] ) && $field_args['field_type']['country_input'] === 'enable' ) {
				$default_country = isset( $field_args['field_type']['default_country'] ) && $field_args['field_type']['default_country'] ? $field_args['field_type']['default_country'] : '';
				$field_content   = str_replace( 'name="', 'name="iti_', $field_content );
				$field_content   = sprintf( '<div class="awsm-job-form-iti-wrapper" data-default-country="%1$s">%2$s</div>', esc_attr( $default_country ), $field_content );
			}
			return $field_content;
		}

		public function get_recaptcha_type() {
			$type = get_option( 'awsm_jobs_recaptcha_type', 'v2' );
			if ( empty( $type ) ) {
				$type = 'v2';
			}
			return $type;
		}

		public function is_recaptcha_visible( $is_visible ) {
			if ( $this->get_recaptcha_type() === 'v3' ) {
				$is_visible = false;
			}
			return $is_visible;
		}

		public function validate_recaptcha( $token ) {
			$is_valid = false;
			if ( ! empty( $token ) ) {
				$type = $this->get_recaptcha_type();
				if ( $type === 'v2' ) {
					$is_valid = $this->validate_captcha_field( $token );
				} elseif ( $type === 'v3' ) {
					if ( method_exists( $this, 'get_recaptcha_response' ) ) {
						$result = $this->get_recaptcha_response( $token );
						if ( ! empty( $result ) && isset( $result['success'] ) && $result['success'] === true ) {
							if ( isset( $result['action'] ) && $result['action'] === 'applicationform' ) {
								$is_valid = isset( $result['score'] ) && $result['score'] > 0.5;
							}
							/**
							 * Filters the validation for the reCAPTCHA v3.
							 *
							 * @since 2.1.0
							 *
							 * @param bool $is_valid Whether the validation is success or not.
							 * @param array $result The response result of the reCAPTCHA verification.
							 */
							$is_valid = apply_filters( 'awsm_jobs_is_valid_recaptcha_v3_response', $is_valid, $result );
						}
					}
				}
			}
			return $is_valid;
		}

		public function application_btn_text( $text, $form_attrs = array() ) {
			$job_id = false;
			if ( isset( $form_attrs['job_id'] ) ) {
				$job_id = $form_attrs['job_id'];
			}
			$fb_options = self::get_job_form_builder_options( $job_id, true );
			$fields     = $fb_options['fields'];
			if ( ! empty( $fields ) && isset( $fields['btn_text'] ) && ! empty( $fields['btn_text'] ) ) {
				$text = apply_filters( 'wpml_translate_single_string', $fields['btn_text'], 'pro-pack-for-wp-job-openings', 'Application Form: Btn Text' );
			}
			return $text;
		}

		public static function get_form_confirmation( $confirmation_options = array() ) {
			$default_msg = __( 'Your application has been submitted.', 'wp-job-openings' );
			if ( empty( $confirmation_options ) ) {
				$confirmation_options = get_option( 'awsm_jobs_form_confirmation_type' );
			}
			$defaults          = array(
				'type'         => 'message',
				'message'      => $default_msg,
				'page'         => '',
				'redirect_url' => '',
			);
			$form_confirmation = wp_parse_args( $confirmation_options, $defaults );
			if ( empty( $form_confirmation['message'] ) ) {
				$form_confirmation['message'] = $default_msg;
			}
			if ( empty( $confirmation_options ) ) {
				$form_confirmation['_default'] = true;
			}
			return $form_confirmation;
		}

		public static function get_resume_default_allowed_types() {
			$allowed_types = array();
			$file_types    = get_option( 'awsm_jobs_admin_upload_file_ext' );
			if ( is_array( $file_types ) && ! empty( $file_types ) ) {
				$allowed_types = join( ',', $file_types );
			} else {
				$allowed_types = 'pdf,doc,docx';
			}
			return $allowed_types;
		}

		public static function get_unique_file_name( $file_name ) {
			return hash( 'sha1', ( $file_name . uniqid( (string) rand(), true ) . microtime( true ) ) ) . time();
		}

		public static function get_upload_directory() {
			$upload_dir = wp_upload_dir();
			$base_dir   = trailingslashit( $upload_dir['basedir'] );
			$upload_dir = trailingslashit( $base_dir . AWSM_JOBS_UPLOAD_DIR_NAME );
			return $upload_dir;
		}

		public static function get_temp_upload_directory() {
			$upload_dir = wp_upload_dir();
			$base_dir   = trailingslashit( $upload_dir['basedir'] );
			$upload_dir = trailingslashit( $base_dir . AWSM_JOBS_UPLOAD_DIR_NAME ) . self::$temp_dir_name;
			return $upload_dir;
		}

		public static function get_temp_uploaded_file_path( $file_name ) {
			return wp_normalize_path( self::get_temp_upload_directory() . '/' . $file_name );
		}

		public function temp_upload_dir( $param ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'awsm_applicant_form_file_upload' ) {
				$subdir = '/' . trailingslashit( AWSM_JOBS_UPLOAD_DIR_NAME ) . self::$temp_dir_name;
				if ( empty( $param['subdir'] ) ) {
					$param['path']   = $param['path'] . $subdir;
					$param['url']    = $param['url'] . $subdir;
					$param['subdir'] = $subdir;
				} else {
					$subdir         .= $param['subdir'];
					$param['path']   = str_replace( $param['subdir'], $subdir, $param['path'] );
					$param['url']    = str_replace( $param['subdir'], $subdir, $param['url'] );
					$param['subdir'] = str_replace( $param['subdir'], $subdir, $param['subdir'] );
				}
			}

			return $param;
		}

		public function intermediate_image_sizes_handler( $new_sizes, $image_meta ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( isset( $_POST['action'] ) && $_POST['action'] === 'awsm_applicant_attachments_handler' ) {
				if ( strpos( $image_meta['file'], 'awsm-job-openings' ) !== false ) {
					$sizes = array();
					if ( isset( $new_sizes['thumbnail'] ) ) {
						$sizes['thumbnail'] = $new_sizes['thumbnail'];
					}
					if ( isset( $new_sizes['medium'] ) ) {
						$sizes['medium'] = $new_sizes['medium'];
					}
					$new_sizes = $sizes;
				}
			}
			return $new_sizes;
		}

		public function get_attached_file_handler( $file, $attachment_id ) {
			// Fix for attachment file path in Windows systems.
			if ( ! empty( $file ) && strpos( $file, 'awsm-job-openings/' ) !== false && ! file_exists( $file ) ) {
				$file_meta = get_post_meta( $attachment_id, '_wp_attached_file', true );
				if ( ! empty( $file_meta ) && file_exists( $file_meta ) ) {
					$file = $file_meta;
				}
			}
			return $file;
		}

		public function check_filetype_and_ext( $wp_filetype, $file, $filename, $mimes, $real_mime = '' ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! empty( $real_mime ) && isset( $_POST['action'] ) && $_POST['action'] === 'awsm_applicant_form_file_upload' && empty( $wp_filetype['type'] ) && ! empty( $mimes ) ) {
				$filetype = wp_check_filetype( $filename, $mimes );
				// fix issue with application/vnd.openxmlformats-officedocument.* mime types in some PHP versions.
				$extensions = array( 'docx', 'dotx', 'xlsx', 'xltx', 'pptx', 'ppsx', 'potx', 'sldx' );
				if ( $filetype['ext'] && in_array( $filetype['ext'], $extensions, true ) && $real_mime === $filetype['type'] . $filetype['type'] ) {
					$wp_filetype['ext']  = $filetype['ext'];
					$wp_filetype['type'] = $filetype['type'];
				}
			}
			return $wp_filetype;
		}

		public function get_allowed_mimes( $type, $other_options ) {
			$allowed_types      = array();
			$allowed_mime_types = get_allowed_mime_types();

			$file_types = isset( $other_options['file_types'] ) ? $other_options['file_types'] : '';
			if ( $type === 'resume' && empty( $file_types ) ) {
				$file_types = self::get_resume_default_allowed_types();
			}
			if ( $type === 'photo' && empty( $file_types ) ) {
				$file_types = 'jpg,png';
			}
			if ( ! empty( $file_types ) ) {
				$allowed_types = self::get_valid_file_types( $file_types );
			}

			$mimes = array();
			if ( ! empty( $allowed_types ) ) {
				foreach ( $allowed_types as $allowed_type ) {
					foreach ( $allowed_mime_types as $ext_preg => $mime_type ) {
						$pattern = '/\.(' . $ext_preg . ')$/i';
						if ( preg_match( $pattern, '.' . $allowed_type ) ) {
							$mimes[ $ext_preg ] = $mime_type;
						}
					}
				}
			}
			return $mimes;
		}

		public function handle_file_upload( $type, $file, $other_options = array(), $is_temp = false ) {
			if ( ! function_exists( 'wp_handle_upload' ) ) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			if ( ! function_exists( 'wp_crop_image' ) ) {
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}

			$upload_overrides = array(
				'test_form'                => false,
				'mimes'                    => $this->get_allowed_mimes( $type, $other_options ),
				'unique_filename_callback' => array( $this, 'hashed_file_name' ),
			);

			$function_name = $is_temp ? 'temp_upload_dir' : 'upload_dir';
			add_filter( 'upload_dir', array( $this, $function_name ) );
			$movefile = wp_handle_upload( $file, $upload_overrides );
			remove_filter( 'upload_dir', array( $this, $function_name ) );

			if ( $movefile && ! isset( $movefile['error'] ) ) {
				$movefile['file_name'] = $file['name']; // Adding the original file name here
			}

			return $movefile;
		}

		public static function sanitize_custom_form_content( $content ) {
			$allowed_html           = wp_kses_allowed_html( 'post' );
			$allowed_html['iframe'] = array(
				'src'          => true,
				'width'        => true,
				'height'       => true,
				'marginwidth'  => true,
				'marginheight' => true,
				'frameborder'  => true,
			);
			return wp_kses( $content, $allowed_html );
		}

		public static function sanitize_fields( $field_type, $field_name, $field_data = array() ) {
			$field_value = '';
			$input       = '';
			if ( ! empty( $field_data ) ) {
				$input = $field_data[ $field_name ];
			} else {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing
				$input = $_POST[ $field_name ];
			}
			switch ( $field_type ) {
				case 'email':
					$field_value = sanitize_email( wp_unslash( $input ) );
					break;
				case 'number':
					$field_value = is_numeric( $input ) ? intval( $input ) : '';
					break;
				case 'url':
					$field_value = esc_url_raw( wp_unslash( $input ) );
					break;
				case 'checkbox':
					$field_value = is_array( $input ) ? sanitize_text_field( join( ', ', wp_unslash( $input ) ) ) : '';
					break;
				case 'textarea':
					$field_value = awsm_jobs_sanitize_textarea( wp_unslash( $input ) );
					break;
				default:
					$field_value = sanitize_text_field( wp_unslash( $input ) );
					break;
			}
			return $field_value;
		}

		public static function get_form_strings( $translate = false ) {
			$defaults         = array(
				'field_description_drag_and_drop'        => __( 'Drop files here or click to upload', 'pro-pack-for-wp-job-openings' ),
				'uploading_drag_and_drop'                => _x( 'Uploading....', 'file upload', 'pro-pack-for-wp-job-openings' ),
				'cancel_uploading_drag_and_drop'         => _x( 'Are you sure you want to cancel this upload?', 'file upload', 'pro-pack-for-wp-job-openings' ),
				/* translators: %1$s: comma separated list of allowed file types */
				'allowed_file_types'                     => __( 'Allowed Type(s): %1$s', 'wp-job-openings' ),
				/* translators: %s: maximum allowed file size */
				'maximum_allowed_file_size'              => __( 'Maximum allowed file size is %s.', 'pro-pack-for-wp-job-openings' ),
				/* translators: %1$s: maximum allowed files for upload, %2$s: maximum allowed file size */
				'multi_upload_maximum_allowed_file_size' => __( 'You can upload upto %1$s files, %2$s per file.', 'pro-pack-for-wp-job-openings' ),
				'submitting'                             => __( 'Submitting..', 'wp-job-openings' ),
				'restrict_application_form'              => __( 'Sorry, it looks like you have already submitted an application for this job.', 'pro-pack-for-wp-job-openings' ),
				'error'                                  => __( 'Error in submitting your application. Please refresh the page and retry.', 'wp-job-openings' ),
			);
			$mod_form_strings = AWSM_Job_Openings_Pro_Main::get_translated_strings( 'awsm_jobs_customize_form_strings', $defaults, $translate );
			if ( strpos( $mod_form_strings['allowed_file_types'], '{{fileTypes}}' ) !== false ) {
				$mod_form_strings['allowed_file_types'] = str_replace( '{{fileTypes}}', '%1$s', $mod_form_strings['allowed_file_types'] );
			}
			if ( strpos( $mod_form_strings['maximum_allowed_file_size'], '{{maxFilesize}}' ) !== false ) {
				$mod_form_strings['maximum_allowed_file_size'] = str_replace( '{{maxFilesize}}', '%s', $mod_form_strings['maximum_allowed_file_size'] );
			}
			if ( strpos( $mod_form_strings['multi_upload_maximum_allowed_file_size'], '{{maxFiles}}' ) !== false || strpos( $mod_form_strings['multi_upload_maximum_allowed_file_size'], '{{maxFilesize}}' ) !== false ) {
				$mod_form_strings['multi_upload_maximum_allowed_file_size'] = str_replace( array( '{{maxFiles}}', '{{maxFilesize}}' ), array( '%1$s', '%2$s' ), $mod_form_strings['multi_upload_maximum_allowed_file_size'] );
			}
			return $mod_form_strings;
		}

		public function customize_form_strings() {
			self::$form_strings = self::get_form_strings( true );
			if ( ! isset( self::$form_strings['_default'] ) ) {
				add_filter( 'awsm_application_form_submit_btn_res_text', array( $this, 'submit_btn_res_text' ) );
			}
		}

		public function submit_btn_res_text() {
			return self::$form_strings['submitting'];
		}

		public static function get_validation_notices( $translate = false ) {
			$defaults               = array(
				'required'                => __( 'This field is required.', 'wp-job-openings' ),
				'email'                   => __( 'Please enter a valid email address.', 'wp-job-openings' ),
				'file_size_default'       => __( 'The file you have selected is too large.', 'wp-job-openings' ),
				'file_size_drag_and_drop' => _x( 'File upload failed. Maximum allowed file size is {{maxFilesize}}MB', 'file upload', 'pro-pack-for-wp-job-openings' ),
				'max_files'               => _x( 'You are not allowed to upload more than the file limit: {{maxFiles}}.', 'file upload', 'pro-pack-for-wp-job-openings' ),
				'file_type'               => _x( "You can't upload files of this type.", 'file upload', 'pro-pack-for-wp-job-openings' ),
				'gdpr'                    => __( 'Please agree to our privacy policy.', 'wp-job-openings' ),
				'recaptcha'               => __( 'Please verify that you are not a robot.', 'wp-job-openings' ),
			);
			$mod_validation_notices = AWSM_Job_Openings_Pro_Main::get_translated_strings( 'awsm_jobs_customize_form_validation_notices', $defaults, $translate );
			return $mod_validation_notices;
		}

		public function customize_validation_notices() {
			self::$validation_notices = self::get_validation_notices( true );
			if ( ! isset( self::$validation_notices['_default'] ) ) {
				add_action(
					'wp_loaded',
					function() {
						remove_action( 'awsm_application_form_field_init', array( AWSM_Job_Openings_Form::init(), 'form_field_init' ) );
					}
				);
				add_action( 'awsm_application_form_field_init', array( $this, 'pro_form_field_init' ) );
			}
		}

		public function get_generic_submission_data() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$data = array(
				'job_id'       => intval( $_POST['awsm_job_id'] ),
				'agree_policy' => false,
				'error'        => array(),
			);

			$form_data = self::get_custom_form_data( $data['job_id'] );
			if ( ! empty( $form_data ) && ( $form_data['id'] === 'disable' || $form_data['id'] === 'custom_form' || $form_data['id'] === 'custom_button' ) ) {
				$data['error'][] = esc_html__( 'User is not allowed to perform this action.', 'wp-job-openings' );
			} else {
				if ( $this->is_recaptcha_set() ) {
					$is_human = false;
					if ( isset( $_POST['g-recaptcha-response'] ) ) {
						$is_human = $this->validate_recaptcha( $_POST['g-recaptcha-response'] );
					}
					if ( ! $is_human ) {
						$data['error'][] = self::$validation_notices['recaptcha'];
					}
				}

				if ( $this->get_gdpr_field_label() !== false ) {
					if ( ! isset( $_POST['awsm_form_privacy_policy'] ) || $_POST['awsm_form_privacy_policy'] !== 'yes' ) {
						$data['error'][] = self::$validation_notices['gdpr'];
					} else {
						$data['agree_policy'] = sanitize_text_field( $_POST['awsm_form_privacy_policy'] );
					}
				}

				if ( get_post_type( $data['job_id'] ) !== 'awsm_job_openings' ) {
					$data['error'][] = esc_html__( 'Error occurred: Invalid Job.', 'wp-job-openings' );
				}

				if ( get_post_status( $data['job_id'] ) === 'expired' ) {
					$data['error'][] = esc_html__( 'Sorry! This job is expired.', 'wp-job-openings' );
				} else {
					if ( AWSM_Job_Openings_Pro_Pack::is_position_filled( $data['job_id'] ) ) {
						$job_detail_strings = AWSM_Job_Openings_Pro_Main::$job_detail_strings;
						$data['error'][]    = esc_html( $job_detail_strings['position_filled'] );
					}
				}
			}
			// phpcs:enable
			return $data;
		}

		public function get_user_submitted_data( $job_id ) {
			$data = array(
				'fields' => array(),
				'error'  => array(),
			);

			$generic_req_msg = esc_html__( 'Please fill the required field.', 'pro-pack-for-wp-job-openings' );
			/* translators: %s: application form field label */
			$req_msg = esc_html__( '%s is required.', 'pro-pack-for-wp-job-openings' );

			$fb_options  = self::get_job_form_builder_options( $job_id );
			$form_fields = $fb_options['fields'];
			if ( is_numeric( $fb_options['id'] ) ) {
				$data['form_id'] = $fb_options['id'];
			}

			foreach ( $form_fields as $form_field ) {
				$field_type  = $form_field['field_type'];
				$is_repeater = isset( $form_field['repeater'] ) && isset( $form_field['repeater']['fields'] );
				if ( $is_repeater ) {
					$field_type = 'repeater';
				}

				if ( $form_field['active'] === 'active' && $field_type !== 'section' ) {
					$field_value   = '';
					$field_label   = $form_field['label'];
					$field_name    = $form_field['name'];
					$other_options = isset( $form_field['misc_options'] ) ? $form_field['misc_options'] : array();

					if ( $field_type !== 'repeater' ) {
						$drag_and_drop = isset( $other_options['drag_and_drop'] ) && $other_options['drag_and_drop'] === 'enable';
						$max_files     = isset( $other_options['multi_upload'] ) ? absint( $other_options['multi_upload'] ) : 1;
						$multi_upload  = $max_files > 1;

						if ( $field_type !== 'resume' && $field_type !== 'photo' && $field_type !== 'file' ) {
							// phpcs:ignore WordPress.Security.NonceVerification.Missing
							if ( isset( $_POST[ $field_name ] ) ) {
								$field_value = self::sanitize_fields( $field_type, $field_name );
							}
						} else {
							$max_file_size    = isset( $other_options['max_file_size'] ) ? intval( $other_options['max_file_size'] ) : wp_max_upload_size();
							$file_limit_error = str_replace( '{{maxFiles}}', strval( $max_files ), esc_html_x( 'You are not allowed to upload more than the file limit: {{maxFiles}}.', 'file upload', 'pro-pack-for-wp-job-openings' ) );
							$file_size_error  = sprintf( self::$form_strings['maximum_allowed_file_size'], size_format( $max_file_size ) );

							if ( ! $drag_and_drop ) {
								$field_value = isset( $_FILES[ $field_name ] ) ? $_FILES[ $field_name ] : '';
								if ( isset( $field_value['size'] ) ) {
									$uploaded_files_count = 1;
									if ( is_array( $field_value['size'] ) ) {
										$uploaded_files_count = count( $field_value['size'] );
										foreach ( $field_value['size'] as $file_size ) {
											if ( $file_size > $max_file_size ) {
												$data['error'][ $field_name ] = $file_size_error;
												break;
											}
										}
									} else {
										if ( ! is_numeric( $field_value['size'] ) || $field_value['size'] > $max_file_size ) {
											$data['error'][ $field_name ] = $file_size_error;
										}
									}
									if ( $uploaded_files_count > $max_files ) {
										$data['error'][ $field_name ] = $file_limit_error;
									}
								}
							} else {
								$field_value      = array(
									'error' => ! $multi_upload ? 1 : array( 1 ),
								);
								$file_field_error = true;
								// phpcs:ignore WordPress.Security.NonceVerification.Missing
								$uploaded_files = isset( $_POST[ $field_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field_name ] ) ) : '';
								if ( ! empty( $uploaded_files ) ) {
									$uploaded_files = json_decode( $uploaded_files, true );
									if ( is_array( $uploaded_files ) && count( $uploaded_files ) ) {
										$uploaded_files_count = count( $uploaded_files );
										if ( $uploaded_files_count > $max_files ) {
											$data['error'][ $field_name ] = $file_limit_error;
										}
										foreach ( $uploaded_files as $uploaded_file ) {
											$file_path  = isset( $uploaded_file['file'] ) ? $uploaded_file['file'] : '';
											$file_title = isset( $uploaded_file['title'] ) ? base64_decode( $uploaded_file['title'] ) : '';
											if ( ! empty( $file_path ) ) {
												$full_file_path = self::get_temp_uploaded_file_path( $file_path );

												if ( @is_file( $full_file_path ) ) {
													$file_size = @filesize( $full_file_path );
													if ( ! is_numeric( $file_size ) || $file_size > $max_file_size ) {
														$data['error'][ $field_name ] = $file_size_error;
														break;
													}
												} else {
													$data['error'][ $field_name ] = esc_html__( 'Invalid file path.', 'pro-pack-for-wp-job-openings' );
													break;
												}
											} else {
												$data['error'][ $field_name ] = esc_html__( 'File path is missing.', 'pro-pack-for-wp-job-openings' );
												break;
											}
										}

										if ( ! isset( $data['error'][ $field_name ] ) ) {
											$wp_upload_dir = wp_upload_dir();
											$allowed_mimes = $this->get_allowed_mimes( $field_type, $other_options );

											foreach ( $uploaded_files as $uploaded_file ) {
												$file_path      = isset( $uploaded_file['file'] ) ? $uploaded_file['file'] : '';
												$file_title     = isset( $uploaded_file['title'] ) ? base64_decode( $uploaded_file['title'] ) : '';
												$full_file_path = self::get_temp_uploaded_file_path( $file_path );
												$file_type      = wp_check_filetype( $full_file_path, $allowed_mimes );

												if ( $file_type['type'] ) {
													$file_name     = basename( $file_path, '.' . $file_type['ext'] );
													$new_file_name = self::get_unique_file_name( $file_name ) . '.' . $file_type['ext'];
													$sub_path      = str_replace( $file_name . '.' . $file_type['ext'], '', $file_path );
													$new_file_dir  = self::get_upload_directory() . $sub_path;

													if ( ! file_exists( $new_file_name ) ) {
														wp_mkdir_p( $new_file_dir );
													}

													$new_file_path = wp_normalize_path( $new_file_dir . '/' . $new_file_name );

													$file_field_error = false;
													$file_data        = array(
														'file' => $new_file_path,
														'temp_file' => $full_file_path,
														'url'  => $wp_upload_dir['baseurl'] . '/' . AWSM_JOBS_UPLOAD_DIR_NAME . '/' . trailingslashit( $sub_path ) . $new_file_name,
														'type' => $file_type['type'],
														'file_name' => $file_title,
													);

													if ( $multi_upload ) {
														if ( ! isset( $field_value['multi_data'] ) ) {
															$field_value['multi_data'] = array();
														}
														$field_value['multi_data'][] = $file_data;
													} else {
														$field_value = $file_data;
													}
												} else {
													$file_field_error = true;
												}

												if ( $file_field_error ) {
													break;
												}
											}
										}
									}
								} else {
									if ( $form_field['required'] !== 'required' ) {
										$file_field_error = false;
									}
								}
								if ( ! $file_field_error ) {
									$field_value['error'] = ! $multi_upload ? 0 : array( 0 );
								}
							}
						}

						if ( ! isset( $data['error'][ $field_name ] ) && $form_field['required'] === 'required' ) {
							if ( empty( $field_value ) && ! is_numeric( $field_value ) ) {
								$data['error'][ $field_name ] = ! empty( $field_label ) ? sprintf( $req_msg, $field_label ) : $generic_req_msg;
							} else {
								if ( $field_type === 'resume' || $field_type === 'photo' || $field_type === 'file' ) {
									$req_file_error = false;

									if ( $field_type === 'file' && $multi_upload ) {
										$req_file_error = array_sum( $field_value['error'] ) > 0;
									} else {
										$req_file_error = $field_value['error'] > 0;
									}
									if ( $req_file_error ) {
										/* translators: %s: application form field type */
										$data['error'][ $field_name ] = sprintf( esc_html__( 'Please select a valid %s.', 'pro-pack-for-wp-job-openings' ), $field_type );
									}
								}
							}
						}

						if ( empty( $data['error'] ) ) {
							if ( $field_type === 'resume' || $field_type === 'photo' || $field_type === 'file' ) {
								$upload_file = false;
								if ( isset( $field_value['error'] ) ) {
									if ( $field_type === 'file' && $multi_upload ) {
										$upload_file = array_sum( $field_value['error'] ) === 0;
									} else {
										$upload_file = $field_value['error'] === 0;
									}
								}
								if ( $upload_file ) {
									if ( ! $drag_and_drop ) {
										$file_error = array();
										if ( $field_type === 'file' && $multi_upload ) {
											$files_data = array();
											foreach ( $field_value['name'] as $file_key => $file_name ) {
												$sub_file = array(
													'name' => $file_name,
													'type' => $field_value['type'][ $file_key ],
													'tmp_name' => $field_value['tmp_name'][ $file_key ],
													'error' => $field_value['error'][ $file_key ],
													'size' => $field_value['size'][ $file_key ],
												);
												$movefile = $this->handle_file_upload( 'file', $sub_file, $other_options );
												if ( $movefile && ! isset( $movefile['error'] ) ) {
													$files_data[] = $movefile;
												} else {
													$file_error['file_name'] = $file_name;
													$file_error['error']     = $movefile['error'];
													break;
												}
											}
											if ( empty( $file_error ) ) {
												$field_value['multi_data'] = $files_data;
											}
										} else {
											$movefile = $this->handle_file_upload( $field_type, $field_value, $other_options );
											if ( $movefile && ! isset( $movefile['error'] ) ) {
												$field_value = $movefile;
											} else {
												$file_error['file_name'] = $field_value['name'];
												$file_error['error']     = $movefile['error'];
											}
										}
										if ( ! empty( $file_error ) ) {
											$data['error'][ $field_name ] = esc_html( $file_error['file_name'] ) . ' - ' . esc_html( $file_error['error'] );
											break;
										}
									} else {
										if ( isset( $field_value['error'] ) ) {
											unset( $field_value['error'] );
										}
									}
								} else {
									$field_value = '';
								}
							} else {
								$field_specific_check = true;
								// phpcs:ignore WordPress.Security.NonceVerification.Missing
								if ( $form_field['required'] !== 'required' && isset( $_POST[ $field_name ] ) && empty( $_POST[ $field_name ] ) && ! is_numeric( $_POST[ $field_name ] ) ) {
									$field_specific_check = false;
								}
								if ( $field_specific_check ) {
									if ( $field_type === 'email' && ! is_email( $field_value ) ) {
										$data['error'][ $field_name ] = esc_html__( 'Invalid email format.', 'wp-job-openings' );
									} elseif ( $field_type === 'tel' ) {
										$tel_error      = false;
										$country_enable = isset( $other_options['country_input'] ) ? ( $other_options['country_input'] ) : '';
										if ( $country_enable !== 'enable' ) {
											if ( ! preg_match( '%^[+]?[0-9()/ -]*$%', trim( $field_value ) ) ) {
												$tel_error = true;
											}
										} else {
											if ( intval( $field_value ) === -1 ) {
												$tel_error = true;
											}
										}
										if ( $tel_error ) {
											$data['error'][ $field_name ] = esc_html__( 'Invalid phone number.', 'wp-job-openings' );
										}
									} elseif ( $field_type === 'date' ) {
										$valid_date = false;
										// Validate yyyy-mm-dd formatted value return by the Date field.
										if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $field_value, $matches ) ) {
											$valid_date = wp_checkdate( $matches[2], $matches[3], $matches[1], $field_value );
										}
										if ( ! $valid_date ) {
											$data['error'][ $field_name ] = esc_html__( 'Invalid date format.', 'pro-pack-for-wp-job-openings' );
										}
									}
								}
							}
						}
					} else {
						$repeater_error     = false;
						$repeater_error_msg = esc_html__( 'Invalid field values. Please enter valid values and try again!', 'pro-pack-for-wp-job-openings' );
						// phpcs:ignore WordPress.Security.NonceVerification.Missing
						if ( ! isset( $_POST[ $field_name ] ) || ! is_array( $_POST[ $field_name ] ) ) {
							$repeater_error = true;
						} else {
							$field_value     = array();
							$repeater_fields = $form_field['repeater']['fields'];
							// phpcs:ignore WordPress.Security.NonceVerification.Missing
							$repeater_data = $_POST[ $field_name ];
							unset( $repeater_data[0] );

							$repeater_required = false;
							foreach ( $repeater_fields as $repeater_field ) {
								if ( isset( $repeater_field['required'] ) && $repeater_field['required'] === 'required' ) {
									$repeater_required = true;
								}
							}
							if ( $repeater_required && empty( $repeater_data ) ) {
								$repeater_error = true;
							}

							if ( isset( $form_field['repeater']['max_items'] ) ) {
								$max_items = $form_field['repeater']['max_items'];
								if ( count( $repeater_data ) > $max_items ) {
									$repeater_error = true;
									/* translators: %s: repeater limit */
									$repeater_error_msg = sprintf( esc_html__( 'The maximum allowed repeated entries limit is %s.', 'pro-pack-for-wp-job-openings' ), esc_html( $max_items ) );
								}
							}

							if ( ! $repeater_error ) {
								$repeater_index = 0;
								foreach ( $repeater_data as $item_data ) {
									$filtered_data = array_filter( $item_data, 'strlen' );
									if ( empty( $filtered_data ) ) {
										$repeater_error = true;
										break;
									}
									foreach ( $repeater_fields as $repeater_field ) {
										$item_field_value = '';
										$item_field_name  = $repeater_field['name'];
										$item_field_type  = $repeater_field['field_type'];
										$item_field_label = $repeater_field['label'];
										$item_required    = isset( $repeater_field['required'] ) && $repeater_field['required'] === 'required';
										if ( isset( $filtered_data[ $item_field_name ] ) ) {
											$item_field_value = self::sanitize_fields( $item_field_type, $item_field_name, $filtered_data );

											$field_value[ $repeater_index ][ $item_field_name ] = array(
												'label' => $item_field_label,
												'type'  => $item_field_type,
												'value' => $item_field_value,
											);
										}
										if ( $item_required && empty( $item_field_value ) ) {
											$repeater_error = true;
											break 2;
										}
									}
									$repeater_index++;
								}
							}
						}
						if ( $repeater_error ) {
							$data['error'][ $field_name ] = $repeater_error_msg;
						}
					}

					$data['fields'][ $field_name ] = array(
						'value' => $field_value,
						'type'  => $field_type,
						'spec'  => 'default',
					);
					if ( $form_field['default_field'] === false ) {
						$data['fields'][ $field_name ]['label'] = $field_label;
						$data['fields'][ $field_name ]['spec']  = 'custom';
					}
				}
			}

			foreach ( $form_fields as $form_field ) {
				$field_label = $form_field['label'];
				$field_name  = $form_field['name'];
				if ( ! empty( $data['error'] ) && isset( $data['error'][ $field_name ] ) ) {
					$data['error'][ $field_name ] = sprintf( '<strong>%1$s:</strong> %2$s', esc_html( $field_label ), $data['error'][ $field_name ] );
				}
			}

			$data['error'] = array_values( $data['error'] );
			return $data;
		}

		public function insert_attachment( $application_id, $post_base_data, $file_data ) {
			if ( isset( $file_data['temp_file'] ) ) {
				@copy( $file_data['temp_file'], $file_data['file'] );
				$delete_file = @unlink( $file_data['temp_file'] );
				if ( ! $delete_file ) {
					return false;
				}
			}
			$attachment_data = array_merge(
				$post_base_data,
				array(
					'post_mime_type' => $file_data['type'],
					'guid'           => $file_data['url'],
				)
			);

			$attach_id = wp_insert_attachment( $attachment_data, $file_data['file'], $application_id );
			if ( empty( $attach_id ) || is_wp_error( $attach_id ) ) {
				$attach_id = false;
			}

			if ( isset( $file_data['file_name'] ) ) {
				if ( is_string( $file_data['file_name'] ) ) {
					update_post_meta( $attach_id, 'awsm_actual_file_name', sanitize_text_field( $file_data['file_name'] ) );
				}
			}

			return $attach_id;
		}

		public function insert_application() {
			global $awsm_response;

			$awsm_response = array(
				'success' => array(),
				'error'   => array(),
			);
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( $_SERVER['REQUEST_METHOD'] === 'POST' && ! empty( $_POST['action'] ) && $_POST['action'] === 'awsm_applicant_form_submission' ) {
				$generic_data           = $this->get_generic_submission_data();
				$job_id                 = $generic_data['job_id'];
				$awsm_response['error'] = $generic_data['error'];
				$job_status             = get_post_status( $job_id );
				if ( $job_status !== 'publish' ) {
					$awsm_response['error'][] = esc_html__( 'Private job submission is not allowed.', 'pro-pack-for-wp-job-openings' );
				}

				$restrict_application = get_option( 'awsm_jobs_restrict_application_form' );
				if ( count( $awsm_response['error'] ) === 0 && $restrict_application === 'restrict' ) {
					if ( self::is_multiple_submission( $job_id ) ) {
						$awsm_response['error'][] = self::$form_strings['restrict_application_form'];
					}
				}

				// Check if there are no generic submission data errors.
				if ( count( $awsm_response['error'] ) === 0 ) {
					$submitted_data         = $this->get_user_submitted_data( $job_id );
					$fields                 = $submitted_data['fields'];
					$awsm_response['error'] = $submitted_data['error'];

					do_action( 'awsm_job_application_submitting' );

					// Check if super fields exist. If not, return generic error message.
					if ( ! isset( $fields['awsm_applicant_name'] ) || ! isset( $fields['awsm_applicant_email'] ) ) {
						$awsm_response['error'][] = self::$form_strings['error'];
					}

					// Check if there are no user submitted data errors.
					if ( count( $awsm_response['error'] ) === 0 ) {
						$applicant_name   = $fields['awsm_applicant_name']['value'];
						$post_base_data   = array(
							'post_title'     => $applicant_name,
							'post_content'   => '',
							'post_status'    => 'publish',
							'comment_status' => 'closed',
						);
						$application_data = array_merge(
							$post_base_data,
							array(
								'post_type'   => 'awsm_job_application',
								'post_parent' => $job_id,
							)
						);
						$application_id   = wp_insert_post( $application_data );

						if ( ! empty( $application_id ) && ! is_wp_error( $application_id ) ) {
							$custom_fields     = array();
							$applicant_details = array(
								'awsm_job_id'       => $job_id,
								'awsm_apply_for'    => html_entity_decode( get_the_title( $job_id ) ),
								'awsm_applicant_ip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
							);

							if ( ! empty( $generic_data['agree_policy'] ) ) {
								$applicant_details['awsm_agree_privacy_policy'] = $generic_data['agree_policy'];
							}

							foreach ( $fields as $field_name => $field ) {
								if ( $field['type'] !== 'resume' ) {
									if ( $field['spec'] === 'default' ) {
										$applicant_details[ $field_name ] = $field['value'];
									} else {
										$field_meta_value = $field['value'];
										if ( $field['type'] === 'photo' || $field['type'] === 'file' ) {
											$field_meta_value = '';
										}
										$custom_fields[ $field_name ] = array(
											'label' => $field['label'],
											'type'  => $field['type'],
											'value' => $field_meta_value,
										);
									}
								}
							}

							if ( isset( $submitted_data['form_id'] ) && get_post_type( $submitted_data['form_id'] ) === 'awsm_job_form' ) {
								$applicant_details['awsm_job_form_id'] = intval( $submitted_data['form_id'] );
							}

							foreach ( $applicant_details as $meta_key => $meta_value ) {
								update_post_meta( $application_id, $meta_key, $meta_value );
							}

							if ( ! empty( $custom_fields ) ) {
								update_post_meta( $application_id, 'awsm_applicant_custom_fields', $custom_fields );
								$applicant_details['custom_fields'] = $custom_fields;
							}

							// Handle attachments.
							foreach ( $fields as $field_name => $field ) {
								if ( $field['type'] === 'resume' || $field['type'] === 'photo' || $field['type'] === 'file' ) {
									$is_file_error = false;
									if ( ! empty( $field['value'] ) ) {
										if ( isset( $field['value']['multi_data'] ) ) {
											$multi_attach_ids = array();
											foreach ( $field['value']['multi_data'] as $mult_file_data ) {
												$attach_id = $this->insert_attachment( $application_id, $post_base_data, $mult_file_data );
												if ( $attach_id ) {
													$multi_attach_ids[] = $attach_id;
												} else {
													$is_file_error = true;
													break;
												}
											}
											if ( ! $is_file_error ) {
												$fields[ $field_name ]['value'] = $multi_attach_ids;
											}
										} else {
											if ( isset( $field['value']['file'] ) ) {
												$attach_id = $this->insert_attachment( $application_id, $post_base_data, $field['value'] );
												if ( $attach_id ) {
													$fields[ $field_name ]['value'] = $attach_id;
												} else {
													$is_file_error = true;
												}
											}
										}
									}
									if ( $is_file_error ) {
										$awsm_response['error'][] = self::$form_strings['error'];
										break;
									} else {
										if ( $field['type'] === 'resume' ) {
											update_post_meta( $application_id, 'awsm_attachment_id', $fields[ $field_name ]['value'] );
											$applicant_details['awsm_attachment_id'] = $fields[ $field_name ]['value'];
										} else {
											if ( ! empty( $custom_fields ) && isset( $custom_fields[ $field_name ] ) ) {
												$custom_fields[ $field_name ] = array(
													'label' => $field['label'],
													'type' => $field['type'],
													'value' => $fields[ $field_name ]['value'],
												);
												update_post_meta( $application_id, 'awsm_applicant_custom_fields', $custom_fields );
											}
										}
									}
								}
							}

							if ( count( $awsm_response['error'] ) === 0 ) {
								$applicant_details['custom_fields'] = $custom_fields;

								// Now, send notification email
								$applicant_details['application_id'] = $application_id;
								if ( method_exists( 'AWSM_Job_Openings_Form', 'get_notification_options' ) && isset( $submitted_data['form_id'] ) ) {
									$form_id           = $submitted_data['form_id'];
									$notification_data = array(
										'applicant' => self::get_notification_options_by_form_id( $form_id, 'applicant' ),
										'admin'     => self::get_notification_options_by_form_id( $form_id, 'admin' ),
									);
									$this->notification_email( $applicant_details, $notification_data );
								} else {
									$this->notification_email( $applicant_details );
								}

								$form_confirmation = self::get_form_confirmation();
								if ( $form_confirmation['type'] === 'page' ) {
									$awsm_response['data']['type'] = 'page';
									$awsm_response['data']['url']  = esc_url( get_page_link( $form_confirmation['page'] ) );
								} elseif ( $form_confirmation['type'] === 'redirect_url' ) {
									$awsm_response['data']['type'] = 'redirect_url';
									$awsm_response['data']['url']  = esc_url( $form_confirmation['redirect_url'] );
								}
								$success_msg = $form_confirmation['message'];
								if ( ! isset( $form_confirmation['_default'] ) ) {
									$success_msg = apply_filters( 'wpml_translate_single_string', $form_confirmation['message'], 'pro-pack-for-wp-job-openings', 'Application Form: Submit confirmation message' );
								}
								$awsm_response['data']['id'] = $application_id;
								$awsm_response['success'][]  = wp_kses( $success_msg, 'post' );

								do_action( 'awsm_job_application_submitted', $application_id );
							} else {
								wp_delete_post( $application_id, true );
							}
						} else {
							$awsm_response['error'][] = self::$form_strings['error'];
						}
					}
				}

				add_action( 'awsm_application_form_notices', array( $this, 'awsm_form_submit_notices' ) );
			}
			// phpcs:enable
			return $awsm_response;
		}

		public function ajax_handle() {
			$response = $this->insert_application();
			wp_send_json( $response );
		}

		public function fallback_handle() {
			// Prevent the action from triggering multiple times.
			remove_action( 'before_awsm_job_details', array( $this, 'fallback_handle' ) );
			// Now, insert the application.
			$this->insert_application();
		}

		public function applicant_attachments_handler() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			if ( $_SERVER['REQUEST_METHOD'] === 'POST' && ! empty( $_POST['action'] ) && $_POST['action'] === 'awsm_applicant_attachments_handler' && isset( $_POST['r_id'] ) ) {
				$application_id = intval( $_POST['r_id'] );
				if ( get_post_type( $application_id ) === 'awsm_job_application' ) {
					$attachments = get_posts(
						array(
							'post_type'   => 'attachment',
							'post_parent' => $application_id,
							'numberposts' => -1,
							'fields'      => 'ids',
						)
					);
					if ( ! empty( $attachments ) ) {
						if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
							require_once ABSPATH . 'wp-admin/includes/image.php';
						}
						foreach ( $attachments as $attachment_id ) {
							$attachment_metadata = wp_get_attachment_metadata( $attachment_id, true );
							if ( empty( $attachment_metadata ) ) {
								$attached_file = get_attached_file( $attachment_id );
								if ( ! empty( $attached_file ) ) {
									$attach_data = wp_generate_attachment_metadata( $attachment_id, $attached_file );
									wp_update_attachment_metadata( $attachment_id, $attach_data );
								}
							}
						}
					}
				}
			}
			// phpcs:enable

			wp_send_json_success();
		}

		public function ajax_file_upload() {
			// phpcs:disable WordPress.Security.NonceVerification.Missing
			$is_valid  = false;
			$error_msg = '';
			$job_id    = intval( $_POST['job_id'] );
			$field_id  = sanitize_text_field( $_POST['field_id'] );

			$file = array();
			if ( get_post_type( $job_id ) !== 'awsm_job_openings' ) {
				$error_msg = __( 'Error occurred: Invalid Job.', 'wp-job-openings' );
			}
			$fb_options  = self::get_job_form_builder_options( $job_id );
			$form_fields = $fb_options['fields'];
			if ( empty( $error_msg ) && ! empty( $form_fields ) ) {
				foreach ( $form_fields as $form_field ) {
					if ( $form_field['name'] === $field_id ) {
						if ( isset( $form_field['misc_options'] ) && isset( $form_field['misc_options']['drag_and_drop'] ) && $form_field['misc_options']['drag_and_drop'] === 'enable' ) {
							if ( isset( $_FILES['file'] ) && $_FILES['file']['error'] === 0 ) {
								$max_files     = isset( $form_field['misc_options']['multi_upload'] ) ? absint( $form_field['misc_options']['multi_upload'] ) : 1;
								$max_file_size = isset( $form_field['misc_options']['max_file_size'] ) ? intval( $form_field['misc_options']['max_file_size'] ) : wp_max_upload_size();

								$accepted_files = sanitize_text_field( wp_unslash( $_POST['accepted_files'] ) );
								$accepted_files = json_decode( $accepted_files );
								if ( ! is_array( $accepted_files ) || count( $accepted_files ) >= $max_files ) {
									$error_msg = str_replace( '{{maxFiles}}', strval( $max_files ), _x( 'You are not allowed to upload more than the file limit: {{maxFiles}}.', 'file upload', 'pro-pack-for-wp-job-openings' ) );
								}

								if ( $_FILES['file']['size'] > $max_file_size ) {
									/* translators: %s: maximum upload size */
									$error_msg = sprintf( self::$form_strings['maximum_allowed_file_size'], size_format( $max_file_size ) );
								}

								if ( empty( $error_msg ) ) {
									$movefile = $this->handle_file_upload( $form_field['field_type'], $_FILES['file'], $form_field['misc_options'], true );
									if ( $movefile && ! isset( $movefile['error'] ) ) {
										$uploaded_file = $movefile['file'];
										$uploaded_file = ltrim( str_replace( self::get_temp_upload_directory(), '', $uploaded_file ), '/\\' );

										$file['file']  = $uploaded_file;
										$file['title'] = base64_encode( esc_attr( $_FILES['file']['name'] ) );
										$is_valid      = true;
									} else {
										$error_msg = $movefile['error'];
									}
								}
							}
						} else {
							$error_msg = __( 'Error occurred: Invalid Field.', 'pro-pack-for-wp-job-openings' );
						}
					}
				}
			}

			if ( $is_valid ) {
				wp_send_json_success( $file );
			} else {
				if ( empty( $error_msg ) ) {
					$error_msg = __( 'Error in upoading the file. Please refresh the page and retry.', 'pro-pack-for-wp-job-openings' );
				}
				wp_send_json( esc_html( $error_msg ), 403 );
			}
			// phpcs:enable
		}

		public function ajax_remove_uploaded_file() {
			$is_removed = false;
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$file_name = sanitize_text_field( $_POST['file_name'] );
			if ( ! empty( $file_name ) ) {
				$file_path = self::get_temp_uploaded_file_path( $file_name );
				if ( @is_file( $file_path ) && $file_name !== '.htaccess' ) {
					wp_delete_file( $file_path );
				}
				$is_removed = true;
			}

			if ( $is_removed ) {
				wp_send_json_success();
			} else {
				wp_send_json( esc_html__( 'Error in removing the file.', 'pro-pack-for-wp-job-openings' ), 400 );
			}
		}

		public function generic_form_shortcode( $atts ) {
			$pairs          = array(
				'id' => false,
			);
			$shortcode_atts = shortcode_atts( $pairs, $atts, 'awsm_application_form' );

			$job_id = intval( $shortcode_atts['id'] );
			if ( empty( $job_id ) ) {
				global $post;
				if ( isset( $post ) && get_post_type( $post->ID ) === 'awsm_job_openings' ) {
					$job_id = $post->ID;
				}
			}
			$is_filled = AWSM_Job_Openings_Pro_Pack::is_position_filled( $job_id );
			$form_data = self::get_custom_form_data( $job_id );
			if ( $is_filled || ( ! empty( $form_data ) && ( $form_data['id'] === 'disable' || $form_data['id'] === 'custom_form' || $form_data['id'] === 'custom_button' ) ) ) {
				if ( $is_filled ) {
					$job_detail_strings = AWSM_Job_Openings_Pro_Main::$job_detail_strings;
					$shortcode_content  = sprintf( '<div class="awsm-job-position-filled awsm-job-form-inner">%s</div>', esc_html( $job_detail_strings['position_filled'] ) );
				} else {
					$shortcode_content = $this->get_custom_form_content( $job_id );
				}
			} else {
				$shortcode_content = $this->get_generic_form( $shortcode_atts );
			}
			/**
			 * Filters the application form shortcode output content.
			 *
			 * @since 2.1.0
			 *
			 * @param string $shortcode_content Shortcode content.
			 * @param array $shortcode_atts Combined and filtered shortcode attribute list.
			 */
			return apply_filters( 'awsm_jobs_pro_application_form_shortcode_output_content', $shortcode_content, $shortcode_atts );
		}

		public function get_generic_form( $shortcode_atts ) {
			$content     = '';
			$single_form = false;
			$job_id      = intval( $shortcode_atts['id'] );

			if ( ! $job_id ) {
				$job_id      = get_the_ID();
				$single_form = true;
			}

			if ( $job_id && get_post_type( $job_id ) === 'awsm_job_openings' ) {
				if ( $this->is_recaptcha_set() && $this->get_recaptcha_type() === 'v2' && ! wp_script_is( 'g-recaptcha' ) ) {
					wp_enqueue_script( 'g-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), '2.0', false );
				}
				$form_attrs = array(
					'single_form' => $single_form,
					'job_id'      => $job_id,
				);
				ob_start();
				include AWSM_Job_Openings::get_template_path( 'form.php', 'single-job' );
				$content = ob_get_clean();
			}
			return $content;
		}

		public static function get_notification_options_by_form_id( $form_id, $type ) {
			if ( ! method_exists( 'AWSM_Job_Openings_Form', 'get_notification_options' ) ) {
				return false;
			}

			$notification_types = array( 'applicant', 'admin' );
			if ( ! in_array( $type, $notification_types, true ) ) {
				return false;
			}

			$defaults                    = AWSM_Job_Openings_Form::get_notification_options( $type );
			$options                     = get_post_meta( $form_id, "awsm_jobs_form_{$type}_notification", true );
			$defaults['acknowledgement'] = '';
			$defaults['enable']          = '';
			$parsed_args                 = wp_parse_args( $options, $defaults );
			return $parsed_args;
		}

		public function form_notifications_switch() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'awsm-admin-nonce' ) ) {
				wp_die();
			}
			if ( ! current_user_can( 'manage_awsm_jobs' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to manage options.', 'wp-job-openings' ) );
			}
			if ( isset( $_POST['option'], $_POST['option_value'], $_POST['form_id'] ) ) {
				$form_id = intval( $_POST['form_id'] );
				if ( get_post_type( $form_id ) === 'awsm_job_form' ) {
					$allowed_options = array( 'awsm_jobs_form_applicant_notification', 'awsm_jobs_form_admin_notification' );
					$option          = sanitize_text_field( $_POST['option'] );
					$option_value    = sanitize_text_field( $_POST['option_value'] );
					if ( ! empty( $option ) ) {
						if ( in_array( $option, $allowed_options, true ) ) {
							$current_options = get_post_meta( $form_id, $option, true );
							$current_options = is_array( $current_options ) ? $current_options : array();
							if ( $option === 'awsm_jobs_form_applicant_notification' ) {
								$current_options['acknowledgement'] = $option_value;
							} elseif ( $option === 'awsm_jobs_form_admin_notification' ) {
								$current_options['enable'] = $option_value;
							}
							update_post_meta( $form_id, $option, $current_options );
						} else {
							/* translators: %s: option name */
							wp_die( sprintf( esc_html__( "Error in updating option: '%s'", 'wp-job-openings' ), esc_html( $option ) ) );
						}
					}
					echo $option_value; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					wp_die( esc_html__( 'Invalid form ID.', 'pro-pack-for-wp-job-openings' ) );
				}
			}
			wp_die();
		}

		public function pro_mail_template_tags( $tags, $applicant_details ) {
			$form_id       = isset( $applicant_details['awsm_job_form_id'] ) ? $applicant_details['awsm_job_form_id'] : 'default';
			$custom_fields = isset( $applicant_details['custom_fields'] ) && is_array( $applicant_details['custom_fields'] ) ? $applicant_details['custom_fields'] : array();

			$fb_options = self::get_form_builder_options( $form_id );
			if ( ! empty( $fb_options ) ) {
				foreach ( $fb_options as $fb_option ) {
					$field_type = $fb_option['field_type'];
					if ( $fb_option['default_field'] !== true && $field_type !== 'photo' && $field_type !== 'file' && $field_type !== 'resume' ) {
						$field_name = $fb_option['name'];
						if ( isset( $custom_fields[ $field_name ] ) ) {
							$template_tag = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['template_tag'] ) ? $fb_option['misc_options']['template_tag'] : '';
							if ( ! empty( $template_tag ) ) {
								$key = sprintf( '{%s}', $template_tag );
								if ( isset( $custom_fields[ $field_name ]['type'] ) && $custom_fields[ $field_name ]['type'] === 'repeater' ) {
									$tag_value = '';
									foreach ( $custom_fields[ $field_name ]['value'] as $repeater_value ) {
										foreach ( $repeater_value as $item_value ) {
											$tag_value .= $item_value['label'] . ': ' . $item_value['value'] . '<br />';
										}
									}
									$tags[ $key ] = $tag_value;
								} else {
									$tags[ $key ] = $custom_fields[ $field_name ]['value'];
								}
							}
						}
					}
				}
			}
			return $tags;
		}

		public function admin_notification_mail_attachments( $attachments, $applicant_details ) {
			$attachment_ids = array();
			$form_id        = isset( $applicant_details['awsm_job_form_id'] ) ? $applicant_details['awsm_job_form_id'] : 'default';
			$custom_fields  = isset( $applicant_details['custom_fields'] ) && is_array( $applicant_details['custom_fields'] ) ? $applicant_details['custom_fields'] : array();

			$fb_options = self::get_form_builder_options( $form_id );
			if ( is_array( $fb_options ) ) {
				AWSM_Job_Openings_Pro_Pack::log(
					array(
						'fb_options'        => $fb_options,
						'applicant_details' => $applicant_details,
					)
				);

				foreach ( $fb_options as $fb_option ) {
					if ( $fb_option['field_type'] === 'resume' || $fb_option['field_type'] === 'photo' || $fb_option['field_type'] === 'file' ) {
						if ( isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['mail_attachment'] ) && $fb_option['misc_options']['mail_attachment'] === 'attach' ) {
							$field_name = $fb_option['name'];
							if ( $field_name === 'awsm_file' ) {
								if ( isset( $applicant_details['awsm_attachment_id'] ) && $applicant_details['awsm_attachment_id'] ) {
									$attachment_ids[] = array(
										'id'   => $applicant_details['awsm_attachment_id'],
										'type' => 'resume',
									);
								}
							} else {
								if ( array_key_exists( $field_name, $custom_fields ) ) {
									if ( is_array( $custom_fields[ $field_name ]['value'] ) && $fb_option['field_type'] === 'file' ) {
										$file_count = 1;
										foreach ( $custom_fields[ $field_name ]['value'] as $sub_value ) {
											$attachment_ids[] = array(
												'id'    => $sub_value,
												'label' => $fb_option['label'] . '-' . $file_count,
												'type'  => $custom_fields[ $field_name ]['type'],
											);
											$file_count++;
										}
									} else {
										$custom_attachment_ids = array(
											'id'   => $custom_fields[ $field_name ]['value'],
											'type' => $custom_fields[ $field_name ]['type'],
										);
										if ( $fb_option['field_type'] === 'file' ) {
											$custom_attachment_ids['label'] = $fb_option['label'];
										}
										$attachment_ids[] = $custom_attachment_ids;
									}
								}
							}
						}
					}
				}
			}

			if ( ! empty( $attachment_ids ) ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$attachment_file = get_attached_file( $attachment_id['id'] );
					if ( file_exists( $attachment_file ) ) {
						$path_info     = pathinfo( $attachment_file );
						$new_file_name = $applicant_details['application_id'];
						if ( isset( $attachment_id['label'] ) ) {
							$new_file_name .= '-' . sanitize_title( $applicant_details['awsm_applicant_name'] . ' ' . $attachment_id['label'] );
						} else {
							$new_file_name .= '-' . sanitize_title( $applicant_details['awsm_applicant_name'] ) . '-' . $attachment_id['type'];
						}
						$new_file = $path_info['dirname'] . '/' . $new_file_name . '.' . $path_info['extension'];
						if ( copy( $attachment_file, $new_file ) ) {
							$attachments[] = array(
								'file' => $new_file,
								'temp' => true,
							);
						}

						AWSM_Job_Openings_Pro_Pack::log(
							array(
								'attachment_file' => $attachment_file,
								'new_file'        => $new_file,
							)
						);
					}
				}
			}
			return $attachments;
		}

		public function job_forward_notification_email( $admin_headers, $applicant_details ) {
			$admin_cc     = get_option( 'awsm_jobs_admin_hr_notification' );
			$cc_addresses = get_post_meta( $applicant_details['awsm_job_id'], 'awsm_job_cc_email_addresses', true );
			if ( ! empty( $cc_addresses ) ) {
				$admin_headers['cc'] = ! empty( $admin_cc ) ? $admin_headers['cc'] . ',' . $cc_addresses : 'Cc: ' . $cc_addresses;
			}
			return $admin_headers;
		}
	}

	AWSM_Job_Openings_Pro_Form::init();

endif; // end of class check
