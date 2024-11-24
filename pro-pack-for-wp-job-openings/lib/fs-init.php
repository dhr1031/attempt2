<?php

/**
 * SDK Integration.
 *
 * @package wp-job-openings
 */
$is_plugin_deactivated = intval( get_option( 'awsm_jobs_pro_deactivated' ) );
if ( $is_plugin_deactivated !== 1 && !function_exists( 'awsm_jobs_pro_fs' ) ) {
    function awsm_jobs_pro_fs() {
        global $awsm_jobs_pro_fs;
        if ( !isset( $awsm_jobs_pro_fs ) ) {
            // Include Freemius SDK.
            require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/lib/freemius/start.php';
            $fs_secret_key = ( defined( 'AWSM_JOBS_PRO_FS_SECRET_KEY' ) ? AWSM_JOBS_PRO_FS_SECRET_KEY : '' );
            $awsm_jobs_pro_fs = fs_dynamic_init( array(
                'id'               => '6983',
                'slug'             => 'pro-pack-for-wp-job-openings',
                'premium_slug'     => 'pro-pack-for-wp-job-openings',
                'type'             => 'plugin',
                'public_key'       => 'pk_6a0040329e377a4898234a800e108',
                'is_premium'       => true,
                'is_premium_only'  => true,
                'has_addons'       => false,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'navigation'       => 'tabs',
                'menu'             => array(
                    'slug'           => 'awsm-jobs-settings',
                    'override_exact' => true,
                    'support'        => false,
                    'parent'         => array(
                        'slug' => 'edit.php?post_type=awsm_job_openings',
                    ),
                ),
                'is_live'          => true,
            ) );
        }
        return $awsm_jobs_pro_fs;
    }

    // Init Freemius.
    awsm_jobs_pro_fs();
    // Signal that SDK was initiated.
    do_action( 'awsm_jobs_pro_fs_loaded' );
    function awsm_jobs_pro_fs_uninstall_cleanup() {
        if ( get_option( 'awsm_delete_data_on_uninstall' ) !== 'delete_data' ) {
            return;
        }
        require_once AWSM_JOBS_PRO_PLUGIN_DIR . '/inc/class-awsm-job-openings-pro-uninstall.php';
        if ( class_exists( 'AWSM_Job_Openings_Pro_Uninstall' ) ) {
            AWSM_Job_Openings_Pro_Uninstall::pro_uninstall();
        }
    }

    function awsm_jobs_pro_fs_is_submenu_visible(  $is_visible, $submenu_id  ) {
        if ( $submenu_id === 'pricing' ) {
            $is_visible = false;
        }
        return $is_visible;
    }

    function awsm_jobs_pro_fs_settings_url() {
        return admin_url( 'edit.php?post_type=awsm_job_openings&page=awsm-jobs-settings&tab=license' );
    }

    function awsm_jobs_pro_fs_show_admin_notice(  $show  ) {
        if ( !class_exists( 'AWSM_Job_Openings' ) ) {
            $show = false;
        }
        return $show;
    }

    awsm_jobs_pro_fs()->add_action( 'after_uninstall', 'awsm_jobs_pro_fs_uninstall_cleanup' );
    awsm_jobs_pro_fs()->add_filter(
        'is_submenu_visible',
        'awsm_jobs_pro_fs_is_submenu_visible',
        10,
        2
    );
    awsm_jobs_pro_fs()->add_filter( 'connect_url', 'awsm_jobs_pro_fs_settings_url' );
    awsm_jobs_pro_fs()->add_filter( 'after_skip_url', 'awsm_jobs_pro_fs_settings_url' );
    awsm_jobs_pro_fs()->add_filter( 'after_connect_url', 'awsm_jobs_pro_fs_settings_url' );
    awsm_jobs_pro_fs()->add_filter( 'after_pending_connect_url', 'awsm_jobs_pro_fs_settings_url' );
    awsm_jobs_pro_fs()->add_filter( 'show_admin_notice', 'awsm_jobs_pro_fs_show_admin_notice' );
}