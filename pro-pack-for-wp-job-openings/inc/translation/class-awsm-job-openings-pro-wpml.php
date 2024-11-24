<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AWSM_Job_Openings_Pro_WPML {
	private static $instance = null;

	protected $cpath = null;

	public function __construct() {
		$this->cpath = untrailingslashit( plugin_dir_path( __FILE__ ) );

		add_action( 'update_option_awsm_jobs_form_builder', array( $this, 'form_builder_handler' ), 10, 2 );
		add_action( 'update_option_awsm_jobs_form_builder_other_options', array( $this, 'form_builder_other_options_handler' ), 10, 2 );
		add_action( 'update_option_awsm_jobs_form_confirmation_type', array( $this, 'form_confirmation_handler' ), 10, 2 );
		add_action( 'update_option_awsm_jobs_pro_mail_templates', array( $this, 'pro_mail_templates_handler' ), 10, 2 );
	}

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function form_builder_handler( $old_value, $fb_options ) {
		if ( ! empty( $fb_options ) && is_array( $fb_options ) ) {
			$name_format = 'Application Form: %s';
			foreach ( $fb_options as $fb_option ) {
				$field_type  = $fb_option['field_type'];
				$field_label = $fb_option['label'];
				if ( $field_type === 'section' ) {
					$name = sprintf( $name_format, 'Section Title for ' . $fb_option['name'] );
					do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name, $field_label );
					if ( isset( $fb_option['misc_options'] ) && isset( $fb_option['misc_options']['section_description'] ) ) {
						$name = sprintf( $name_format, 'Section Description for ' . $fb_option['name'] );
						do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name, $fb_option['misc_options']['section_description'] );
					}
				} else {
					$name = sprintf( $name_format, 'Label for ' . $fb_option['name'] );
					do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name, $field_label );
				}

				// Handle field options.
				if ( $fb_option['default_field'] === false ) {
					if ( $field_type === 'select' || $field_type === 'checkbox' || $field_type === 'radio' ) {
						$options_list = isset( $fb_option['field_options'] ) ? $fb_option['field_options'] : '';
						if ( ! empty( $options_list ) ) {
							$name = sprintf( $name_format, 'Field Options for ' . $fb_option['name'] );
							do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name, $options_list );
						}
					}
				}
			}
		}
	}

	public function form_builder_other_options_handler( $old_value, $other_options ) {
		if ( ! empty( $other_options ) && is_array( $other_options ) ) {
			$name_format = 'Application Form: %s';
			foreach ( $other_options as $name => $value ) {
				$name = sprintf( $name_format, ucwords( str_replace( '_', ' ', $name ) ) );
				do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name, $value );
			}
		}
	}

	public function form_confirmation_handler( $old_value, $options ) {
		if ( ! empty( $options ) && is_array( $options ) && $options['type'] === 'message' ) {
			$name = 'Application Form: Submit confirmation message';
			do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name, $options['message'] );
		}
	}

	public function pro_mail_templates_handler( $old_value, $template_options ) {
		if ( ! empty( $template_options ) && is_array( $template_options ) ) {
			foreach ( $template_options as $option ) {
				$name = 'Notification Template: ' . $option['name'];
				do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name . ' - Subject', $option['subject'] );
				do_action( 'wpml_register_single_string', 'pro-pack-for-wp-job-openings', $name . ' - Content', $option['content'] );
			}
		}
	}
}

AWSM_Job_Openings_Pro_WPML::init();
