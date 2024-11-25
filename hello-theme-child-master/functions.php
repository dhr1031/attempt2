<?php
/**
 * Theme functions and definitions
 *
 * @package HelloElementorChild
 */

/**
 * Load child theme css and optional scripts
 *
 * @return void
 */
function hello_elementor_child_enqueue_scripts() {
	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		'1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts', 20 );

/** Remove Call page from search results */

function ss_search_filter( $query ) {
    if ( !$query->is_admin && $query->is_search && $query->is_main_query() ) {
        $query->set( 'post__not_in', array( 3546, 3574 ) );
    }
}
add_action( 'pre_get_posts', 'ss_search_filter' );

/** Remove "All" prefix from search filter at top of job list */
function awsm_jobs_filter_label( $label ) {
	return str_replace( 'All ', '', $label );
}
add_filter( 'awsm_filter_label', 'awsm_jobs_filter_label' );

/** Custom validation on WP Openings Forms - Client-side.  Also ensure the scrips.min.js from WP Job Openings loads after custom script. */

function enqueue_custom_script() {
    // Enqueue your custom validation script
    wp_enqueue_script('wpopenings-validation', get_stylesheet_directory_uri() . '/wpopenings-validation.js', array('jquery'), '1.0.0', true);
}

add_action('wp_enqueue_scripts', 'enqueue_custom_script');

//function reorder_scripts() {
    // Dequeue the original script
//    wp_dequeue_script('awsm-job-scripts');

    // Enqueue your custom script first
//    wp_enqueue_script('wpopenings-validation', get_stylesheet_directory_uri() . '/wpopenings-validation.js', array('jquery'), '1.0.0', true);

    // Re-enqueue the original script after your custom script
//    wp_enqueue_script('awsm-job-scripts', plugins_url('wp-job-openings/assets/js/script.min.js'), array('jquery'), '3.5.0', true);
//}
//add_action('wp_enqueue_scripts', 'reorder_scripts', 999);



/** Custom validation on WP Openings Forms - Server-side  */

function custom_validate_application($response) {
    // Assuming the form data is available in $_POST
    $applicant_name = isset($_POST['awsm_applicant_name']) ? sanitize_text_field($_POST['awsm_applicant_name']) : '';

    // Custom validation for applicant name
    if (filter_var($applicant_name, FILTER_VALIDATE_EMAIL) || preg_match('/https?:\/\/[^\s]+/', $applicant_name)) {
        return array(
            'success' => false,
            'message' => __('The name field may not contain an email address or URL 001.', 'your-text-domain')
        );
    }

    return $response;
}
add_filter('aws_form_insert_application', 'custom_validate_application');

// Add this code to your child theme's functions.php file

function custom_awsm_application_validation() {
    if (!isset($_POST['awsm_applicant_name'])) {
        return;
    }

    $applicant_name = sanitize_text_field($_POST['awsm_applicant_name']);
    if (filter_var($applicant_name, FILTER_VALIDATE_EMAIL) || preg_match('/https?:\/\/[^\s]+/', $applicant_name)) {
        wp_send_json(array(
            'success' => false,
            'error' => __('The name field may not contain an email address or URL 002.', 'your-text-domain')
        ));
        exit;
    }
}
add_action('wp_ajax_nopriv_awsm_applicant_form_submission', 'custom_awsm_application_validation', 0);
add_action('wp_ajax_awsm_applicant_form_submission', 'custom_awsm_application_validation', 0);
