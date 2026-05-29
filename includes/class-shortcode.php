<?php
/**
 * The [egm_map] shortcode.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Registers and renders the [egm_map] shortcode.
 *
 * Supported attributes: map_name, address, lat, lng, place_id, zoom, height,
 * width, map_type, marker, marker_title, marker_text, marker_color.
 */
class Shortcode {

	/**
	 * Register the shortcode.
	 *
	 * @return void
	 */
	public function register(): void {
		add_shortcode( SHORTCODE_TAG, array( $this, 'render' ) );
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array<string, mixed>|string $atts Shortcode attributes ('' when none given).
	 *
	 * @return string
	 */
	public function render( $atts ): string {
		$atts = is_array( $atts ) ? $atts : array();

		return get_renderer()->render( $atts );
	}
}
