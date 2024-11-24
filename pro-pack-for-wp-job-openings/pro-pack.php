<?php
/**
 * Plugin Name: Pro Pack for WP Job Openings
 * Description: Converts WP Job Openings to a powerful recruitment tool by adding some of the most sought features.
 * Author: AWSM Innovations
 * Author URI: https://awsm.in/
 * Version: 3.4.0
 * Update URI: https://api.freemius.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text domain: pro-pack-for-wp-job-openings
 * Domain Path: /languages
 */

/**
 * Pro Pack for WP Job Openings
 *
 * Converts WP Job Openings to a powerful recruitment tool by adding some of the most sought features.
 *
 * @package wp-job-openings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Plugin Constants
if ( ! defined( 'AWSM_JOBS_MAIN_PLUGIN' ) ) {
	define( 'AWSM_JOBS_MAIN_PLUGIN', 'wp-job-openings/wp-job-openings.php' );
}
if ( ! defined( 'AWSM_JOBS_PRO_PLUGIN_BASENAME' ) ) {
	define( 'AWSM_JOBS_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'AWSM_JOBS_PRO_PLUGIN_DIR' ) ) {
	define( 'AWSM_JOBS_PRO_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
}
if ( ! defined( 'AWSM_JOBS_PRO_PLUGIN_URL' ) ) {
	define( 'AWSM_JOBS_PRO_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
}
if ( ! defined( 'AWSM_JOBS_PRO_PLUGIN_VERSION' ) ) {
	define( 'AWSM_JOBS_PRO_PLUGIN_VERSION', '3.4.0' );
}
if ( ! defined( 'AWSM_JOBS_MAIN_REQ_VERSION' ) ) {
	define( 'AWSM_JOBS_MAIN_REQ_VERSION', '2.0.0' );
}
if ( ! defined( 'AWSM_JOBS_MAIN_REC_VERSION' ) ) {
	define( 'AWSM_JOBS_MAIN_REC_VERSION', '3.3.0' );
}
if ( ! defined( 'AWSM_JOBS_PRO_DEBUG' ) ) {
	define( 'AWSM_JOBS_PRO_DEBUG', false );
}

// Helper functions.
require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/helper-functions.php';
require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/lib/fs-init.php';

if ( function_exists( 'register_block_type' ) ) {
	require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/blocks/class-awsm-job-openings-pro-block.php';
}

class AWSM_Job_Openings_Pro_Pack {
	private static $instance = null;

	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'remove_hooks' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'after_setup_theme', array( $this, 'template_functions' ), 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 100 );
		add_action( 'admin_init', array( $this, 'handle_plugin_activation' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'restrict_manage_posts', array( $this, 'admin_filters' ) );
		add_action( 'admin_head', array( $this, 'remove_submenu_pages' ) );
		add_action( 'admin_notices', array( $this, 'plugin_notices' ) );

		add_filter( 'plugin_action_links_' . AWSM_JOBS_PRO_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ), 100 );
		add_filter( 'awsm_jobs_shortcode_defaults', array( $this, 'jobs_shortcode_defaults' ) );
		add_filter( 'shortcode_atts_awsmjobs', array( $this, 'jobs_shortcode_atts' ) );
		add_filter( 'awsm_job_query_args', array( $this, 'jobs_query_args' ), 10, 3 );
		add_filter( 'awsm_job_block_query_args', array( $this, 'job_block_query_args' ), 10, 3 );
		add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'sitemaps_jobs_query_args' ), 10, 2 );
		add_filter( 'awsm_job_listing_item_class', array( $this, 'job_listing_item_class' ), 10, 2 );
		add_filter( 'awsm_job_block_listing_item_class', array( $this, 'job_block_listing_item_class' ), 10, 2 );
		add_filter( 'awsm_job_listing_data_attrs', array( $this, 'listing_data_attrs' ), 10, 2 );
		add_filter( 'awsm_job_structured_data', array( $this, 'structured_data' ) );
		add_action( 'after_awsm_job_details', array( $this, 'back_to_listings' ) );
		add_filter( 'display_post_states', array( $this, 'display_post_states' ), 10, 2 );
		add_filter( 'parse_query', array( $this, 'admin_filter_by_spec' ) );
		add_filter( 'parse_query', array( $this, 'admin_filter_by_filled' ), 100 );
		add_filter( 'awsm_jobs_localized_script_data', array( $this, 'localized_script_data' ) );
	}

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function load_classes() {
		require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-main.php';
		require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-form.php';
		require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-third-party.php';

		// Admin classes.
		if ( is_admin() ) {
			require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-overview.php';
			require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-meta.php';
			require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-export.php';
			require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-settings.php';
			require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/blocks/class-awsm-job-openings-pro-block.php';
		}
	}

	public function activate() {
		if ( ! class_exists( 'AWSM_Job_Openings' ) ) {
			update_option( 'awsm_jobs_pro_deactivated', 1 );
		} else {
			delete_option( 'awsm_jobs_pro_deactivated' );
		}

		if ( defined( 'AWSM_JOBS_PLUGIN_DIR' ) ) {
			if ( ! class_exists( 'AWSM_Job_Openings_Settings' ) ) {
				require_once AWSM_JOBS_PLUGIN_DIR . '/admin/class-awsm-job-openings-settings.php';
			}
			if ( ! class_exists( 'AWSM_Job_Openings_Pro_Settings' ) ) {
				require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-settings.php';
			}
			AWSM_Job_Openings_Pro_Settings::register_pro_defaults();
		}
	}

	public function deactivate() {
		delete_option( 'awsm_jobs_pro_deactivated' );
	}

	public static function log( $data, $prefix = '' ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG && defined( 'AWSM_JOBS_PRO_DEBUG' ) && AWSM_JOBS_PRO_DEBUG ) {
			if ( is_string( $data ) ) {
				error_log( 'WP Job Openings (PRO):' . $prefix . ': ' . $data );
			} else {
				error_log( 'WP Job Openings (PRO):' . $prefix . ': ' . json_encode( $data, JSON_PRETTY_PRINT ) );
			}
		}
	}

	public function plugins_loaded() {
		// load classes
		if ( class_exists( 'AWSM_Job_Openings' ) ) {
			self::load_classes();
		}
		// load translated strings
		load_plugin_textdomain( 'pro-pack-for-wp-job-openings', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	public function remove_hooks() {
		if ( class_exists( 'AWSM_Job_Openings' ) ) {
			remove_action( 'admin_notices', array( AWSM_Job_Openings::init(), 'plugin_rating_notice_handler' ) );
		}
	}

	public function plugin_notices() {
		$this->update_notice_handler();
		$this->plugin_rating_notice_handler();
	}

	public function update_notice_handler() {
		if ( current_user_can( 'install_plugins' ) ) {
			// show, update to recommended version notice.
			if ( defined( 'AWSM_JOBS_PLUGIN_VERSION' ) && version_compare( AWSM_JOBS_PLUGIN_VERSION, AWSM_JOBS_MAIN_REC_VERSION, '<' ) ) {
				$plugin_updates = get_site_transient( 'update_plugins' );
				if ( $plugin_updates && isset( $plugin_updates->response ) && isset( $plugin_updates->response[ AWSM_JOBS_MAIN_PLUGIN ] ) ) {
					$this->admin_notices( false, AWSM_JOBS_MAIN_REC_VERSION );
				}
			}
		}
	}

	public function plugin_rating_notice_handler() {
		$rated = intval( get_option( 'awsm_jobs_plugin_rating' ) );
		if ( class_exists( 'AWSM_Job_Openings' ) && $rated !== 1 ) {
			$rating_url = 'https://wordpress.org/support/plugin/wp-job-openings/reviews/?filter=5';
			$rating_env = 'WordPress';

			// Job Context.
			AWSM_Job_Openings::plugin_rating_notice( $rating_url, $rating_env );

			// Application context.
			AWSM_Job_Openings::plugin_rating_notice( $rating_url, $rating_env, 'application' );
		}
	}

	public function plugin_action_links( $actions ) {
		unset( $actions['activate-license pro-pack-for-wp-job-openings'] );
		if ( function_exists( 'awsm_jobs_pro_fs' ) ) {
			if ( class_exists( 'AWSM_Job_Openings' ) ) {
				$link_text = awsm_jobs_pro_fs()->is_free_plan() ? __( 'Activate License', 'pro-pack-for-wp-job-openings' ) : __( 'Manage License', 'pro-pack-for-wp-job-openings' );
				$link      = sprintf( '<a href="%1$s">%2$s</a>', esc_url( admin_url( 'edit.php?post_type=awsm_job_openings&page=awsm-jobs-settings&tab=license' ) ), esc_html( $link_text ) );
				array_unshift( $actions, $link );
			} else {
				unset( $actions['opt-in-or-opt-out pro-pack-for-wp-job-openings'] );
			}
		}
		return $actions;
	}

	public function remove_submenu_pages() {
		remove_submenu_page( 'edit.php?post_type=awsm_job_openings', 'awsm-jobs-settings-account' );
		remove_submenu_page( 'edit.php?post_type=awsm_job_openings', 'awsm-jobs-settings-contact' );
	}

	public function get_main_plugin_activation_link( $is_update = false ) {
		$content = $link_action = $action_url = $link_class = ''; // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found

		if ( ! $is_update ) {
			// when plugin is not active.
			$link_action = esc_html__( 'Activate', 'pro-pack-for-wp-job-openings' );
			$action_url  = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . AWSM_JOBS_MAIN_PLUGIN ), 'activate-plugin_' . AWSM_JOBS_MAIN_PLUGIN );
			$link_class  = ' activate-now';

			// when plugin is not installed.
			$plugin_arr       = explode( '/', esc_html( AWSM_JOBS_MAIN_PLUGIN ) );
			$plugin_slug      = $plugin_arr[0];
			$installed_plugin = get_plugins( '/' . $plugin_slug );
			if ( empty( $installed_plugin ) ) {
				if ( defined( 'WP_PLUGIN_DIR' ) && get_filesystem_method( array(), WP_PLUGIN_DIR ) === 'direct' ) {
					$link_action = esc_html__( 'Install', 'pro-pack-for-wp-job-openings' );
					$action_url  = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ), 'install-plugin_' . $plugin_slug );
					$link_class  = ' install-now';
				}
			}
		} else {
			// when plugin needs an update.
			$link_action = esc_html__( 'Update', 'pro-pack-for-wp-job-openings' );
			$action_url  = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' . AWSM_JOBS_MAIN_PLUGIN ), 'upgrade-plugin_' . AWSM_JOBS_MAIN_PLUGIN );
			$link_class  = ' update-now';
		}

		if ( ! empty( $link_action ) && ! empty( $action_url ) ) {
			$content = sprintf( '<a href="%2$s" class="button button-small%3$s">%1$s</a>', esc_html( $link_action ), esc_url( $action_url ), esc_attr( $link_class ) );
		}
		return $content;
	}

	public function admin_notices( $is_default = true, $req_plugin_version = AWSM_JOBS_MAIN_REQ_VERSION ) {
		?>
		<div class="updated error">
				<p>
					<?php
						$req_plugin = sprintf( '<strong>"%s"</strong>', esc_html__( 'WP Job Openings', 'pro-pack-for-wp-job-openings' ) );
						$plugin     = sprintf( '<strong>"%s"</strong>', esc_html__( 'Pro Pack for WP Job Openings', 'pro-pack-for-wp-job-openings' ) );
					if ( $is_default ) {
						/* translators: %1$s: main plugin, %2$s: current plugin, %3$s: plugin activation link, %4$s: line break */
						printf( esc_html__( 'The plugin %2$s needs the plugin %1$s active. %4$s Please %3$s %1$s', 'pro-pack-for-wp-job-openings' ), $req_plugin, $plugin, $this->get_main_plugin_activation_link(), '<br />' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						/* translators: %1$s: main plugin, %2$s: current plugin, %3$s: minimum required version of the main plugin, %4$s: plugin updation link */
						printf( esc_html__( '%2$s plugin requires %1$s version %3$s. Please %4$s %1$s plugin to the latest version.', 'pro-pack-for-wp-job-openings' ), $req_plugin, $plugin, esc_html( $req_plugin_version ), $this->get_main_plugin_activation_link( true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</p>
			</div>
		<?php
	}

	public function handle_plugin_activation() {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! class_exists( 'AWSM_Job_Openings' ) ) {
			add_action(
				'admin_notices',
				function() {
					$this->admin_notices();
				}
			);
			deactivate_plugins( AWSM_JOBS_PRO_PLUGIN_BASENAME );
		}
		if ( defined( 'AWSM_JOBS_PLUGIN_VERSION' ) ) {
			if ( version_compare( AWSM_JOBS_PLUGIN_VERSION, AWSM_JOBS_MAIN_REQ_VERSION, '<' ) ) {
				add_action(
					'admin_notices',
					function() {
						$this->admin_notices( false );
					}
				);
				deactivate_plugins( AWSM_JOBS_PRO_PLUGIN_BASENAME );
			}
		}
	}

	public function template_functions() {
		include_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/template-functions.php';
	}

	public function localized_script_data( $localized_script_data ) {
		$job_listing_strings = AWSM_Job_Openings_Pro_Main::$listing_strings;
		$form_strings        = AWSM_Job_Openings_Pro_Form::$form_strings;
		$validation_notices  = AWSM_Job_Openings_Pro_Form::$validation_notices;
		if ( ! isset( $job_listing_strings['_default'] ) && ! isset( $form_strings['_default'] ) && ! isset( $validation_notices['_default'] ) ) {
			$localized_script_data['i18n']['loading_text']                      = wp_strip_all_tags( $job_listing_strings['loading'] );
			$localized_script_data['i18n']['form_error_msg']['general']         = wp_strip_all_tags( $form_strings['error'] );
			$localized_script_data['i18n']['form_error_msg']['file_validation'] = wp_strip_all_tags( $validation_notices['file_size_default'] );
		}
		return $localized_script_data;
	}

	public function enqueue_scripts() {
		// Third-party libraries styles.
		wp_register_style( 'awsm-job-pro-flatpickr', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/css/flatpickr.min.css', array(), '4.6.9', 'all' );

		// Third-party libraries scripts.
		wp_register_script( 'awsm-job-pro-flatpickr', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/flatpickr.min.js', array(), '4.6.9', true );

		wp_register_style( 'awsm-job-pro-country-select', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/css/intlTelInput.min.css', array(), '17.0.16', 'all' );

		wp_register_script( 'awsm-job-pro-country-select', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/intlTelInput.min.js', array(), '17.0.16', true );

		// Main style and script.
		$style_deps                                        = array( 'awsm-jobs-style' );
		$script_deps                                       = array( 'jquery', 'awsm-job-scripts' );
		$localized_script_data                             = array();
		$style_deps[]                                      = 'awsm-job-pro-flatpickr';
		$script_deps[]                                     = 'awsm-job-pro-flatpickr';
		$localized_script_data['datepicker']               = 'default'; // default or custom.
		$style_deps[]                                      = 'awsm-job-pro-country-select';
		$script_deps[]                                     = 'awsm-job-pro-country-select';
		$localized_script_data['iti']['show_country_code'] = false;
		$localized_script_data['iti']['utils_url']         = AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/intlTelInput-utils.min.js';

		wp_enqueue_style( 'awsm-job-pro-style', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/css/style.min.css', $style_deps, AWSM_JOBS_PRO_PLUGIN_VERSION, 'all' );

		if ( class_exists( 'AWSM_Job_Openings_Pro_Form' ) ) {
			$awsm_jobs_form = AWSM_Job_Openings_Pro_Form::init();
			if ( $awsm_jobs_form->is_recaptcha_set() && $awsm_jobs_form->get_recaptcha_type() === 'v3' ) {
				$site_key          = get_option( 'awsm_jobs_recaptcha_site_key' );
				$recaptcha_api_url = "https://www.google.com/recaptcha/api.js?render={$site_key}";

				wp_dequeue_script( 'g-recaptcha' );
				wp_deregister_script( 'g-recaptcha' );
				wp_enqueue_script( 'g-recaptcha', esc_url( $recaptcha_api_url ), array(), '3.0', false );
				$script_deps[] = 'g-recaptcha';

				$localized_script_data['recaptcha'] = array(
					'action'   => 'applicationform',
					'site_key' => esc_attr( $site_key ),
				);
			}
		}

		// Main script.
		wp_enqueue_script( 'awsm-job-pro-scripts', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/script.min.js', $script_deps, AWSM_JOBS_PRO_PLUGIN_VERSION, true );

		$form_strings                  = AWSM_Job_Openings_Pro_Form::$form_strings;
		$validation_notices            = AWSM_Job_Openings_Pro_Form::$validation_notices;
		$localized_script_data['i18n'] = array(
			'repeater'    => array(
				'add_more' => esc_html_x( 'Add More', 'repeater', 'pro-pack-for-wp-job-openings' ),
				'edit'     => esc_html_x( 'Edit', 'repeater', 'pro-pack-for-wp-job-openings' ),
				'update'   => esc_html_x( 'Update', 'repeater', 'pro-pack-for-wp-job-openings' ),
				'remove'   => esc_html_x( 'Delete', 'repeater', 'pro-pack-for-wp-job-openings' ),
			),
			'file_upload' => array(
				'uploading'                  => wp_strip_all_tags( $form_strings['uploading_drag_and_drop'] ),
				'cancel_upload'              => esc_html_x( 'Cancel', 'file upload', 'pro-pack-for-wp-job-openings' ),
				'upload_canceled'            => esc_html_x( 'Upload canceled.', 'file upload', 'pro-pack-for-wp-job-openings' ),
				'cancel_upload_confirmation' => wp_strip_all_tags( $form_strings['cancel_uploading_drag_and_drop'] ),
				'remove_file'                => esc_html_x( 'Remove', 'file upload', 'pro-pack-for-wp-job-openings' ),
				'max_files'                  => wp_strip_all_tags( $validation_notices['max_files'] ),
				'invalid_file_type'          => wp_strip_all_tags( $validation_notices['file_type'] ),
				'file_size'                  => wp_strip_all_tags( $validation_notices['file_size_drag_and_drop'] ),
			),
		);
		/**
		 * Filters the public script localized data.
		 *
		 * @since 2.1.0
		 *
		 * @param array $localized_script_data Localized data array.
		 */
		$localized_script_data = apply_filters( 'awsm_jobs_pro_localized_script_data', $localized_script_data );
		wp_localize_script( 'awsm-job-pro-scripts', 'awsmProJobsPublic', $localized_script_data );
	}

	public static function admin_scripts_localized_data() {
		$localized_data = array(
			'nonce' => wp_create_nonce( 'awsm-pro-admin-nonce' ),
			'i18n'  => array(
				'download_file'       => esc_html__( 'Download File', 'wp-job-openings' ),
				'delete'              => esc_html__( 'Delete', 'pro-pack-for-wp-job-openings' ),
				'delete_confirmation' => esc_html__( 'Are you sure you want to delete this form?', 'pro-pack-for-wp-job-openings' ),
				'unsaved_data'        => esc_html__( 'The changes you made will be lost if you navigate away from this page.', 'default' ),
				'select_status'       => esc_html__( 'Please select a status', 'pro-pack-for-wp-job-openings' ),
				'move_application'    => esc_html__( 'Are you sure you want to move application to another job?', 'pro-pack-for-wp-job-openings' ),
			),
		);
		if ( function_exists( 'awsm_jobs_wp_editor_settings' ) ) {
			$localized_data['wp_editor_settings'] = awsm_jobs_wp_editor_settings();
		}
		return $localized_data;
	}

	public function admin_enqueue_scripts() {
		$screen    = get_current_screen();
		$post_type = false;
		if ( ! empty( $screen ) ) {
			$post_type = $screen->post_type;
		}
		wp_register_style( 'awsm-job-pro-admin-global', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/css/admin-global.min.css', array(), AWSM_JOBS_PRO_PLUGIN_VERSION, 'all' );
		wp_register_style( 'awsm-job-pro-admin', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/css/admin.min.css', array( 'awsm-job-admin', 'awsm-job-pro-admin-global' ), AWSM_JOBS_PRO_PLUGIN_VERSION, 'all' );
		wp_register_style( 'awsm-job-pro-admin-overview', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/css/admin-overview.min.css', array( 'awsm-job-pro-admin' ), AWSM_JOBS_PRO_PLUGIN_VERSION, 'all' );

		wp_register_script( 'awsm-job-pro-jspdf', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/jspdf.umd.min.js', array(), '2.1.1', true );
		wp_register_script( 'awsm-job-pro-notosans', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/notosans.js', array( 'awsm-job-pro-jspdf' ), '1.0.0', true );
		wp_register_script( 'chartjs-plugin-datalabels', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/chartjs-plugin-datalabels.min.js', array( 'chartjs' ), '2.0.0', true );

		$admin_deps = array( 'jquery', 'jquery-ui-sortable', 'awsm-job-admin', 'wp-util' );
		if ( $post_type && $post_type === 'awsm_job_application' && $screen->base === 'post' ) {
			$admin_deps[] = 'awsm-job-pro-jspdf';
			$admin_deps[] = 'awsm-job-pro-notosans';
		}
		wp_register_script( 'awsm-job-pro-admin', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/admin.min.js', $admin_deps, AWSM_JOBS_PRO_PLUGIN_VERSION, true );
		wp_register_script( 'awsm-job-pro-admin-overview', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/admin-overview.min.js', array( 'awsm-job-pro-admin', 'awsm-job-admin-overview', 'chartjs-plugin-datalabels' ), AWSM_JOBS_PRO_PLUGIN_VERSION, true );

		wp_enqueue_style( 'awsm-job-pro-admin-global' );
		if ( $post_type && ( $post_type === 'awsm_job_openings' || $post_type === 'awsm_job_application' || $post_type === 'awsm_job_form' ) ) {
			if ( isset( $_GET['page'] ) && $_GET['page'] === 'awsm-jobs-settings' ) {
				wp_enqueue_editor();
			}
			wp_enqueue_style( 'awsm-job-pro-admin' );
			wp_enqueue_script( 'awsm-job-pro-admin' );

			$status_options = get_option( 'awsm_jobs_application_status' );
			if ( is_array( $status_options ) ) {
				$inline_style       = '';
				$application_status = AWSM_Job_Openings_Pro_Main::get_application_status();
				foreach ( $application_status as $status => $status_option ) {
					$inline_style .= sprintf( '.awsm-application-%s-status { background-color: %s; } ', esc_attr( $status ), esc_attr( $status_option['color'] ) );
				}
				wp_add_inline_style( 'awsm-job-pro-admin', trim( $inline_style ) );
			}

			if ( class_exists( 'AWSM_Job_Openings_Overview' ) && $screen->id === AWSM_Job_Openings_Overview::$screen_id ) {
				wp_enqueue_style( 'awsm-job-pro-admin-overview' );
				wp_enqueue_script( 'awsm-job-pro-admin-overview' );
			}

			if ( $post_type === 'awsm_job_form' && $screen->base === 'post' ) {
				global $post;

				// Handle styles for default form.
				$default_form_id = AWSM_Job_Openings_Pro_Form::get_default_form_id();
				if ( isset( $post ) && $post->ID === $default_form_id ) {
					$inline_style = '#delete-action { display: none; } #misc-publishing-actions a { visibility: hidden; }';
					wp_add_inline_style( 'awsm-job-pro-admin', $inline_style );
				}
			}
		}

		wp_localize_script(
			'awsm-job-pro-admin',
			'awsmProJobsAdmin',
			self::admin_scripts_localized_data()
		);

		if ( class_exists( 'AWSM_Job_Openings_Pro_Overview' ) ) {
			wp_localize_script(
				'awsm-job-pro-admin-overview',
				'awsmProJobsAdminOverview',
				array(
					'applications_by_status_data' => AWSM_Job_Openings_Pro_Overview::get_applications_by_status_data(),
					'i18n'                        => array(),
				)
			);
		}
	}

	public function jobs_shortcode_defaults( $pairs ) {
		$pairs['status']  = 'default';
		$pairs['orderby'] = 'date';
		$pairs['order']   = 'DESC';
		return $pairs;
	}

	public function jobs_shortcode_atts( $out ) {
		if ( isset( $out['orderby'] ) && $out['orderby'] === 'rand' ) {
			$random_num     = wp_rand( 1, PHP_INT_MAX );
			$out['orderby'] = "rand({$random_num})";
		}
		return $out;
	}

	public static function get_job_meta_query( $key, $value ) {
		return array(
			'relation' => 'OR',
			array(
				'key'     => $key,
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => $key,
				'value'   => $value,
				'compare' => '!=',
			),
		);
	}

	public function job_block_query_args( $args, $filters, $attributes ) {
		$args['meta_query'][] = self::get_job_meta_query( 'awsm_pro_exclude_job', 'exclude' );
		if ( isset( $attributes['position_filling'] ) && $attributes['position_filling'] === 'filled' ) {
			$args['meta_query'][] = self::get_job_meta_query( 'awsm_job_filled', 'filled' );
		}
		return $args;
	}

	public function jobs_query_args( $args, $filters, $shortcode_atts ) {
		$spec_details = isset( $shortcode_atts['specs'] ) ? $shortcode_atts['specs'] : '';
		if ( strpos( $spec_details, '|orderby:' ) !== false ) {
			$shortcode_specs           = explode( '|orderby:', $spec_details );
			$spec_details              = $shortcode_specs[0];
			$orderby                   = explode( ' ', $shortcode_specs[1] );
			$shortcode_atts['orderby'] = $orderby[0];
			$shortcode_atts['order']   = $orderby[1];
		}

		if ( ! empty( $spec_details ) ) {
			$specs = explode( ',', $spec_details );
			foreach ( $specs as $spec ) {
				if ( strpos( $spec, ':' ) !== false ) {
					list( $taxonomy, $spec_terms ) = explode( ':', $spec );
					$args['tax_query'][]           = array(
						'taxonomy' => sanitize_text_field( $taxonomy ),
						'field'    => 'id',
						'terms'    => array_map( 'intval', explode( ' ', $spec_terms ) ),
					);
				}
			}
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['jstatus'] ) && ( $_POST['jstatus'] === 'publish' || $_POST['jstatus'] === 'expired' ) ) {
			$args['post_status'] = sanitize_text_field( $_POST['jstatus'] );
		}
		// phpcs:enable
		if ( isset( $shortcode_atts['status'] ) && ( $shortcode_atts['status'] === 'publish' || $shortcode_atts['status'] === 'expired' ) ) {
			$args['post_status'] = sanitize_text_field( $shortcode_atts['status'] );
		}

		if ( isset( $shortcode_atts['orderby'] ) && $shortcode_atts['orderby'] !== 'date' ) {
			$args['orderby'] = sanitize_text_field( $shortcode_atts['orderby'] );
		}
		if ( isset( $shortcode_atts['order'] ) ) {
			$args['order'] = sanitize_text_field( $shortcode_atts['order'] );
		}

		if ( ! isset( $args['meta_query'] ) ) {
			$args['meta_query'] = array();
		}
		$args['meta_query'][] = self::get_job_meta_query( 'awsm_pro_exclude_job', 'exclude' );

		$hide_filled = get_option( 'awsm_jobs_filled_jobs_listings' );
		if ( $hide_filled === 'filled' ) {
			$args['meta_query'][] = self::get_job_meta_query( 'awsm_job_filled', 'filled' );
		}

		return $args;
	}

	public function sitemaps_jobs_query_args( $args, $post_type ) {
		if ( $post_type === 'awsm_job_openings' ) {
			$hide_filled = get_option( 'awsm_jobs_filled_jobs_listings' );
			if ( $hide_filled === 'filled' ) {
				if ( ! isset( $args['meta_query'] ) ) {
					$args['meta_query'] = array();
				}
				$args['meta_query'][] = self::get_job_meta_query( 'awsm_job_filled', 'filled' );
			}
		}
		return $args;
	}

	public static function is_position_filled( $job_id = 0 ) {
		if ( empty( $job_id ) ) {
			global $post;
			$job_id = $post->ID;
		}
		$filled = get_post_meta( $job_id, 'awsm_job_filled', true );
		return $filled === 'filled';
	}

	public function job_listing_item_class( $classes, $job_id ) {
		if ( self::is_position_filled( $job_id ) ) {
			$classes[] = 'awsm-job-filled-item';
		}
		return $classes;
	}

	public function job_block_listing_item_class( $classes, $job_id ) {
		if ( self::is_position_filled( $job_id ) ) {
			$classes[] = 'awsm-b-job-filled-item';
		}
		return $classes;
	}

	public function listing_data_attrs( $attrs, $shortcode_atts = array() ) {
		if ( ! empty( $shortcode_atts ) ) {
			if ( isset( $shortcode_atts['status'] ) && ( $shortcode_atts['status'] === 'publish' || $shortcode_atts['status'] === 'expired' ) ) {
				$attrs['jstatus'] = sanitize_text_field( $shortcode_atts['status'] );
			}
		}
		return $attrs;
	}

	public function structured_data( $data ) {
		if ( self::is_position_filled() ) {
			$data = false;
		}
		return $data;
	}

	public function back_to_listings() {
		$default_page  = get_option( 'awsm_jobs_default_listing_page_id' );
		$selected_page = get_option( 'awsm_select_page_listing', $default_page );
		$enable_link   = get_option( 'awsm_jobs_back_to_listings' );
		if ( $enable_link === 'enable' ) {
			$job_detail_strings = AWSM_Job_Openings_Pro_Main::$job_detail_strings;
			$link_content       = sprintf( '<div class="awsm-jobs-pro-listings-link-container"><a href="%2$s" class="awsm-jobs-pro-listings-link">%1$s</a></div>', esc_html( $job_detail_strings['back_to_listings'] ), esc_url( get_page_link( $selected_page ) ) );
			/**
			 * Filters the back to job listings link HTML content.
			 *
			 * @since 2.0.0
			 *
			 * @param string $link_content Link HTML content.
			 */
			echo apply_filters( 'awsm_jobs_pro_back_to_listings_link_content', $link_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function display_post_states( $post_states, $post ) {
		if ( is_array( $post_states ) && $post->post_type === 'awsm_job_openings' && $post->post_status !== 'trash' ) {
			if ( self::is_position_filled( $post->ID ) ) {
				$post_states['awsm-jobs-pro'] = sprintf( '<span class="awsm-jobs-pro-post-state"><span>✔</span>%s</span>', esc_html__( 'Filled', 'pro-pack-for-wp-job-openings' ) );
			}
		}
		return $post_states;
	}

	public function admin_filters( $post_type ) {
		$enable_jobs_filters         = get_option( 'awsm_jobs_enable_admin_filters', 'enable' );
		$enable_applications_filters = get_option( 'awsm_applications_enable_admin_filters', 'enable' );

		if ( ( $post_type === 'awsm_job_openings' && $enable_jobs_filters === 'enable' ) || ( $post_type === 'awsm_job_application' && $enable_applications_filters === 'enable' ) ) {
			$default_filters = array( 'job-category', 'job-type', 'job-location' );
			if ( $post_type === 'awsm_job_openings' ) {
				$available_filters = get_option( 'awsm_jobs_available_filters', $default_filters );
			} elseif ( $post_type === 'awsm_job_application' ) {
				$available_filters = get_option( 'awsm_applications_available_filters', $default_filters );
			}

			$job_spec = array();
			if ( isset( $_GET['awsm_job_admin_filter'] ) ) {
				$job_spec = $_GET['awsm_job_admin_filter'];
			}

			$taxonomies = get_object_taxonomies( 'awsm_job_openings', 'objects' );
			if ( ! empty( $available_filters ) && ! empty( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy => $tax_details ) {
					if ( in_array( $taxonomy, $available_filters ) ) {
						$terms = get_terms( $taxonomy );
						if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
							$filter_content  = '<select name="awsm_job_admin_filter[' . esc_attr( $taxonomy ) . ']">';
							$filter_content .= '<option value="">' . esc_html( $tax_details->label ) . '</option>';
							foreach ( $terms as $term ) {
								$selected_option = isset( $job_spec[ $taxonomy ] ) ? $job_spec[ $taxonomy ] : '';
								$filter_content .= sprintf( '<option value="%1$d"%3$s>%2$s</option>', intval( $term->term_id ), esc_html( $term->name ), selected( $selected_option, $term->term_id, false ) );
							}
							$filter_content .= '</select>';
							/**
							 * Filters the admin filter content.
							 *
							 * @since 3.1.0
							 *
							 * @param string $filter_content Filter HTML content.
							 * @param string $taxonomy The taxonomy.
							 * @param string $post_type The post type.
							 */
							echo apply_filters( 'awsm_jobs_pro_admin_filter_content', $filter_content, $taxonomy, $post_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
				}
			}
		}

		$this->filled_jobs_admin_filter( $post_type );
	}

	public function admin_filter_by_spec( $query ) {
		global $pagenow;
		$type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
		if ( is_admin() && $pagenow === 'edit.php' && $query->is_main_query() && isset( $_GET['awsm_job_admin_filter'] ) && is_array( $_GET['awsm_job_admin_filter'] ) ) {
			$filters   = array();
			$job_specs = $_GET['awsm_job_admin_filter'];
			foreach ( $job_specs as $taxonomy => $term_id ) {
				$taxonomy             = sanitize_text_field( $taxonomy );
				$filters[ $taxonomy ] = intval( $term_id );
			}

			if ( ! empty( $filters ) ) {
				if ( $type === 'awsm_job_openings' ) {
					$tax_query = array();
					foreach ( $filters as $taxonomy => $term_id ) {
						if ( ! empty( $term_id ) ) {
							$spec        = array(
								'taxonomy' => $taxonomy,
								'field'    => 'term_id',
								'terms'    => $term_id,
							);
							$tax_query[] = $spec;
						}
					}
					if ( ! empty( $tax_query ) ) {
						$query->query_vars['tax_query'] = $tax_query;
					}
				} elseif ( $type === 'awsm_job_application' ) {
					$job_ids = $this->get_job_ids_by_tax( $filters );
					if ( ! empty( $job_ids ) ) {
						$query->query_vars['post_parent__in'] = $job_ids;
					}
				}
			}
		}
	}

	public function admin_filter_by_filled( $query ) {
		global $pagenow;
		$type = isset( $_GET['post_type'] ) ? $_GET['post_type'] : '';
		if ( $type === 'awsm_job_openings' && is_admin() && $pagenow === 'edit.php' && $query->is_main_query() && isset( $_GET['awsm_filled_jobs_filter'] ) ) {
			$filter_value = isset( $_GET['awsm_filled_jobs_filter'] ) ? sanitize_text_field( $_GET['awsm_filled_jobs_filter'] ) : '';
			if ( $filter_value === 'yes' || $filter_value === 'no' ) {
				$meta_query = array();
				if ( isset( $query->query_vars['meta_query'] ) && is_array( $query->query_vars['meta_query'] ) ) {
					$meta_query = $query->query_vars['meta_query'];
				}
				if ( $filter_value === 'yes' ) {
					$meta_query[] = array(
						'key'   => 'awsm_job_filled',
						'value' => 'filled',
					);
				} else {
					$meta_query[] = self::get_job_meta_query( 'awsm_job_filled', 'filled' );
				}
				$query->query_vars['meta_query'] = $meta_query;
			}
		}
	}

	public function get_job_ids_by_tax( $filters ) {
		$job_ids = array();
		if ( ! empty( $filters ) ) {
			$args = array();
			foreach ( $filters as $taxonomy => $term_id ) {
				if ( ! empty( $term_id ) ) {
					$spec                = array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term_id,
					);
					$args['tax_query'][] = $spec;
				}
			}
			if ( ! empty( $args ) ) {
				$args['post_type']   = 'awsm_job_openings';
				$args['numberposts'] = -1;
				$args['fields']      = 'ids';
				$job_ids             = get_posts( $args );
			}
		}
		return $job_ids;
	}

	public function filled_jobs_admin_filter( $post_type ) {
		if ( $post_type === 'awsm_job_openings' ) {
			$filled_filter = '';
			if ( isset( $_GET['awsm_filled_jobs_filter'] ) ) {
				$filled_filter = $_GET['awsm_filled_jobs_filter'];
			}
			$options = array(
				'yes' => esc_html__( 'Filled', 'pro-pack-for-wp-job-openings' ),
				'no'  => esc_html__( 'Not Filled', 'pro-pack-for-wp-job-openings' ),
			);

			echo "<select name='awsm_filled_jobs_filter'>";
			echo "<option value=''>" . esc_html__( 'Select Filled', 'pro-pack-for-wp-job-openings' ) . '</option>';
			foreach ( $options as $option => $label ) {
				$selected = '';
				if ( $filled_filter === $option ) {
					$selected = ' selected';
				}
				printf( '<option value="%1$s"%3$s>%2$s</option>', esc_html( $option ), esc_html( $label ), esc_attr( $selected ) );
			}
			echo '</select>';
		}
	}
}

// Initialize the class.
$awsm_pro_job_openings = AWSM_Job_Openings_Pro_Pack::init();

// Handle plugin activation.
register_activation_hook( __FILE__, array( $awsm_pro_job_openings, 'activate' ) );

// Handle plugin deactivation.
register_deactivation_hook( __FILE__, array( $awsm_pro_job_openings, 'deactivate' ) );
