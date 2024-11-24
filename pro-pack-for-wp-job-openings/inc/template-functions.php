<?php
/**
 * Template specific functions
 *
 * @package wp-job-openings
 * @subpackage pro-pack-for-wp-job-openings
 * @since 3.0.0
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'awsm_jobs_data_attrs' ) ) {
	function awsm_jobs_data_attrs( $attrs = array(), $shortcode_atts = array() ) {
		$content = '';
		$attrs   = array_merge( AWSM_Job_Openings::get_job_listing_data_attrs( $shortcode_atts ), $attrs );
		if ( isset( $shortcode_atts['orderby'] ) && ( $shortcode_atts['orderby'] !== 'date' || $shortcode_atts['order'] !== 'DESC' ) ) {
			$attrs['specs'] .= '|orderby:' . $shortcode_atts['orderby'] . ' ' . $shortcode_atts['order'];
		}
		if ( ! empty( $attrs ) ) {
			foreach ( $attrs as $name => $value ) {
				if ( ! empty( $value ) ) {
					$content .= sprintf( ' data-%s="%s"', esc_attr( $name ), esc_attr( $value ) );
				}
			}
		}
		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
