<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AWSM_Job_Openings_Pro_Third_Party {
	private static $instance = null;

	protected $cpath = null;

	public function __construct() {
		$this->cpath = untrailingslashit( plugin_dir_path( __FILE__ ) );

		$this->multilingual_support();

		$this->filled_jobs_support();
	}

	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function multilingual_support() {
		// WPML and Polylang support.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) || defined( 'POLYLANG_VERSION' ) ) {
			require_once $this->cpath . '/translation/class-awsm-job-openings-pro-wpml.php';
		}
	}

	public function filled_jobs_support() {
		$hide_filled = get_option( 'awsm_jobs_filled_jobs_listings' );
		if ( $hide_filled === 'filled' ) {
			// Yoast SEO support.
			add_filter( 'wpseo_sitemap_entry', array( $this, 'sitemap_entry' ), 10, 3 );
			// Rank Math SEO support.
			add_filter( 'rank_math/sitemap/entry', array( $this, 'sitemap_entry' ), 10, 3 );
			// All in One SEO support.
			add_filter( 'aioseo_sitemap_posts', array( $this, 'sitemap_posts' ), 10, 2 );
		}
	}

	public function sitemap_entry( $url, $type, $post ) {
		if ( $type === 'post' && $post->post_type === 'awsm_job_openings' ) {
			if ( AWSM_Job_Openings_Pro_Pack::is_position_filled( $post->ID ) ) {
				$url = '';
			}
		}
		return $url;
	}

	public function sitemap_posts( $entries, $post_type ) {
		if ( $post_type === 'awsm_job_openings' ) {
			foreach ( $entries as $index => $entry ) {
				$job_id = url_to_postid( $entry['loc'] );
				if ( ! empty( $job_id ) && AWSM_Job_Openings_Pro_Pack::is_position_filled( $job_id ) ) {
					unset( $entries[ $index ] );
				}
			}
		}
		return $entries;
	}
}

AWSM_Job_Openings_Pro_Third_Party::init();
