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

function validate_awsm_applicant_name_field($errors, $data) {
    if (!empty($data['awsm_applicant_name']) && filter_var($data['awsm_applicant_name'], FILTER_VALIDATE_EMAIL)) {
        $errors->add('awsm_applicant_name_error', __('The name field should not contain an email address.'));
    }
    return $errors;
}
add_filter('wp_job_openings_validate_application_form', 'validate_awsm_applicant_name_field', 10, 2);
