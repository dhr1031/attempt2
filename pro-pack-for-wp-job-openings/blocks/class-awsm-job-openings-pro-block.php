<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AWSM_Job_Openings_Pro_Block {
	private static $instance = null;

	public function __construct() {
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_assets_pro' ) );
		add_action( 'enqueue_block_assets', array( $this, 'block_assets_pro' ) );
		add_filter( 'block_type_metadata', array( $this, 'add_custom_block_attributes' ) );
		add_filter( 'awsm_jobs_listings_block_attributes', array( $this, 'block_render_customise' ) );
		add_filter( 'awsm_jobs_block_attributes_set', array( $this, 'block_attrs_set' ), 10, 3 );
		add_filter( 'awsm_block_job_listing_data_attrs', array( $this, 'block_job_listing_data_attrs' ), 10, 2 );
		add_filter( 'awsm_jobs_block_post_filters', array( $this, 'block_post_filters' ), 10, 2 );
		add_filter( 'awsm_jobs_block_featured_image_content', array( $this, 'block_featured_image_content' ), 10, 3 );
	}

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function block_editor_assets_pro() {
		wp_enqueue_script( 'awsm-pro-block-job-admin', plugins_url( 'blocks/build/index.js', dirname( __FILE__ ) ), array( 'wp-blocks', 'wp-element', 'wp-data', 'wp-components', 'wp-editor', 'wp-i18n' ), '1.0.0', true );
	}

	public function block_assets_pro() {
		wp_enqueue_script( 'awsm-job-block-pro-scripts', AWSM_JOBS_PRO_PLUGIN_URL . '/assets/js/public/job-block.js', array( 'jquery' ), AWSM_JOBS_PRO_PLUGIN_VERSION, true );
	}

	public function add_custom_block_attributes( $metadata ) {
		if ( $metadata['name'] === 'wp-job-openings/blocks' ) {
			$metadata['attributes']['position_filling'] = array(
				'type'    => 'boolean',
				'default' => false,
			);

			$metadata['attributes']['featured_image_size'] = array(
				'type'    => 'string',
				'default' => '',
			);
		}

		return $metadata;
	}

	public function block_render_customise( $atts ) {
		if ( isset( $atts['position_filling'] ) && $atts['position_filling'] === true ) {
			$atts['position_filling'] = 'filled';
		}
		return $atts;
	}

	public function block_attrs_set( $block_atts_set, $blockatts ) {
		if ( isset( $blockatts['position_filling'] ) ) {
			$block_atts_set['position_filling'] = $blockatts['position_filling'];
		}
		if ( isset( $blockatts['featured_image_size'] ) ) {
			$block_atts_set['featured_image_size'] = $blockatts['featured_image_size'];
		}
		return $block_atts_set;
	}

	public function block_job_listing_data_attrs( $attrs, $block_atts ) {
		$attrs['position_filling']    = isset( $block_atts['position_filling'] ) ? $block_atts['position_filling'] : '';
		$attrs['featured_image_size'] = isset( $block_atts['featured_image_size'] ) ? $block_atts['featured_image_size'] : '';
		return $attrs;
	}

	public function block_post_filters( $attributes, $data ) {
		if ( isset( $data['position_filling'] ) ) {
			$attributes['position_filling'] = $data['position_filling'];
		}

		if ( isset( $data['featured_image_size'] ) ) {
			$attributes['featured_image_size'] = $data['featured_image_size'];
		}
		return $attributes;
	}

	public function block_featured_image_content( $content, $post_thumbnail_id, $block_atts ) {
		if ( ! empty( $content ) ) {
			$image_size = 'thumbnail';
			if ( isset( $attributes['featured_image_size'] ) && $attributes['featured_image_size'] != '' ) {
				$image_size = $attributes['featured_image_size'];
			}

			if ( ! empty( $image_size ) && $image_size !== 'thumbnail' && ! is_singular( 'awsm_job_openings' ) ) {
				$content = wp_get_attachment_image( $post_thumbnail_id, $image_size );
			}
		}
		return $content;
	}

}

AWSM_Job_Openings_Pro_Block::get_instance();
