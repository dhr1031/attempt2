<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AWSM_Job_Openings_Pro_Export {
	private static $instance = null;

	protected $cpath = null;

	public static $menu_slug = 'awsm-jobs-export';

	public function __construct() {
		$this->cpath = untrailingslashit( plugin_dir_path( __FILE__ ) );

		add_action( 'init', array( $this, 'export_applications_handler' ), 100 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function admin_menu() {
		$wp_version = get_bloginfo( 'version' );
		$menu_title = __( 'Export', 'pro-pack-for-wp-job-openings' );
		if ( version_compare( $wp_version, '5.3', '>=' ) ) {
			add_submenu_page( 'edit.php?post_type=awsm_job_openings', $menu_title, $menu_title, 'edit_others_applications', self::$menu_slug, array( $this, 'export_page' ), 5 );
		} else {
			add_submenu_page( 'edit.php?post_type=awsm_job_openings', $menu_title, $menu_title, 'edit_others_applications', self::$menu_slug, array( $this, 'export_page' ) );
		}
	}

	public function export_page() {
		include_once $this->cpath . '/templates/export/main.php';
	}

	public static function export_button() {
		$button = sprintf( '<button type="submit" class="button button-primary %2$s" name="awsm_export_submit">%1$s</button>', esc_html__( 'Export Applications', 'pro-pack-for-wp-job-openings' ), esc_attr( self::$menu_slug . '-button' ) );
		/**
		 * Customize the export button content.
		 *
		 * @since 3.2.0
		 */
		return apply_filters( 'awsm_jobs_export_button', $button );
	}

	public function export_applications_handler() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'awsm-jobs-export' && isset( $_POST['awsm_action'] ) && $_POST['awsm_action'] === 'export_applications' ) {
			if ( ! is_user_logged_in() || ! current_user_can( 'edit_others_applications' ) ) {
				wp_die( esc_html__( 'You are not authorized to make this request!', 'pro-pack-for-wp-job-openings' ) );
			}

			if ( ! isset( $_POST['awsm_nonce'] ) || ! wp_verify_nonce( $_POST['awsm_nonce'], 'awsm_export_nonce' ) ) {
				wp_die( esc_html__( 'Invalid request!', 'pro-pack-for-wp-job-openings' ) );
			}

			$args = array(
				'post_type'      => 'awsm_job_application',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'order'          => 'ASC',
				'orderby'        => 'ID',
			);

			$export_by = isset( $_POST['awsm_application_export_by'] ) ? sanitize_text_field( $_POST['awsm_application_export_by'] ) : 'job-listing';
			$job_id    = 0;
			$form_id   = 0;
			// Handle application status.
			if ( isset( $_POST['post_status'] ) && ! empty( $_POST['post_status'] ) ) {
				$args['post_status'] = sanitize_text_field( $_POST['post_status'] );
			}

			// Handle meta queries.
			$meta_query = array();
			if ( $export_by === 'job-listing' ) {
				$job_id = isset( $_POST['awsm_job_id'] ) ? intval( $_POST['awsm_job_id'] ) : '';
				if ( ! empty( $job_id ) && get_post_type( $job_id ) === 'awsm_job_openings' ) {
					$meta_query[] = array(
						'key'   => 'awsm_job_id',
						'value' => $job_id,
					);
				} else {
					$job_id = 0;
				}
			} else {
				$form_id = isset( $_POST['awsm_application_form_id'] ) ? intval( $_POST['awsm_application_form_id'] ) : '';
				if ( ! empty( $form_id ) ) {
					$meta_query[] = array(
						'key'   => 'awsm_job_form_id',
						'value' => $form_id,
					);
				} else {
					$meta_query[] = array(
						'key'     => 'awsm_job_form_id',
						'compare' => 'NOT EXISTS',
					);
				}
			}
			if ( ! empty( $meta_query ) ) {
				$args['meta_query'] = $meta_query;
			}

			// Handle specification filters.
			if ( $export_by === 'application-form' && isset( $_POST['awsm_job_admin_filter'] ) && is_array( $_POST['awsm_job_admin_filter'] ) ) {
				$job_specs = $_POST['awsm_job_admin_filter'];
				$filters   = array();
				foreach ( $job_specs as $taxonomy => $term_id ) {
					$taxonomy             = sanitize_text_field( $taxonomy );
					$filters[ $taxonomy ] = intval( $term_id );
				}
				if ( ! empty( $filters ) ) {
					$awsm_pro_job_openings = AWSM_Job_Openings_Pro_Pack::init();
					$job_ids               = $awsm_pro_job_openings->get_job_ids_by_tax( $filters );
					if ( ! empty( $job_ids ) ) {
						$args['post_parent__in'] = $job_ids;
					}
				}
			}

			// Handle search.
			if ( isset( $_POST['s'] ) && ! empty( $_POST['s'] ) ) {
				$args['s'] = sanitize_text_field( $_POST['s'] );
			}

			// Handle date queries.
			if ( isset( $_POST['awsm_date_from'] ) && isset( $_POST['awsm_date_to'] ) ) {
				$date_from = sanitize_text_field( $_POST['awsm_date_from'] );
				$date_to   = sanitize_text_field( $_POST['awsm_date_to'] );
				if ( ! empty( $date_from ) && ! empty( $date_to ) ) {
					$date_from          = DateTime::createFromFormat( 'Y-m-d', $date_from );
					$date_to            = DateTime::createFromFormat( 'Y-m-d', $date_to );
					$args['date_query'] = array(
						array(
							'after'     => $date_from->format( 'Y-m-d' ),
							'before'    => $date_to->format( 'Y-m-d' ),
							'inclusive' => true,
						),
					);
				}
			}

			/**
			 * Filters the arguments for the applications query for export.
			 *
			 * @since 2.0.1
			 *
			 * @param array $args An array containing arguments.
			 */
			$args = apply_filters( 'awsm_jobs_export_applications_query_args', $args );

			$applications = get_posts( $args );

			if ( ! empty( $applications ) ) {
				/**
				 * Fires once the applications are generated for the query and ready for export.
				 *
				 * @since 3.2.0
				 *
				 * @param array $applications The applications array.
				 */
				do_action( 'awsm_jobs_export_applications_init', $applications );

				if ( isset( $_POST['awsm_export_submit'] ) ) {
					// Generate Header.
					$data       = array(
						array(
							'awsm_application_id'   => esc_html__( 'Application ID', 'pro-pack-for-wp-job-openings' ),
							'awsm_applicant_name'   => esc_html__( 'Applicant Name', 'pro-pack-for-wp-job-openings' ),
							'awsm_applicant_email'  => esc_html__( 'Email', 'pro-pack-for-wp-job-openings' ),
							'awsm_job_id'           => esc_html__( 'Job ID', 'pro-pack-for-wp-job-openings' ),
							'awsm_apply_for'        => esc_html__( 'Job Title', 'pro-pack-for-wp-job-openings' ),
							'awsm_application_date' => esc_html__( 'Applied on', 'pro-pack-for-wp-job-openings' ),
						),
					);
					$fb_options = array();
					if ( ! empty( $job_id ) ) {
						$job_fb_options = AWSM_Job_Openings_Pro_Form::get_job_form_builder_options( $job_id );
						$fb_options     = $job_fb_options['fields'];
					} else {
						$fb_options = AWSM_Job_Openings_Pro_Form::get_form_builder_options( $form_id );
					}
					foreach ( $fb_options as $fb_option ) {
						if ( $fb_option['name'] !== 'awsm_applicant_name' && $fb_option['name'] !== 'awsm_applicant_email' && $fb_option['field_type'] !== 'section' ) {
							$data[0][ $fb_option['name'] ] = $fb_option['label'];
						}
					}
					$data[0]['application_rating'] = esc_html__( 'Rating', 'pro-pack-for-wp-job-openings' );
					$data[0]['application_status'] = esc_html__( 'Status', 'pro-pack-for-wp-job-openings' );

					// Now, generate content.
					foreach ( $applications as $application ) {
						$application_id    = $application->ID;
						$applicant_details = array();

						// Fixed fields.
						$applicant_details['awsm_application_id'] = $application_id;
						$general_fields                           = array( 'awsm_applicant_name', 'awsm_applicant_email', 'awsm_job_id', 'awsm_apply_for' );
						foreach ( $general_fields as $general_field ) {
							$applicant_details[ $general_field ] = get_post_meta( $application_id, $general_field, true );
						}
						$applicant_details['awsm_application_date'] = get_the_date( '', $application_id );

						// Custom fields.
						$custom_fields = get_post_meta( $application_id, 'awsm_applicant_custom_fields', true );
						foreach ( $fb_options as $fb_option ) {
							$field_type = $fb_option['field_type'];

							if ( $fb_option['super_field'] !== true && $field_type !== 'section' ) {
								$field_value = '';
								$name        = $fb_option['name'];
								if ( $fb_option['default_field'] !== true ) {
									if ( isset( $custom_fields[ $name ]['value'] ) ) {
										$field_value = $custom_fields[ $name ]['value'];
										if ( isset( $custom_fields[ $name ]['type'] ) && $custom_fields[ $name ]['type'] === 'repeater' && is_array( $field_value ) ) {
											$repeater_fields_value = $field_value;
											$field_value           = '';
											foreach ( $repeater_fields_value as $repeater_group ) {
												foreach ( $repeater_group as $repeater_field ) {
													$field_value .= $repeater_field['label'] . ': ' . $repeater_field['value'] . "\n";
												}
												$field_value .= "\n";
											}
											$field_value = trim( $field_value );
										}
										if ( $field_type === 'photo' || $field_type === 'file' ) {
											if ( $field_type === 'file' && is_array( $field_value ) ) {
												$multi_attachment_url = '';
												foreach ( $field_value as $file_id ) {
													$file_url = wp_get_attachment_url( $file_id );
													if ( ! empty( $file_url ) ) {
														$multi_attachment_url .= $file_url . "\r\n";
													}
												}
												$field_value = $multi_attachment_url;
											} else {
												$field_value = ! empty( $field_value ) ? wp_get_attachment_url( $field_value ) : '';
											}
										}
									}
								} else {
									if ( $field_type === 'resume' ) {
										$attachment_id = get_post_meta( $application_id, 'awsm_attachment_id', true );
										$field_value   = ! empty( $attachment_id ) ? wp_get_attachment_url( $attachment_id ) : '';
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
						// Application rating.
						$rating                                  = get_post_meta( $application_id, 'awsm_application_rating', true );
						$rating                                  = ! empty( $rating ) ? absint( $rating ) : '';
						$applicant_details['application_rating'] = $rating;

						// Application status.
						$post_status = get_post_status( $application_id );
						if ( method_exists( 'AWSM_Job_Openings_Pro_Main', 'get_application_status' ) ) {
							$available_status = AWSM_Job_Openings_Pro_Main::get_application_status();
							if ( isset( $available_status[ $post_status ] ) ) {
								$post_status = $available_status[ $post_status ]['label'];
							}
						}
						$applicant_details['application_status'] = $post_status;

						$data[] = $applicant_details;
					}

					/**
					 * Filters the export applications data.
					 *
					 * @since 2.0.1
					 *
					 * @param array $data Export data array.
					 * @param array $applications Applications array.
					 */
					$data = apply_filters( 'awsm_jobs_export_applications_data', $data, $applications );

					$file_name = sanitize_file_name( 'job-applications-' . current_time( 'Y-m-d' ) . '.csv' );
					header( 'Content-Encoding: UTF-8' );
					header( 'Content-Type: text/csv; charset=utf-8' );
					header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
					header( 'Pragma: no-cache' );
					header( 'Expires: 0' );
					$file = fopen( 'php://output', 'w' );
					foreach ( $data as $rows ) {
						fputcsv( $file, $rows );
					}
					exit;
				}
			} else {
				$redirect_url = add_query_arg(
					array(
						'page'     => self::$menu_slug,
						'awsm_err' => 1,
					),
					admin_url( 'edit.php?post_type=awsm_job_openings' )
				);
				wp_safe_redirect( $redirect_url );
				exit;
			}
		}
	}
}

AWSM_Job_Openings_Pro_Export::init();
