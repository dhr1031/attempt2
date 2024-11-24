<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'AWSM_Job_Openings_Settings' ) ) :

	class AWSM_Job_Openings_Pro_Settings extends AWSM_Job_Openings_Settings {
		private static $instance = null;

		public function __construct( $awsm_core ) {
			$this->cpath     = untrailingslashit( plugin_dir_path( __FILE__ ) );
			$this->awsm_core = $awsm_core;

			add_action( 'admin_init', array( $this, 'admin_init' ) );

			add_filter( 'awsm_jobs_settings_tab_menus', array( $this, 'pro_tab_menus' ) );
			add_action( 'awsm_jobs_settings_tab_section', array( $this, 'pro_tab_section' ) );
			add_filter( 'awsm_jobs_settings_subtabs', array( $this, 'pro_setting_subtabs' ), 10, 2 );
			add_action( 'after_awsm_settings_main_content', array( $this, 'subtab_section_content' ) );
			add_filter( 'awsm_jobs_form_settings_fields', array( $this, 'form_settings_fields' ) );
			add_filter( 'awsm_job_template_tags', array( $this, 'custom_template_tags' ) );
			add_filter( 'awsm_jobs_appearance_settings_fields', array( $this, 'appearance_settings_fields' ) );
			add_action( 'update_option_awsm_jobs_form_builder', array( $this, 'form_builder_update_handler' ), 10, 2 );
			add_action( 'wp_ajax_awsm_jobs_form_builder_actions', array( $this, 'ajax_form_builder_actions_handler' ) );
			add_filter( 'awsm_job_settings_submit_btn', array( $this, 'settings_submit_btn' ), 10, 2 );
			add_filter( 'awsm_jobs_notification_customizer_fields', array( $this, 'notification_customizer_fields' ), 10, 2 );
			add_filter( 'awsm_jobs_notification_html_template_main_styles', array( $this, 'notification_html_template_main_styles' ) );
			add_action( 'wp_ajax_awsm_job_delete_status', array( $this, 'delete_job_status_handler' ) );
			add_action( 'wp_ajax_awsm_job_status_action', array( $this, 'delete_or_move_job_status' ) );
		}

		public static function init( $awsm_core = null ) {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self( $awsm_core );
			}
			return self::$instance;
		}

		public function admin_init() {
			$this->register_pro_settings();
			$this->redirect_settings_page();
		}

		public function pro_tab_menus( $tab_menus ) {
			$tab_menus['shortcodes'] = esc_html__( 'Shortcodes', 'pro-pack-for-wp-job-openings' );
			$tab_menus['advanced']   = esc_html__( 'Advanced', 'pro-pack-for-wp-job-openings' );
			if ( function_exists( 'awsm_jobs_pro_fs' ) ) {
				if ( ! awsm_jobs_pro_fs()->can_use_premium_code() ) {
					$tab_menus['license'] = esc_html__( 'License', 'pro-pack-for-wp-job-openings' );
				}
			}
			return $tab_menus;
		}

		public function redirect_settings_page() {
			global $pagenow;
			if ( $pagenow === 'edit.php' && isset( $_GET['post_type'], $_GET['page'] ) && $_GET['post_type'] === 'awsm_job_openings' && $_GET['page'] === 'awsm-jobs-settings' ) {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'license' ) {
					if ( function_exists( 'awsm_jobs_pro_fs' ) ) {
						if ( awsm_jobs_pro_fs()->can_use_premium_code() ) {
							wp_safe_redirect( admin_url( 'edit.php?post_type=awsm_job_openings&page=awsm-jobs-settings-account' ) );
							exit;
						}
					}
				}
			}
			if ( isset( $_GET['page'] ) && $_GET['page'] === 'awsm-jobs-settings-pricing' ) {
				wp_redirect( 'https://wpjobopenings.com' );
				exit;
			}
		}

		public function pro_tab_section() {
			$current_tab = isset( $_GET['tab'] ) ? sanitize_title( $_GET['tab'] ) : 'general';

			if ( $current_tab === 'shortcodes' ) {
				include_once $this->cpath . '/templates/settings/shortcodes.php';
			} elseif ( $current_tab === 'advanced' ) {
				include_once $this->cpath . '/templates/settings/advanced.php';
			}
		}

		public function pro_setting_subtabs( $subtabs, $section ) {
			if ( $section === 'appearance' ) {
				$subtabs['change_strings'] = array(
					'target' => 'awsm-change-strings-appearance-options-container',
					'label'  => esc_html__( 'Change Strings', 'pro-pack-for-wp-job-openings' ),
				);
			} elseif ( $section === 'notification' ) {
				if ( ! isset( $subtabs['general'] ) ) {
					$subtabs['general'] = array(
						'target' => 'awsm-job-notification-options-container',
						'label'  => esc_html__( 'General', 'pro-pack-for-wp-job-openings' ),
					);
				}
				$subtabs['templates'] = array(
					'label' => esc_html__( 'Templates', 'pro-pack-for-wp-job-openings' ),
				);
			} elseif ( $section === 'form' ) {
				$subtabs['builder'] = array(
					'label' => esc_html__( 'Form Builder', 'pro-pack-for-wp-job-openings' ),
				);
			} elseif ( $section === 'shortcodes' ) {
				$subtabs['general']    = array(
					'label' => esc_html__( 'General', 'pro-pack-for-wp-job-openings' ),
				);
				$subtabs['form']       = array(
					'label' => esc_html__( 'Form', 'pro-pack-for-wp-job-openings' ),
				);
				$subtabs['job_specs']  = array(
					'label' => esc_html__( 'Job Specs', 'pro-pack-for-wp-job-openings' ),
				);
				$subtabs['jobs_stats'] = array(
					'label' => esc_html__( 'Jobs Stats', 'pro-pack-for-wp-job-openings' ),
				);
			} elseif ( $section === 'specifications' ) {
				if ( ! isset( $subtabs['general'] ) ) {
					$subtabs['manage_spec'] = array(
						'target' => 'awsm-job-specifications-options-container',
						'label'  => esc_html__( 'Manage Specifications', 'pro-pack-for-wp-job-openings' ),
					);
				}
				$subtabs['admin_filters'] = array(
					'target' => 'awsm-job-specifications-admin-filters-container',
					'label'  => esc_html__( 'Admin Filters', 'pro-pack-for-wp-job-openings' ),
				);
			}
			return $subtabs;
		}

		public function subtab_section_content( $group ) {
			if ( $group === 'appearance' ) {
				include_once $this->cpath . '/templates/settings/change-strings.php';
			} elseif ( $group === 'specifications' ) {
				include_once $this->cpath . '/templates/settings/admin-filters.php';
			} elseif ( $group === 'form' ) {
				include_once $this->cpath . '/templates/settings/form-builder.php';
			} elseif ( $group === 'notification' ) {
				include_once $this->cpath . '/templates/settings/mail-templates.php';
			}
		}

		private function settings() {
			$settings = array(
				'appearance'     => array(
					array(
						/** @since 3.0.0 */
						'option_name' => 'awsm_jobs_filled_jobs_listings',
					),
					array(
						/** @since 2.0.0 */
						'option_name' => 'awsm_jobs_back_to_listings',
					),
					array(
						/** @since 2.3.0 */
						'option_name' => 'awsm_jobs_listing_featured_image_size',
					),
					array(
						/** @since 2.4.0 */
						'option_name' => 'awsm_jobs_customize_job_listing_strings',
						'callback'    => array( $this, 'sanitize_job_listing_strings_fields' ),
					),
					array(
						/** @since 3.0.0 */
						'option_name' => 'awsm_jobs_customize_job_detail_strings',
						'callback'    => array( $this, 'sanitize_array_empty_fields' ),
					),
					array(
						/** @since 2.4.0 */
						'option_name' => 'awsm_jobs_customize_form_strings',
						'callback'    => array( $this, 'sanitize_array_empty_fields' ),
					),
					array(
						/** @since 2.4.0 */
						'option_name' => 'awsm_jobs_customize_form_validation_notices',
						'callback'    => array( $this, 'sanitize_array_empty_fields' ),
					),
				),
				'specifications' => array(
					array(
						/** @since 2.3.0 */
						'option_name' => 'awsm_jobs_enable_admin_filters',
					),
					array(
						/** @since 2.3.0 */
						'option_name' => 'awsm_jobs_available_filters',
						'callback'    => array( $this, 'sanitize_array_fields' ),
					),
					array(
						/** @since 2.3.0 */
						'option_name' => 'awsm_applications_enable_admin_filters',
					),
					array(
						/** @since 2.3.0 */
						'option_name' => 'awsm_applications_available_filters',
						'callback'    => array( $this, 'sanitize_array_fields' ),
					),
				),
				'form'           => array(
					array(
						'option_name' => 'awsm_jobs_custom_application_form',
						'callback'    => array( $this, 'custom_form' ),
					),
					array(
						/** @since 3.1.0 */
						'option_name' => 'awsm_jobs_restrict_application_form',
					),
					array(
						/** @since 2.1.0 */
						'option_name' => 'awsm_jobs_recaptcha_type',
					),
					array(
						/** @since 2.4.0 */
						'option_name' => 'awsm_jobs_form_confirmation_type',
						'callback'    => array( $this, 'form_confirmation_type_handler' ),
					),
				),
				'notification'   => array(
					array(
						'option_name' => 'awsm_jobs_pro_mail_templates',
						'callback'    => array( $this, 'email_template_handler' ),
					),
				),
				'advanced'       => array(
					array(
						'option_name' => 'awsm_jobs_application_status',
						'callback'    => array( $this, 'status_handler' ),
					),
				),
			);
			return $settings;
		}

		public function register_pro_settings() {
			$settings = $this->settings();
			foreach ( $settings as $group => $settings_args ) {
				foreach ( $settings_args as $setting_args ) {
					register_setting( 'awsm-jobs-' . $group . '-settings', $setting_args['option_name'], isset( $setting_args['callback'] ) ? $setting_args['callback'] : 'sanitize_text_field' );
				}
			}
		}

		private static function default_settings() {
			$admin_filters = array( 'job-category', 'job-type', 'job-location' );
			$options       = array(
				'awsm_jobs_pro_version'                  => AWSM_JOBS_PRO_PLUGIN_VERSION,
				'awsm_jobs_enable_admin_filters'         => 'enable',
				'awsm_jobs_available_filters'            => $admin_filters,
				'awsm_applications_enable_admin_filters' => 'enable',
				'awsm_applications_available_filters'    => $admin_filters,
				'awsm_jobs_restrict_application_form'    => 'restrict',
			);
			foreach ( $options as $option => $value ) {
				if ( ! get_option( $option ) ) {
					update_option( $option, $value );
				}
			}
		}

		public static function register_pro_defaults() {
			if ( intval( get_option( 'awsm_register_pro_default_settings' ) ) === 1 ) {
				return;
			}
			self::default_settings();
			update_option( 'awsm_register_pro_default_settings', 1 );
		}

		private function create_temp_upload_directory() {
			$upload_dir = AWSM_Job_Openings_Pro_Form::get_temp_upload_directory();
			$file_name  = trailingslashit( $upload_dir ) . '.htaccess';
			if ( wp_mkdir_p( $upload_dir ) && ! file_exists( $file_name ) ) {
				$file_name    = trailingslashit( $upload_dir ) . '.htaccess';
				$file_content = 'deny from all';
				$handle       = @fopen( $file_name, 'w' );
				if ( $handle ) {
					fwrite( $handle, $file_content );
					fclose( $handle );
				}
			}
		}

		public static function form_builder_default_options() {
			if ( ! class_exists( 'AWSM_Job_Openings_Pro_Form' ) ) {
				if ( ! class_exists( 'AWSM_Job_Openings_Form' ) ) {
					require_once AWSM_JOBS_PLUGIN_DIR . '/inc/class-awsm-job-openings-form.php';
				}
				require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-main.php';
				require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-form.php';
			}
			return AWSM_Job_Openings_Pro_Form::default_form_fields_options();
		}

		public static function form_builder_field_types() {
			$field_types = array(
				'text'     => esc_html__( 'Text', 'pro-pack-for-wp-job-openings' ),
				'email'    => esc_html__( 'Email', 'pro-pack-for-wp-job-openings' ),
				'number'   => esc_html__( 'Number', 'pro-pack-for-wp-job-openings' ),
				'tel'      => esc_html__( 'Phone', 'pro-pack-for-wp-job-openings' ),
				'date'     => esc_html__( 'Date', 'pro-pack-for-wp-job-openings' ),
				'textarea' => esc_html__( 'Textarea', 'pro-pack-for-wp-job-openings' ),
				'select'   => esc_html__( 'Dropdown', 'pro-pack-for-wp-job-openings' ),
				'radio'    => esc_html__( 'Radio', 'pro-pack-for-wp-job-openings' ),
				'checkbox' => esc_html__( 'Checkbox', 'pro-pack-for-wp-job-openings' ),
				'resume'   => esc_html__( 'Resume', 'pro-pack-for-wp-job-openings' ),
				'photo'    => esc_html__( 'Photo', 'pro-pack-for-wp-job-openings' ),
				'file'     => esc_html__( 'File', 'pro-pack-for-wp-job-openings' ),
				'section'  => esc_html__( 'Section', 'pro-pack-for-wp-job-openings' ),
			);

			/**
			 * Filters the form builder field types.
			 *
			 * @since 3.1.0
			 *
			 * @param array $field_types Form field types.
			 */
			return apply_filters( 'awsm_jobs_fb_field_types', $field_types );
		}

		public function generate_unique_name( $field_type, $options ) {
			$new_number = 1;
			$prefix     = "awsm_{$field_type}_";
			$names      = array();
			foreach ( $options as $option ) {
				if ( isset( $option['name'] ) && ( ! isset( $option['default_field'] ) || ! $option['default_field'] ) ) {
					if ( strpos( $option['name'], $prefix ) !== false ) {
						$names[] = $option['name'];
					}
				}
			}
			if ( ! empty( $names ) ) {
				natsort( $names );
				$last_name = end( $names );
				$number    = str_replace( $prefix, '', $last_name );
				if ( intval( $number ) ) {
					$new_number = (int) $number + 1;
				}
			}
			return sanitize_text_field( $prefix . $new_number );
		}

		public function form_settings_fields( $settings_fields ) {
			if ( isset( $settings_fields['general'] ) ) {
				$general_group = $settings_fields['general'];
				$offset        = 0;
				foreach ( $general_group as $field_index => $general_fields ) {
					if ( isset( $general_fields['type'] ) && $general_fields['type'] === 'title' && $general_fields['id'] === 'awsm-form-options-title' ) {
						$offset = $field_index + 1;
					}
					if ( isset( $general_fields['name'] ) && $general_fields['name'] === 'awsm_jobs_admin_upload_file_ext' ) {
						unset( $general_group[ $field_index ] );
					}
				}

				$post_id = 'option';
				ob_start();
				include $this->cpath . '/templates/generic/custom-form.php';
				$form_content = ob_get_clean();

				ob_start();
				include $this->cpath . '/templates/settings/form-submit-confirmation.php';
				$form_submit = ob_get_clean();

				array_splice(
					$general_group,
					$offset,
					0,
					array(
						array(
							'id'    => 'awsm-pro-application-form',
							'label' => __( 'Default form for new openings', 'pro-pack-for-wp-job-openings' ),
							'type'  => 'raw',
							'value' => $form_content,
						),
						array(
							'name'        => 'awsm_jobs_restrict_application_form',
							'label'       => __( 'Restrict Duplicate Applications', 'pro-pack-for-wp-job-openings' ),
							'type'        => 'checkbox',
							'choices'     => array(
								array(
									'value' => 'restrict',
									'text'  => __( 'Restrict applicants from submitting duplicate applications for a job listing', 'pro-pack-for-wp-job-openings' ),
								),
							),
							'description' => __( 'If checked, the plugin will validate each application by email and phone number (if available) and restrict duplicate submissions.', 'pro-pack-for-wp-job-openings' ),
						),
						array(
							'id'    => 'awsm-pro-form-submit-confirmation-type',
							'label' => __( 'Form submit confirmation', 'pro-pack-for-wp-job-openings' ),
							'type'  => 'raw',
							'value' => $form_submit,
						),
					)
				);
				$settings_fields['general'] = $general_group;
			}

			if ( isset( $settings_fields['recaptcha'] ) && defined( 'AWSM_JOBS_PLUGIN_VERSION' ) && version_compare( AWSM_JOBS_PLUGIN_VERSION, '2.2.0', '>=' ) ) {
				$recaptcha_group = $settings_fields['recaptcha'];
				$offset          = 0;
				foreach ( $recaptcha_group as $index => $recaptcha_fields ) {
					if ( isset( $recaptcha_fields['type'] ) ) {
						if ( $recaptcha_fields['type'] === 'title' && $recaptcha_fields['id'] === 'awsm-form-recaptcha-options-title' ) {
							$recaptcha_group[ $index ]['label'] = __( 'reCAPTCHA options', 'pro-pack-for-wp-job-openings' );
						}
						if ( $recaptcha_fields['type'] === 'checkbox' && $recaptcha_fields['name'] === 'awsm_jobs_enable_recaptcha' ) {
							$offset = $index + 1;
							$recaptcha_group[ $index ]['choices'][0]['text']  = __( 'Enable reCAPTCHA on the form', 'pro-pack-for-wp-job-openings' );
							$recaptcha_group[ $index ]['help_button']['text'] = __( 'Get reCAPTCHA keys', 'pro-pack-for-wp-job-openings' );
						}
					}
				}

				$awsm_jobs_form = AWSM_Job_Openings_Pro_Form::init();
				$recaptcha_type = $awsm_jobs_form->get_recaptcha_type();

				array_splice(
					$recaptcha_group,
					$offset,
					0,
					array(
						array(
							'name'        => 'awsm_jobs_recaptcha_type',
							'label'       => __( 'reCAPTCHA type', 'pro-pack-for-wp-job-openings' ),
							'type'        => 'radio',
							'choices'     => array(
								array(
									'value' => 'v2',
									'text'  => __( 'reCAPTCHA v2', 'pro-pack-for-wp-job-openings' ),
								),
								array(
									'value' => 'v3',
									'text'  => __( 'reCAPTCHA v3', 'pro-pack-for-wp-job-openings' ),
								),
							),
							'value'       => $recaptcha_type,
							'description' => __( 'IMPORTANT NOTE: reCAPTCHA v2 and v3 Site key and Secret key are different. Using invalid keys will cause a reCAPTCHA error leading to issues with the job application form. Please verify the keys before updating the settings.', 'pro-pack-for-wp-job-openings' ),
						),
					)
				);

				$settings_fields['recaptcha'] = $recaptcha_group;
			}

			return $settings_fields;
		}

		public function custom_form( $options ) {
			$options = AWSM_Job_Openings_Pro_Main::sanitize_custom_form_data( $options );
			return $options;
		}

		public function form_builder_handler( $fb_options, $form_id = 0 ) {
			$old_options = get_option( 'awsm_jobs_form_builder' );
			if ( ! empty( $form_id ) ) {
				$old_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form_id );
			}

			$is_error            = false;
			$count_resume_fields = $count_photo_fields = 0; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			if ( ! empty( $fb_options ) ) {
				$photo_file_types = array( 'jpg', 'jpeg', 'jpe', 'png', 'gif' );
				foreach ( $fb_options as $key => $fb_option ) {
					$field_type                          = sanitize_text_field( $fb_option['field_type'] );
					$default_field                       = isset( $fb_option['default_field'] ) && ( strval( $fb_option['default_field'] ) === 'default' || $fb_option['default_field'] === true ) ? true : false;
					$default_field                       = $field_type === 'resume' ? true : $default_field;
					$fb_options[ $key ]['default_field'] = $default_field;
					if ( $field_type === 'resume' || $field_type === 'photo' ) {
						if ( $field_type === 'resume' ) {
							$count_resume_fields ++;
						} else {
							$count_photo_fields ++;
						}
						if ( $count_resume_fields > 1 || $count_photo_fields > 1 ) {
							$is_error = true;
							break;
						}
					}

					$field_name = '';
					if ( ! isset( $fb_option['name'] ) ) {
						if ( ! $default_field ) {
							if ( $field_type === 'photo' ) {
								$field_name = 'awsm_applicant_photo';
							} else {
								$field_name = $this->generate_unique_name( $field_type, $fb_options );
							}
						}
					} else {
						$field_name = sanitize_text_field( $fb_option['name'] );
					}
					if ( $field_type === 'resume' ) {
						$field_name = 'awsm_file';
					}

					$fb_options[ $key ]['name'] = $field_name;
					$label                      = isset( $fb_option['label'] ) ? sanitize_text_field( $fb_option['label'] ) : '';
					if ( empty( $label ) && $field_type !== 'section' ) {
						$is_error = true;
						break;
					}
					$fb_options[ $key ]['label']      = $label;
					$fb_options[ $key ]['field_type'] = $field_type;
					$field_options                    = isset( $fb_option['field_options'] ) ? sanitize_text_field( $fb_option['field_options'] ) : '';
					if ( $field_type === 'select' || $field_type === 'checkbox' || $field_type === 'radio' ) {
						if ( empty( $field_options ) ) {
							$is_error = true;
							break;
						}
					}
					$fb_options[ $key ]['field_options'] = $field_options;
					$fb_options[ $key ]['required']      = isset( $fb_option['required'] ) ? sanitize_text_field( $fb_option['required'] ) : '';
					$fb_options[ $key ]['active']        = 'active';
					// handle miscellaneous options for all fields.
					$misc_options = isset( $fb_option['misc_options'] ) && is_array( $fb_option['misc_options'] ) ? $fb_option['misc_options'] : array();
					if ( ! empty( $misc_options ) ) {
						$default_tmpl_tags = array( 'applicant', 'application-id', 'applicant-email', 'applicant-phone', 'applicant-resume', 'applicant-cover', 'job-title', 'job-id', 'job-expiry', 'admin-email', 'hr-email', 'company' );
						foreach ( $misc_options  as $misc_option_key => $misc_option ) {
							if ( $misc_option_key === 'section_description' ) {
								$misc_options[ $misc_option_key ] = wp_kses( $misc_option, 'post' );
							} elseif ( $misc_option_key === 'multi_upload' || $misc_option_key === 'max_file_size' ) {
								$active_option_value = absint( $misc_option );
								if ( $active_option_value < 1 ) {
									$active_option_value = 1;
								}
								if ( $misc_option_key === 'max_file_size' ) {
									$active_option_value = min( wp_max_upload_size(), self::get_valid_file_size( $active_option_value, 'M', true ) );
								}
								$misc_options[ $misc_option_key ] = $active_option_value;
							} else {
								$misc_options[ $misc_option_key ] = sanitize_text_field( $misc_option );
							}
							if ( $misc_option_key === 'template_tag' ) {
								if ( ! empty( $misc_option ) ) {
									if ( in_array( $misc_option, $default_tmpl_tags ) ) {
										unset( $misc_options[ $misc_option_key ] );
									} else {
										if ( ! preg_match( '/^([a-z0-9]+(-|_))*[a-z0-9]+$/', $misc_option ) ) {
											unset( $misc_options[ $misc_option_key ] );
										} else {
											$is_valid_tag = true;
											if ( is_array( $old_options ) ) {
												foreach ( $old_options as $old_option ) {
													$old_tag = isset( $old_option['misc_options'] ) && isset( $old_option['misc_options']['template_tag'] ) ? $old_option['misc_options']['template_tag'] : '';
													if ( $field_name !== $old_option['name'] && $old_tag === $misc_option ) {
														$is_valid_tag = false;
														break;
													}
												}
											}
											$awsm_filters = get_option( 'awsm_jobs_filter' );
											if ( ! empty( $awsm_filters ) ) {
												$spec_keys = wp_list_pluck( $awsm_filters, 'taxonomy' );
												if ( in_array( $misc_option, $spec_keys, true ) ) {
													$is_valid_tag = false;
												}
											}
											if ( ! $is_valid_tag ) {
												unset( $misc_options[ $misc_option_key ] );
											}
										}
									}
								}
							} elseif ( $misc_option_key === 'file_types' ) {
								if ( $field_type === 'photo' ) {
									if ( empty( $misc_option ) ) {
										$misc_options['file_types'] = 'jpg,png';
									} else {
										$file_types = AWSM_Job_Openings_Pro_Form::get_valid_file_types( $misc_option );
										foreach ( $file_types as $file_type ) {
											if ( ! in_array( $file_type, $photo_file_types, true ) ) {
												$misc_options['file_types'] = 'jpg,png';
												break;
											}
										}
									}
								} elseif ( $field_type === 'resume' ) {
									if ( empty( $misc_option ) ) {
										$misc_options['file_types'] = AWSM_Job_Openings_Pro_Form::get_resume_default_allowed_types();
									}
								}
							} elseif ( $misc_option_key === 'section_title' ) {
								if ( empty( $misc_options[ $misc_option_key ] ) ) {
									$is_error = true;
									break;
								} else {
									$fb_options[ $key ]['label'] = $misc_options['section_title'];
								}
							}
						}
					}
					$fb_options[ $key ]['misc_options'] = $misc_options;

					// handle super fields.
					if ( $field_name === 'awsm_applicant_name' || $field_name === 'awsm_applicant_email' ) {
						$fb_options[ $key ]['super_field']   = true;
						$fb_options[ $key ]['default_field'] = true;
						$fb_options[ $key ]['required']      = 'required';
					} else {
						$fb_options[ $key ]['super_field'] = false;
					}
				}
				$fb_options = array_values( $fb_options );
			}
			if ( $is_error === true ) {
				$fb_options = $old_options;
			}
			return $fb_options;
		}

		public function form_builder_update_handler( $old_value, $fb_options ) {
			if ( ! empty( $fb_options ) && is_array( $fb_options ) ) {
				foreach ( $fb_options as $fb_option ) {
					if ( isset( $fb_option['misc_options']['drag_and_drop'] ) && $fb_option['misc_options']['drag_and_drop'] === 'enable' ) {
						$this->create_temp_upload_directory();
					}
				}
			}
		}

		public function form_builder_other_options_handler( $options ) {
			if ( ! empty( $options ) ) {
				$options['form_title']       = isset( $options['form_title'] ) ? sanitize_text_field( $options['form_title'] ) : '';
				$options['form_description'] = isset( $options['form_description'] ) ? stripslashes( wp_kses( $options['form_description'], 'post' ) ) : '';
				$options['btn_text']         = isset( $options['btn_text'] ) ? sanitize_text_field( $options['btn_text'] ) : '';
				if ( empty( $options['btn_text'] ) ) {
					$options['btn_text'] = esc_html__( 'Submit', 'wp-job-openings' );
				}
			}
			return $options;
		}

		public function ajax_form_builder_actions_handler() {
			$response        = array(
				'error' => array(),
			);
			$generic_err_msg = sprintf( esc_html__( 'Error in handling form builder actions!', 'pro-pack-for-wp-job-openings' ) );
			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) ) {
				$response['error'][] = $generic_err_msg;
			}
			$form_id = intval( $_POST['awsm_form_id'] );
			if ( get_post_type( $form_id ) !== 'awsm_job_form' ) {
				$response['error'][] = esc_html__( 'Invalid Form ID', 'pro-pack-for-wp-job-openings' );
			}
			if ( ! current_user_can( 'edit_post', $form_id ) ) {
				$response['error'][] = esc_html__( 'You do not have sufficient permissions to edit form!', 'pro-pack-for-wp-job-openings' );
			}
			$fb_action  = sanitize_text_field( $_POST['awsm_fb_action'] );
			$fb_actions = array( 'duplicate', 'delete' );
			if ( ! in_array( $fb_action, $fb_actions, true ) ) {
				$response['error'][] = $generic_err_msg;
			}
			if ( count( $response['error'] ) === 0 ) {
				$default_form_id = AWSM_Job_Openings_Pro_Form::get_default_form_id();
				if ( $fb_action === 'duplicate' ) {
					$form = get_post( $form_id );
					if ( ! empty( $form ) ) {
						$form_title       = $form->post_title;
						$form_title_parts = explode( '#', $form_title );
						if ( is_numeric( end( $form_title_parts ) ) ) {
							array_pop( $form_title_parts );
						}
						$form_title  = implode( '', $form_title_parts );
						$new_form_id = wp_insert_post(
							array(
								'post_title'     => $form_title,
								'post_content'   => '',
								'post_status'    => $form->post_status,
								'comment_status' => 'closed',
								'post_type'      => 'awsm_job_form',
							)
						);
						if ( ! empty( $new_form_id ) && ! is_wp_error( $new_form_id ) ) {
							$new_form_slug    = get_post_field( 'post_name', $new_form_id );
							$form_slug_parts  = explode( '-', $new_form_slug );
							$form_slug_suffix = end( $form_slug_parts );
							$prefix           = esc_html__( 'Copy of', 'pro-pack-for-wp-job-openings' );
							if ( strpos( $form_title, $prefix ) === false ) {
								$new_form_title = $prefix . ' ' . $form_title;
							} else {
								$new_form_title = $form_title;
							}
							if ( is_numeric( $form_slug_suffix ) ) {
								$new_form_title = $form_title . '#' . $form_slug_suffix;
							}
							wp_update_post(
								array(
									'ID'         => $new_form_id,
									'post_title' => $new_form_title,
								)
							);
							$fields_count = 0;
							$edit_link    = get_edit_post_link( $new_form_id, 'raw' );
							$meta_keys    = array( 'awsm_jobs_form_builder', 'awsm_jobs_form_builder_other_options', 'awsm_jobs_form_applicant_notification', 'awsm_jobs_form_admin_notification' );
							foreach ( $meta_keys as $meta_key ) {
								$meta_data = get_post_meta( $form_id, $meta_key, true );
								if ( $meta_key === 'awsm_jobs_form_builder' ) {
									$fields_count = count( $meta_data );
								}
								add_post_meta( $new_form_id, $meta_key, $meta_data );
							}

							$response['duplicate'] = true;
							$response['data']      = array(
								'id'           => $new_form_id,
								'title'        => esc_html( $new_form_title ),
								'status'       => $form->post_status,
								'status_text'  => '',
								'edit_link'    => esc_url_raw( $edit_link ),
								'fields_count' => $fields_count,
							);
							if ( $form->post_status !== 'publish' ) {
								$post_status_data = get_post_status_object( $form->post_status );
								if ( ! empty( $post_status_data ) ) {
									$response['data']['status_text'] = esc_html( $post_status_data->label );
								}
							}
						}
					}
				} elseif ( $fb_action === 'delete' ) {
					if ( $form_id !== $default_form_id ) {
						$delete = wp_trash_post( $form_id );
						if ( $delete ) {
							$response['delete'] = true;
						}
					}
				}
				update_option( 'awsm_current_form_subtab', 'awsm-builder-form-nav-subtab' );
			}
			wp_send_json( $response );
		}

		public function settings_submit_btn( $btn, $tab ) {
			if ( $tab === 'form' ) {
				$form_subtab = get_option( 'awsm_current_form_subtab' );
				if ( $form_subtab === 'awsm-builder-form-nav-subtab' ) {
					$btn = get_submit_button( '', 'primary large awsm-hidden' );
				}
			}
			return $btn;
		}

		public function form_confirmation_type_handler( $options ) {
			if ( ! empty( $options ) ) {
				$options                 = AWSM_Job_Openings_Pro_Form::get_form_confirmation( $options );
				$confirmation_type       = sanitize_text_field( $options['type'] );
				$options['type']         = $confirmation_type;
				$options['message']      = wp_kses( $options['message'], 'post' );
				$options['page']         = intval( $options['page'] );
				$options['redirect_url'] = esc_url_raw( $options['redirect_url'] );
				if ( empty( $options[ $confirmation_type ] ) ) {
					$options['type'] = 'message';
					unset( $options[ $confirmation_type ] );
				}
			}
			return $options;
		}

		public function email_template_handler( $et_options ) {
			if ( ! empty( $et_options ) ) {
				$options_count = count( $et_options );
				foreach ( $et_options as $index => $et_option ) {
					$template_name = isset( $et_option['name'] ) ? sanitize_text_field( $et_option['name'] ) : '';
					if ( empty( $template_name ) ) {
						unset( $et_options[ $index ] );
						if ( $options_count > 1 ) {
							add_settings_error( 'awsm_jobs_pro_mail_templates', 'awsm-jobs-mail-templates-settings', esc_html__( 'Template Name cannot be empty!', 'pro-pack-for-wp-job-openings' ) );
						}
						continue;
					}
					$template_key = isset( $et_option['key'] ) ? sanitize_text_field( $et_option['key'] ) : str_replace( ' ', '-', strtolower( $template_name ) );
					if ( ! isset( $et_option['key'] ) ) {
						$template_keys = wp_list_pluck( $et_options, 'key' );
						if ( in_array( $template_key, $template_keys, true ) ) {
							unset( $et_options[ $index ] );
							/* translators: %s: user supplied template name */
							add_settings_error( 'awsm_jobs_pro_mail_templates', 'awsm-jobs-mail-templates-settings', sprintf( esc_html__( 'Template Name: "%s" already exists!', 'pro-pack-for-wp-job-openings' ), $template_name ) );
							continue;
						}
					}
					$et_options[ $index ]['key']     = $template_key;
					$et_options[ $index ]['name']    = $template_name;
					$et_options[ $index ]['subject'] = isset( $et_option['subject'] ) ? sanitize_text_field( $et_option['subject'] ) : '';
					$et_options[ $index ]['content'] = isset( $et_option['content'] ) ? wp_kses_post( $et_option['content'] ) : '';
				}
				$et_options = array_values( $et_options );
			}
			return $et_options;
		}

		public function status_handler( $status_options ) {
			if ( ! empty( $status_options ) ) {
				// Handle status order.
				$new_status_options = array();
				foreach ( $status_options as $index => $status_option ) {
					if ( is_numeric( $index ) ) {
						$default_options = AWSM_Job_Openings_Pro_Main::get_application_status( true );
						unset( $default_options['publish'], $default_options['trash'] );
						$new_key = sanitize_title( $status_options[ $index ]['key'] );
						if ( $new_key > 20 ) {
							$new_key = substr( $new_key, 0, 20 );
						}
						$new_status = $new_key;
						foreach ( $default_options as $status => $default_option ) {
							$status_key = sanitize_title( $default_option['label'] );
							if ( $status_key === $new_key ) {
								$new_status = $status;
								break;
							}
						}
						$new_status_options[ $new_status ] = $status_options[ $index ];
					} else {
						$new_status_options[ $index ] = $status_options[ $index ];
					}
				}
				$status_options = $new_status_options;

				// Sanitize and handle status attributes.
				foreach ( $status_options as $index => $status_option ) {
					$status_options[ $index ] = $this->sanitize_array_fields( $status_option );
					$status_label             = $status_options[ $index ]['label'];
					// phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment,WordPress.WP.I18n.NonSingularStringLiteralSingle,WordPress.WP.I18n.NonSingularStringLiteralPlural
					$status_options[ $index ]['label_count'] = _n_noop( $status_label . ' <span class="count">(%s)</span>', $status_label . ' <span class="count">(%s)</span>' );
				}
			}
			if ( empty( $status_options ) || ! isset( $status_options['publish'] ) || ! isset( $status_options['trash'] ) ) {
				$status_options = AWSM_Job_Openings_Pro_Main::get_application_status( true );
			}
			return $status_options;
		}

		public function sanitize_array_fields( $input ) {
			if ( is_array( $input ) ) {
				$input = array_map( 'sanitize_text_field', $input );
			}
			return $input;
		}

		public function sanitize_job_listing_strings_fields( $input ) {
			return $this->sanitize_array_empty_fields( $input, array( 'filter_prefix', 'filter_suffix', 'search_placeholder' ) );
		}

		public function sanitize_array_empty_fields( $input, $exceptions = array() ) {
			if ( is_array( $input ) ) {
				$input = array_map( 'sanitize_text_field', $input );
				foreach ( $input as $index => $field_val ) {
					$current_val = sanitize_text_field( $field_val );
					if ( empty( $current_val ) ) {
						if ( ! in_array( $index, $exceptions, true ) ) {
							unset( $input[ $index ] );
						} else {
							$input[ $index ] = false;
						}
					} else {
						$input[ $index ] = $current_val;
					}
				}
			}
			return $input;
		}

		public function fb_field_options_template( $index, $fb_option = array() ) {
			?>
			<p>
				<label for="awsm-jobs-form-builder-type-options-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Field Options:', 'pro-pack-for-wp-job-openings' ); ?></label>
				<textarea class="awsm-job-fb-options-control" id="awsm-jobs-form-builder-type-options-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][field_options]" cols="25" rows="2" placeholder="<?php echo esc_attr__( 'Please enter options separated by commas', 'pro-pack-for-wp-job-openings' ); ?>" required><?php echo isset( $fb_option['field_options'] ) ? esc_textarea( $fb_option['field_options'] ) : ''; ?></textarea>
			</p>
			<?php
		}

		public static function get_valid_file_size( $size, $unit, $to_bytes = false ) {
			if ( ! $to_bytes ) {
				$factor = KB_IN_BYTES;
				$unit   = strtolower( $unit );
				if ( $unit === 'm' ) {
					$factor = MB_IN_BYTES;
				} elseif ( $unit === 'g' ) {
					$factor = GB_IN_BYTES;
				}
				$size = intval( $size / $factor );
			} else {
				$size = wp_convert_hr_to_bytes( $size . $unit );
			}
			return $size;
		}

		public function fb_file_type_options_template( $index, $fb_option = array() ) {
			$is_js_template = $index === '{{data.index}}' ? true : false;
			$attach_to_mail = checked( isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['mail_attachment'] ) ? $fb_option['misc_options']['mail_attachment'] : '', 'attach', false );
			$drag_and_drop  = checked( isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['drag_and_drop'] ) ? $fb_option['misc_options']['drag_and_drop'] : '', 'enable', false );
			?>
			<p>
				<input type="checkbox" id="awsm-jobs-form-builder-mail-attachment-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][mail_attachment]" value="attach" <?php echo $attach_to_mail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
				<label for="awsm-jobs-form-builder-mail-attachment-<?php echo esc_attr( $index ); ?>">
					<?php esc_html_e( 'Attach the file with email notifications', 'pro-pack-for-wp-job-openings' ); ?>
				</label>
			</p>
			<?php
			if ( $is_js_template || isset( $fb_option['field_type'] ) ) :
				$file_types = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['file_types'] ) ? $fb_option['misc_options']['file_types'] : '';

				$description = esc_html__( 'The comma-separated list of supported file types.', 'pro-pack-for-wp-job-openings' );

				// Handle resume supported file types.
				if ( $is_js_template || isset( $fb_option['field_type'] ) && $fb_option['field_type'] === 'resume' ) {
					$selected_file_types = AWSM_Job_Openings_Pro_Form::get_resume_default_allowed_types();
					if ( $is_js_template ) {
						$file_types = "<# if( data.fieldType && data.fieldType === 'resume' ) { #>" . esc_attr( $selected_file_types ) . '<# } #>';
					} else {
						if ( empty( $file_types ) ) {
							$file_types = $selected_file_types;
						}
					}
				}

				// Handle photo supported file types.
				if ( $is_js_template || isset( $fb_option['field_type'] ) && $fb_option['field_type'] === 'photo' ) {
					$sub_description = esc_html__( 'File types can be jpg, png, or gif.', 'pro-pack-for-wp-job-openings' );
					if ( $is_js_template ) {
						$sub_description = "<# if( data.fieldType && data.fieldType === 'photo' ) { #>" . $sub_description . '<# } #>';
					} else {
						if ( empty( $file_types ) ) {
							$file_types = 'jpg,png';
						}
					}
					$description .= ' ' . $sub_description;
				}
				?>
				<p>
					<label for="awsm-jobs-form-builder-file-types-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Allowed File Types:', 'pro-pack-for-wp-job-openings' ); ?></label>
					<input type="text" id="awsm-jobs-form-builder-file-types-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][file_types]" class="widefat"  value="<?php echo ! $is_js_template ? esc_attr( $file_types ) : $file_types; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" />
					<p class="description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				</p>
				<?php
					// Handle file specific fields.
				if ( $is_js_template || isset( $fb_option['field_type'] ) && $fb_option['field_type'] === 'file' ) :
					$multi_upload = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['multi_upload'] ) && absint( $fb_option['misc_options']['multi_upload'] ) ? $fb_option['misc_options']['multi_upload'] : 1;

					echo $is_js_template ? "<# if( data.fieldType && data.fieldType === 'file' ) { #>" : '';
					?>
						<p>
							<label for="awsm-jobs-form-builder-multi-upload-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Maximum Files', 'pro-pack-for-wp-job-openings' ); ?></label>
							<input type="number" id="awsm-jobs-form-builder-multi-upload-<?php echo esc_attr( $index ); ?>" class="small-text" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][multi_upload]" value="<?php echo esc_attr( $multi_upload ); ?>" min="1" />
						</p>
					<?php
					echo $is_js_template ? '<# } #>' : '';
					endif;
			endif;
				$wp_max_upload_size = self::get_valid_file_size( wp_max_upload_size(), 'M' );
				$max_file_size      = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['max_file_size'] ) && absint( $fb_option['misc_options']['max_file_size'] ) ? self::get_valid_file_size( $fb_option['misc_options']['max_file_size'], 'M' ) : $wp_max_upload_size;
			?>
				<p>
					<label for="awsm-jobs-form-builder-max-file-size-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Maximum File Size', 'pro-pack-for-wp-job-openings' ); ?></label>
					<input type="number" id="awsm-jobs-form-builder-max-file-size-<?php echo esc_attr( $index ); ?>" class="small-text" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][max_file_size]" value="<?php echo esc_attr( $max_file_size ); ?>" min="1" max="<?php echo esc_attr( $wp_max_upload_size ); ?>" />
					<p class="description"><?php esc_html_e( 'Maximum allowed file size in megabytes.', 'pro-pack-for-wp-job-openings' ); ?></p>
				</p>
				<p>
					<input type="checkbox" id="awsm-jobs-form-builder-drag-and-drop-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][drag_and_drop]" value="enable" <?php echo $drag_and_drop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
					<label for="awsm-jobs-form-builder-drag-and-drop-<?php echo esc_attr( $index ); ?>">
						<?php esc_html_e( 'Enable drag and drop file upload', 'pro-pack-for-wp-job-openings' ); ?>
					</label>
				</p>
			<?php
		}

		public function fb_field_tag_template( $index, $fb_option = array() ) {
			?>
				<p>
					<label for="awsm-jobs-form-builder-template-tag-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Template Tag:', 'pro-pack-for-wp-job-openings' ); ?></label>
					<input type="text" class="widefat awsm-jobs-form-builder-template-tag" id="awsm-jobs-form-builder-template-tag-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][template_tag]" placeholder="<?php echo esc_attr__( 'Template Tag to be used in the notification', 'pro-pack-for-wp-job-openings' ); ?>" value="<?php echo isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['template_tag'] ) ? esc_attr( $fb_option['misc_options']['template_tag'] ) : ''; ?>" >
				</p>
			<?php
		}

		public function fb_section_field_options_template( $index, $fb_option = array() ) {
			$section_title       = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['section_title'] ) ? $fb_option['misc_options']['section_title'] : '';
			$section_description = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['section_description'] ) ? $fb_option['misc_options']['section_description'] : '';
			?>
			<p>
				<label for="awsm-jobs-form-builder-section-field-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Title:', 'pro-pack-for-wp-job-openings' ); ?></label>

				<input type="text" class="widefat awsm-jobs-form-builder-section-field" id="awsm-jobs-form-builder-section-field-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][section_title]" placeholder="<?php esc_attr_e( 'Section title', 'pro-pack-for-wp-job-openings' ); ?>" value="<?php echo esc_attr( $section_title ); ?>" required />
			</p>
			<p>
				<label for="awsm-jobs-form-builder-description-field-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Description:', 'pro-pack-for-wp-job-openings' ); ?></label>

				<textarea class="widefat awsm-jobs-form-builder-description-field" id="awsm-jobs-form-builder-description-field-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][section_description]" cols="25" rows="3" placeholder="<?php esc_attr_e( 'Section description (optional)', 'pro-pack-for-wp-job-openings' ); ?>"><?php echo esc_textarea( $section_description ); ?></textarea>
			</p>
			<?php
		}

		public function fb_placeholder_field_options_template( $index, $fb_option = array() ) {
			$placeholder = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['placeholder'] ) ? $fb_option['misc_options']['placeholder'] : '';
			?>
				<p>
				<label for="awsm-jobs-form-builder-placeholder-field-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Placeholder:', 'pro-pack-for-wp-job-openings' ); ?></label>
					<input type="text" class="widefat awsm-jobs-form-builder-placeholder-field" id="awsm-jobs-form-builder-placeholder-field-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][placeholder]" value="<?php echo esc_attr( $placeholder ); ?>" />
				</p>
			<?php
		}

		public function fb_iti_options_template( $index, $fb_option = array() ) {
			$country_input   = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['country_input'] ) ? $fb_option['misc_options']['country_input'] : '';
			$input_checked   = checked( $country_input, 'enable', false );
			$default_country = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['default_country'] ) ? $fb_option['misc_options']['default_country'] : '';
			?>
				<p>
					<input type="checkbox" id="awsm-jobs-form-builder-iti-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-iti-control" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][country_input]" value="enable"<?php echo $input_checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
					<label for="awsm-jobs-form-builder-iti-<?php echo esc_attr( $index ); ?>">
						<?php esc_html_e( 'Enable country based input', 'pro-pack-for-wp-job-openings' ); ?>
					</label>
				</p>
				<p class="awsm-jobs-form-builder-iti-default-wrapper <?php echo $country_input !== 'enable' ? ' hidden' : ''; ?>">
					<label for="awsm-jobs-form-builder-iti-default-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Default Country:', 'pro-pack-for-wp-job-openings' ); ?></label>
						<input type="text" class="widefat" id="awsm-jobs-form-builder-iti-default-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][misc_options][default_country]" value="<?php echo esc_attr( $default_country ); ?>" minlength="2" maxlength="2" />
						<p class="description"> <?php esc_html_e( 'Country code to be used as default (two characters in length)', 'pro-pack-for-wp-job-openings' ); ?> </p>
				</p>
			<?php
		}

		public function fb_template( $index, $fb_option = array() ) {
			if ( ! empty( $fb_option ) && ! is_numeric( $index ) ) {
				return;
			}

			$field_types = self::form_builder_field_types();
			$main_class  = ! is_numeric( $index ) ? ' open' : '';
			$title       = esc_html__( 'New input field', 'pro-pack-for-wp-job-openings' );
			$super_field = $default_field = $repeater_field = false; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			$field_type  = 'text';
			$label       = $field_type_label = $hidden_fields = ''; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

			if ( ! empty( $fb_option ) ) {
				$super_field     = $fb_option['super_field'];
				$default_field   = $fb_option['default_field'];
				$repeater_field  = isset( $fb_option['repeater'] ) && isset( $fb_option['repeater']['fields'] );
				$title           = $fb_option['label'];
				$field_type      = $fb_option['field_type'];
				$label           = $fb_option['label'];
				$field_type_data = isset( $field_types[ $field_type ] ) ? $field_types[ $field_type ] : '';
				if ( is_array( $field_type_data ) ) {
					$field_type_label = $field_type_data['label'];
				} else {
					$field_type_label = $field_type_data;
				}
				$hidden_fields = sprintf( '<input type="hidden" name="awsm_jobs_form_builder[%s][name]" value="%s" />', esc_attr( $index ), esc_attr( $fb_option['name'] ) );
				if ( $super_field ) {
					$hidden_fields .= sprintf( '<input type="hidden" name="awsm_jobs_form_builder[%s][super_field]" value="super" />', esc_attr( $index ) );
				}
				if ( $default_field ) {
					$hidden_fields .= sprintf( '<input type="hidden" name="awsm_jobs_form_builder[%s][default_field]" value="default" />', esc_attr( $index ) );
				}
			}

			ob_start();
			?>
			<div class="awsm-jobs-form-element-main<?php echo esc_attr( $main_class ); ?>">
				<div class="awsm-jobs-form-element-head">
					<div class="awsm-jobs-form-element-head-title">
						<h3>
							<span class="awm-jobs-form-builder-title">
								<?php echo esc_html( $title ); ?>
							</span>
							<span class="awm-jobs-form-builder-input-type">
								<?php echo esc_html( $field_type_label ); ?>
							</span>
						</h3>
					</div>
				</div><!-- .awsm-jobs-form-element-head -->
				<div class="awsm-jobs-form-element-content">
					<div class="awsm-jobs-form-element-content-in">
						<?php echo $hidden_fields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

						<div class="awsm-jobs-form-builder-type-wrapper">
							<label for="awsm-jobs-form-builder-type-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Field Type:', 'pro-pack-for-wp-job-openings' ); ?></label>
							<select class="awsm-builder-field-select-control" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][field_type]" id="awsm-jobs-form-builder-type-<?php echo esc_attr( $index ); ?>" data-index="<?php echo esc_attr( $index ); ?>">
								<?php
								foreach ( $field_types as $value => $field_type_data ) {
									$text  = $field_type_data;
									$attrs = '';
									if ( is_array( $field_type_data ) ) {
										$text = $field_type_data['label'];
										if ( isset( $field_type_data['repeater'] ) && $field_type_data['repeater'] ) {
											$attrs .= ' data-repeater="true"';
										}
									}
									$attrs .= $value === $field_type ? ' selected' : '';
									if ( $default_field || $field_type === 'photo' ) {
										$attrs .= $field_type !== $value ? ' disabled' : '';
									}
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									printf( '<option value="%1$s"%3$s>%2$s</option>', esc_attr( $value ), esc_html( $text ), $attrs );
								}
								?>
							</select>
							<div class="awsm-job-fb-options-container">
								<?php
								ob_start();
								if ( $field_type === 'select' || $field_type === 'radio' || $field_type === 'checkbox' ) {
									$this->fb_field_options_template( $index, $fb_option );
								} elseif ( $field_type === 'file' || $field_type === 'resume' || $field_type === 'photo' ) {
									$this->fb_file_type_options_template( $index, $fb_option );
								} elseif ( $field_type === 'section' ) {
									$this->fb_section_field_options_template( $index, $fb_option );
								} elseif ( $field_type === 'tel' ) {
									$this->fb_iti_options_template( $index, $fb_option );
								}
								$fb_field_options_content = ob_get_clean();
								/**
								 * Filters the form builder field options content.
								 *
								 * @since 3.1.0
								 *
								 * @param string $fb_field_options_content Form field options content.
								 * @param int|string $index Field index.
								 * @param array $fb_option Field options array.
								 */
								echo apply_filters( 'awsm_jobs_fb_field_options_content', $fb_field_options_content, $index, $fb_option ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</div>
						</div>

						<?php
							/**
							 * Fires before the form builder field main content.
							 *
							 * @since 3.1.0
							 *
							 * @param int|string $index Field index.
							 * @param array $fb_option Field options array.
							 */
							do_action( 'before_awsm_jobs_fb_field_main_content', $index, $fb_option );
						?>
						<p class="awsm-job-fb-label-wrapper<?php echo $field_type === 'section' ? ' hidden' : ''; ?>">
							<label for="awsm-jobs-form-builder-label-<?php echo esc_attr( $index ); ?>" class="awsm-jobs-form-builder-field-label"><?php esc_html_e( 'Label:', 'pro-pack-for-wp-job-openings' ); ?></label>
							<input type="text" class="widefat awsm_jobs_form_builder_label<?php echo ! is_numeric( $index ) ? ' awsm_jobs_form_builder_new_label' : ''; ?>" id="awsm-jobs-form-builder-label-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][label]" value="<?php echo esc_attr( $label ); ?>" required />
						</p>

						<div class="awsm-job-fb-placeholder-option">
						<?php
						if ( $field_type === 'text' || $field_type === 'email' || $field_type === 'number' || $field_type === 'textarea' || $field_type === 'tel' ) {
							$this->fb_placeholder_field_options_template( $index, $fb_option );
						}
						?>
						</div>
						<div class="awsm-job-fb-template-key<?php echo $field_type === 'section' ? ' hidden' : ''; ?>">
						<?php
						if ( ! $default_field && $field_type !== 'resume' && $field_type !== 'photo' && $field_type !== 'file' && $field_type !== 'section' ) {
							$this->fb_field_tag_template( $index, $fb_option );
						}
						?>
						</div>

						<p class="awsm-job-fb-required-wrapper<?php echo ( $field_type === 'section' || $repeater_field ) ? ' hidden' : ''; ?>">
							<label for="awsm-jobs-form-builder-required-field-<?php echo esc_attr( $index ); ?>">
									<?php
									$attrs = '';
									if ( ! empty( $fb_option ) ) {
										$attrs = ' ' . $this->is_settings_field_checked( $fb_option['required'], 'required' );
										if ( $super_field ) {
											$attrs .= ' disabled';
										}
									}
									?>
								<input type="checkbox" name="awsm_jobs_form_builder[<?php echo esc_attr( $index ); ?>][required]" class="awsm-form-builder-required-field" id="awsm-jobs-form-builder-required-field-<?php echo esc_attr( $index ); ?>" value="required"<?php echo esc_attr( $attrs ); ?> /><?php esc_html_e( 'Required Field', 'pro-pack-for-wp-job-openings' ); ?>
							</label>
						</p>
						<?php
							/**
							 * Fires after the form builder field main content.
							 *
							 * @since 3.1.0
							 *
							 * @param int|string $index Field index.
							 * @param array $fb_option Field options array.
							 */
							do_action( 'after_awsm_jobs_fb_field_main_content', $index, $fb_option );
						?>
						<p>
							<?php if ( ! $super_field ) : ?>
									<a class="button-link awsm-text-red awsm-form-field-remove-row" href="#" ><?php esc_html_e( 'Delete', 'pro-pack-for-wp-job-openings' ); ?></a>
									<span> | </span>
							<?php endif; ?>

							<button type="button" class="button-link awsm-jobs-form-element-close"><?php esc_html_e( 'Close', 'pro-pack-for-wp-job-openings' ); ?></button>
						</p>
					</div><!-- .awsm-jobs-form-element-content-in -->
				</div><!-- .awsm-jobs-form-element-content -->
			</div><!-- .awsm-jobs-form-element-main -->
			<?php

			$fb_field_content = ob_get_clean();
			/**
			 * Filters the form builder field content.
			 *
			 * @since 3.1.0
			 *
			 * @param string $fb_field_content Form field content.
			 * @param int|string $index Field index.
			 * @param array $fb_option Field options array.
			 */
			echo apply_filters( 'awsm_jobs_fb_field_content', $fb_field_content, $index, $fb_option ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		public function status_template( $index, $status_option = array() ) {
			$title_format = '<span class="awsm-jobs-manage-application-status-title">%s</span>';
			$title        = sprintf( $title_format, esc_html__( 'New Status', 'pro-pack-for-wp-job-openings' ) );

			$label = $default_color = $status_color = $status_notification = $status_mail_template = $status_key = ''; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

			$is_core_status = $index === 'publish' || $index === 'trash';
			$header_class   = ' on';
			$content_style  = empty( $status_option ) ? ' style="display: block;"' : '';
			if ( ! empty( $status_option ) ) {
				$default_options = AWSM_Job_Openings_Pro_Main::get_application_status( true );
				$label           = $status_option['label'];
				$header_class    = $index === 'publish' ? $header_class : '';
				$title           = sprintf( $title_format, esc_html( $label ) );
				if ( isset( $status_option['color'] ) ) {
					$status_color = $status_option['color'];
				}
				if ( isset( $default_options[ $index ] ) ) {
					$default_color = sprintf( ' data-default-color="%s"', esc_attr( $default_options[ $index ]['color'] ) );
				}
				if ( isset( $status_option['notification'] ) ) {
					$status_notification = $status_option['notification'];
				}
				if ( isset( $status_option['mail_template'] ) ) {
					$status_mail_template = $status_option['mail_template'];
				}
				if ( ! isset( $status_option['key'] ) ) {
					$status_key = sanitize_title( $status_option['label'] );
				} else {
					$status_key = sanitize_title( $status_option['key'] );
				}
			}
			$header_class .= ' awsm-clearfix';
			?>
			<div class="awsm-acc-main<?php echo ! $is_core_status ? ' awsm-acc-main-sortable-item' : ''; ?>" id="application-status-<?php echo esc_attr( $status_key ); ?>" >
				<div class="awsm-jobs-manage-application-status-header awsm-acc-head<?php echo esc_attr( $header_class ); ?>">
					<h3>
						<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo $title;
						if ( ! $is_core_status ) {
							echo '<span class="awsm-acc-drag-control dashicons dashicons-move"></span>';
						}
						?>
					</h3>
				</div><!-- .awsm-acc-head -->
				<div class="awsm-acc-content"<?php echo $content_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<div class="form-group-1 col-md-6">
						<div class="awsm-row" data-index="<?php echo esc_attr( $index ); ?>">
							<div class="awsm-col awsm-form-group awsm-col-full">
								<label for="awsm-jobs-manage-application-status-label-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Status Label', 'pro-pack-for-wp-job-openings' ); ?></label>
								<input type="text" name="awsm_jobs_application_status[<?php echo esc_attr( $index ); ?>][label]" class="awsm-form-control awsm-jobs-manage-application-status-label" id="awsm-jobs-manage-application-status-label-<?php echo esc_attr( $index ); ?>" value="<?php echo esc_attr( $label ); ?>" required />
							</div><!-- .col -->
							<div class="awsm-col awsm-form-group awsm-col-full">
								<label for="awsm-jobs-manage-application-status-key-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Status Key', 'pro-pack-for-wp-job-openings' ); ?></label>
									<input type="text" class="awsm-form-control awsm-jobs-manage-application-status-key" id="awsm-jobs-manage-application-status-key-<?php echo esc_attr( $index ); ?>" value="<?php echo esc_attr( $status_key ); ?>"<?php echo ! empty( $status_option ) ? ' disabled' : ' maxlength="20"'; ?> />
									<input type="hidden" class="awsm-jobs-manage-application-status-key" id="awsm-jobs-manage-application-status-key-hidden-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_application_status[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $status_key ); ?>" />
							</div><!-- .col -->
							<div class="awsm-col awsm-form-group awsm-col-full">
								<label for="awsm-jobs-manage-application-status-color-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Status Color', 'pro-pack-for-wp-job-openings' ); ?></label>
									<input type="text" class="awsm-jobs-colorpicker-field" id="awsm-jobs-manage-application-status-color-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_application_status[<?php echo esc_attr( $index ); ?>][color]" value="<?php echo esc_attr( $status_color ); ?>"<?php echo $default_color; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> />
							</div><!-- .col -->
							<div class="awsm-col awsm-form-group awsm-col-full<?php echo $index === 'publish' ? ' awsm-hide' : ''; ?>">
								<label for="awsm-jobs-manage-application-mail-on-status-<?php echo esc_attr( $index ); ?>"><input type="checkbox" name="awsm_jobs_application_status[<?php echo esc_attr( $index ); ?>][notification]" id="awsm-jobs-manage-application-mail-on-status-<?php echo esc_attr( $index ); ?>" value="yes" class="awsm-check-control-field awsm-jobs-manage-application-mail-on-status" <?php checked( $status_notification, 'yes' ); ?> /> <?php esc_html_e( 'Automatically send a notification on status change', 'pro-pack-for-wp-job-openings' ); ?></label>
								<p class="description"><?php esc_html_e( 'Checking this option will automatically notify the applicant based on the template for the selected status.', 'pro-pack-for-wp-job-openings' ); ?></p>
							</div><!-- .col -->
							<div class="awsm-jobs-status-mail-template-group awsm-col awsm-form-group awsm-col-full<?php echo $status_notification !== 'yes' ? ' awsm-hide' : ''; ?>">
								<label for="awsm-jobs-manage-application-mail-template-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Email template', 'pro-pack-for-wp-job-openings' ); ?></label>
									<select class="awsm-form-control awsm-jobs-manage-application-mail-template" id="awsm-jobs-manage-application-mail-template-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_application_status[<?php echo esc_attr( $index ); ?>][mail_template]">
										<option value=""><?php esc_html_e( 'Select a template', 'pro-pack-for-wp-job-openings' ); ?></option>
										<?php
											$ets_data = get_option( 'awsm_jobs_pro_mail_templates' );
										if ( ! empty( $ets_data ) ) :
											foreach ( $ets_data as $et_data ) :
												?>
														<option value="<?php echo esc_attr( $et_data['key'] ); ?>" <?php selected( $status_mail_template, $et_data['key'] ); ?>><?php echo esc_html( $et_data['name'] ); ?></option>
													<?php
												endforeach;
											endif;
										?>
									</select>
							</div><!-- .col -->
						</div><!-- row -->
					</div>
					<ul class="awsm-list-inline">
						<li><?php echo apply_filters( 'awsm_job_settings_submit_btn', get_submit_button( esc_html__( 'Save', 'pro-pack-for-wp-job-openings' ) ), 'advanced' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
						<?php if ( ! $is_core_status ) : ?>
							<li><a href="#" class="awsm-text-red awsm-remove-application-status" data-status-label="<?php echo esc_attr( $label ); ?>"  data-status="<?php echo esc_attr( $status_key ); ?>" data-old-status="<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Delete status', 'pro-pack-for-wp-job-openings' ); ?></a></li>
						<?php endif; ?>
					</ul>
				</div><!-- .awsm-acc-content -->
			</div><!-- .awsm-acc-main -->
			<?php
		}

		public function mail_template( $index, $template = array() ) {
			if ( ! empty( $template ) && ! is_numeric( $index ) ) {
				return;
			}

			$title_format = '<span class="awsm-jobs-pro-mail-template-title">%s</span>%s';
			$subtitle     = sprintf( '<span class="awsm-jobs-pro-mail-template-subtitle hidden">%s</span>', esc_html__( '(Not Saved...)', 'pro-pack-for-wp-job-openings' ) );
			$title        = sprintf( $title_format, esc_html__( 'New Template', 'pro-pack-for-wp-job-openings' ), $subtitle );

			$name          = $subject = $content = $hidden_fields = ''; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
			$header_class  = ' on';
			$content_style = ! is_numeric( $index ) ? ' style="display: block;"' : '';
			if ( ! empty( $template ) ) {
				$name          = $template['name'];
				$header_class  = $index === 0 ? $header_class : '';
				$title         = sprintf( $title_format, esc_html( $name ), '' );
				$subject       = $template['subject'];
				$content       = $template['content'];
				$hidden_fields = sprintf( '<input type="hidden" name="awsm_jobs_pro_mail_templates[%s][key]" value="%s" />', esc_attr( $index ), esc_attr( $template['key'] ) );
			}
			?>
			<div class="awsm-acc-main">
				<div class="awsm-jobs-pro-mail-template-header awsm-acc-head<?php echo esc_attr( $header_class ); ?>">
					<h3>
						<?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</h3>
				</div><!-- .awsm-acc-head -->
				<div class="awsm-acc-content"<?php echo $content_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
					<div class="form-group-1 col-md-6">
						<div class="awsm-row" data-index="<?php echo esc_attr( $index ); ?>">
							<div class="awsm-col awsm-form-group awsm-col-full">
								<label for="awsm-jobs-pro-mail-name-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Template Name', 'pro-pack-for-wp-job-openings' ); ?></label>
								<input type="text" name="awsm_jobs_pro_mail_templates[<?php echo esc_attr( $index ); ?>][name]" class="awsm-form-control awsm-jobs-pro-mail-template-name" id="awsm-jobs-pro-mail-name-<?php echo esc_attr( $index ); ?>" value="<?php echo esc_attr( $name ); ?>" data-required="required" />
								<?php echo $hidden_fields; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div><!-- .col -->
							<div class="awsm-col awsm-form-group awsm-col-full">
								<label for="awsm-jobs-pro-mail-subject-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Subject ', 'pro-pack-for-wp-job-openings' ); ?></label>
									<input type="text" class="awsm-form-control" id="awsm-jobs-pro-mail-subject-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_pro_mail_templates[<?php echo esc_attr( $index ); ?>][subject]" value="<?php echo esc_attr( $subject ); ?>"  />
							</div><!-- .col -->
							<div class="awsm-col awsm-form-group awsm-col-full">
								<label for="awsm-jobs-pro-mail-content-<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Content ', 'pro-pack-for-wp-job-openings' ); ?></label>
									<?php
									if ( function_exists( 'awsm_jobs_wp_editor' ) && is_numeric( $index ) ) :
										awsm_jobs_wp_editor(
											$content,
											'awsm-jobs-pro-mail-content-' . esc_attr( $index ),
											array(
												'textarea_name' => sprintf( 'awsm_jobs_pro_mail_templates[%s][content]', esc_attr( $index ) ),
												'editor_height' => 180,
											)
										);
										else :
											?>
										<textarea class="awsm-form-control" id="awsm-jobs-pro-mail-content-<?php echo esc_attr( $index ); ?>" name="awsm_jobs_pro_mail_templates[<?php echo esc_attr( $index ); ?>][content]" rows="7" cols="50"><?php echo esc_textarea( $content ); ?></textarea>
											<?php
										endif;
										?>
							</div><!-- .col -->
						</div><!-- row -->
					</div>
					<ul class="awsm-list-inline">
						<li><?php echo apply_filters( 'awsm_job_settings_submit_btn', get_submit_button( esc_html__( 'Save', 'pro-pack-for-wp-job-openings' ) ), 'notification' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
						<li><a href="#" class="awsm-text-red awsm-remove-mail-template"><?php esc_html_e( 'Delete template', 'pro-pack-for-wp-job-openings' ); ?></a></li>
					</ul>
				</div><!-- .awsm-acc-content -->
			</div><!-- .awsm-acc-main -->
			<?php
		}

		public function custom_template_tags( $tags ) {
			global $post;
			$form_id = 'default';
			if ( isset( $post ) && get_post_type( $post ) === 'awsm_job_form' ) {
				$form_id = $post->ID;
			}
			$fb_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form_id );
			if ( is_array( $fb_options ) ) {
				$removable_tags = array(
					'{applicant-phone}'  => 'awsm_applicant_phone',
					'{applicant-resume}' => 'awsm_file',
					'{applicant-cover}'  => 'awsm_applicant_letter',
				);
				foreach ( $fb_options as $fb_option ) {
					if ( $fb_option['default_field'] !== true && $fb_option['field_type'] !== 'photo' && $fb_option['field_type'] !== 'file' ) {
							$template_tag = isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['template_tag'] ) ? $fb_option['misc_options']['template_tag'] : '';
						if ( ! empty( $template_tag ) ) {
							$key          = sprintf( '{%s}', $template_tag );
							$tags[ $key ] = $fb_option['label'] . ':';
						}
					} else {
						if ( $fb_option['super_field'] !== true ) {
							$field_name = $fb_option['name'];
							$tag        = array_search( $field_name, $removable_tags );
							if ( $tag !== false ) {
								unset( $removable_tags[ $tag ] );
							}
						}
					}
				}
				if ( ! empty( $removable_tags ) ) {
					foreach ( $removable_tags as $removable_tag => $field_name ) {
						unset( $tags[ $removable_tag ] );
					}
				}
			}
			return $tags;
		}

		public function appearance_settings_fields( $settings_fields ) {
			$listing_fields = $settings_fields['listing'];

			$offset = 0;
			foreach ( $listing_fields as $field_index => $listing_field ) {
				if ( isset( $listing_field['name'] ) && $listing_field['name'] === 'awsm_jobs_list_per_page' ) {
					$offset = $field_index + 1;
				}
			}

			$image_sizes = get_intermediate_image_sizes();
			if ( ! in_array( 'full', $image_sizes, true ) ) {
				$image_sizes[] = 'full';
			}
			$image_size_choices = array();
			foreach ( $image_sizes as $image_size ) {
				$image_size_choices[] = array(
					'value' => $image_size,
					'text'  => $image_size,
				);
			}
			$featured_image_support = get_option( 'awsm_jobs_enable_featured_image' ) === 'enable';
			array_splice(
				$listing_fields,
				$offset,
				0,
				array(
					array(
						'visible'       => $featured_image_support,
						'name'          => 'awsm_jobs_listing_featured_image_size',
						'label'         => __( 'Featured image size', 'pro-pack-for-wp-job-openings' ),
						'type'          => 'select',
						'class'         => 'awsm-select-control regular-text',
						'choices'       => $image_size_choices,
						'default_value' => 'thumbnail',
					),
				)
			);
			$listing_fields[]           = array(
				'name'    => 'awsm_jobs_filled_jobs_listings',
				'label'   => __( 'Position Filled', 'pro-pack-for-wp-job-openings' ),
				'type'    => 'checkbox',
				'choices' => array(
					array(
						'value' => 'filled',
						'text'  => __( 'Hide filled positions from listing page', 'pro-pack-for-wp-job-openings' ),
					),
				),
			);
			$settings_fields['listing'] = $listing_fields;

			$settings_fields['details'][] = array(
				'name'          => 'awsm_jobs_back_to_listings',
				'label'         => __( 'Back to job listings', 'pro-pack-for-wp-job-openings' ),
				'type'          => 'checkbox',
				'class'         => '',
				'default_value' => 'default',
				'choices'       => array(
					array(
						'value' => 'enable',
						'text'  => __( 'Enable back to listings link', 'pro-pack-for-wp-job-openings' ),
					),
				),
				'description'   => __( 'Check this option to show back to job listings link in the job detail page', 'pro-pack-for-wp-job-openings' ),
			);
			return $settings_fields;
		}

		public function notification_customizer_fields( $customizer_fields ) {
			$customizer_settings = AWSM_Job_Openings_Mail_Customizer::get_settings();
			$offset              = 3;
			$background_color    = array(
				'id'    => 'awsm_jobs_notification_customizer_bg_color',
				'name'  => 'awsm_jobs_notification_customizer[bg_color]',
				'label' => __( 'Background Color', 'pro-pack-for-wp-job-openings' ),
				'type'  => 'colorpicker',
				'value' => isset( $customizer_settings['bg_color'] ) ? $customizer_settings['bg_color'] : '#f1f4f7',
			);
			$customizer_fields   = array_merge( array_slice( $customizer_fields, 0, $offset ), array( $background_color ), array_slice( $customizer_fields, $offset ) );

			return $customizer_fields;
		}

		public function notification_html_template_main_styles( $styles ) {
			$customizer_settings = AWSM_Job_Openings_Mail_Customizer::get_settings();
			if ( isset( $customizer_settings['bg_color'] ) ) {
				$styles[] = array(
					'selector'    => 'body, center, .email-container-body, .email-main-container',
					'declaration' => array(
						'background-color' => $customizer_settings['bg_color'] . ' !important',
					),
				);
			}

			return $styles;
		}

		public function delete_job_status_handler() {
			$response        = array(
				'error' => array(),
			);
			$generic_err_msg = esc_html__( 'Error in delete status!', 'pro-pack-for-wp-job-openings' );

			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) ) {
				$response['error'][] = $generic_err_msg;
			}

			$old_status = isset( $_POST['awsm_job_old_status'] ) ? $_POST['awsm_job_old_status'] : '';
			if ( count( $response['error'] ) === 0 ) {
				$application_status = AWSM_Job_Openings_Pro_Main::get_application_status();
				unset( $application_status['publish'] );
				unset( $application_status['trash'] );
				unset( $application_status[ $old_status ] );
				$remaining_status = count( $application_status );

				$args['post_type']          = 'awsm_job_application';
				$args['numberposts']        = -1;
				$args['fields']             = 'ids';
				$args['post_status']        = $old_status;
				$application_ids            = get_posts( $args );
				$apllication_count          = count( $application_ids );
				$response['count'][]        = $apllication_count;
				$response['status_count'][] = $remaining_status;
			}
			wp_send_json( $response );
		}

		public function delete_or_move_job_status() {
			$response        = array(
				'success' => array(),
				'error'   => array(),
			);
			$generic_err_msg = esc_html__( 'Error in delete status!', 'pro-pack-for-wp-job-openings' );

			if ( ! wp_verify_nonce( $_POST['nonce'], 'awsm-pro-admin-nonce' ) ) {
				$response['error'][] = $generic_err_msg;
			}
			$action     = isset( $_POST['status_action'] ) ? $_POST['status_action'] : '';
			$old_status = sanitize_text_field( $_POST['old_status'] );
			$options    = get_option( 'awsm_jobs_application_status' );
			if ( $options ) {
				$status_options_data = $options;
			} else {
				$status_options_data = AWSM_Job_Openings_Pro_Main::get_application_status( true );
			}

			if ( $action === 'delete_status' ) {
				unset( $status_options_data[ $old_status ] );
				$deleted = update_option( 'awsm_jobs_application_status', $status_options_data );
				if ( $deleted ) {
					$response['success'][] = '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible awsm-status-response-notice"><p><strong>' . esc_html__( 'Status deleted successfully.', 'pro-pack-for-wp-job-openings' ) . '</strong></p><button type="button" class="notice-dismiss awsm-delete-status-notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'pro-pack-for-wp-job-openings' ) . '</span></button></div>';
				} else {
					$response['error'][] = '<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible awsm-status-response-notice"><p><strong>' . esc_html__( 'Failed to delete status!.', 'pro-pack-for-wp-job-openings' ) . '</strong></p><button type="button" class="notice-dismiss awsm-delete-status-notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'pro-pack-for-wp-job-openings' ) . '</span></button></div>';
				}
			} elseif ( $action === 'move_status' ) {
				$new_status          = $_POST['awsm_application_status'];
				$staus_label         = $_POST['awsm_application_status_label'];
				$args['post_type']   = 'awsm_job_application';
				$args['numberposts'] = -1;
				$args['fields']      = 'ids';
				$args['post_status'] = $old_status;
				$apllication_ids     = get_posts( $args );
				$application_count   = count( $apllication_ids );

				if ( ! empty( $apllication_ids ) ) {
					set_time_limit( 300 );
					foreach ( $apllication_ids as $post_id ) {
						$updated_post = array(
							'ID'          => $post_id,
							'post_status' => $new_status,
						);

						wp_update_post( $updated_post );

						$status         = get_post_status( $post_id );
						$status_options = AWSM_Job_Openings_Pro_Main::get_application_status();
						unset( $status_options['publish'], $status_options['trash'] );
						$user_id      = get_current_user_id();
						$current_time = current_time( 'timestamp' );
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
					unset( $status_options_data[ $old_status ] );
					$deleted = update_option( 'awsm_jobs_application_status', $status_options_data );

					$success_message = sprintf(
						/* translators: %1$s represents the application count, %2$s represents the status label */
						esc_html__( '%1$d applications moved to \'%2$s\'', 'pro-pack-for-wp-job-openings' ),
						$application_count,
						$staus_label
					);
					$response['success'][] = '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible awsm-status-response-notice"><p><strong>' . $success_message . '</strong></p><button type="button" class="notice-dismiss awsm-delete-status-notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'pro-pack-for-wp-job-openings' ) . '</span></button></div>';
				} else {
					$response['error'][] = '<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible awsm-status-response-notice"><p><strong>' . esc_html__( 'Failed to delete status!.', 'pro-pack-for-wp-job-openings' ) . '</strong></p><button type="button" class="notice-dismiss awsm-delete-status-notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'pro-pack-for-wp-job-openings' ) . '</span></button></div>';
				}
			}
			wp_send_json( $response );
		}
	}

	AWSM_Job_Openings_Pro_Settings::init();

endif; // end of class check
