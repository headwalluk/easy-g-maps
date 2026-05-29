<?php
/**
 * Map renderer - shared by the shortcode and the block.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Builds the map configuration and renders the map container.
 *
 * Both the [egm_map] shortcode and the easy-g-maps/map block funnel through
 * {@see render()} so they always produce identical markup.
 */
class Renderer {

	/**
	 * Auto-increment index for maps rendered without an explicit name.
	 *
	 * @var int
	 */
	private static int $auto_index = 0;

	/**
	 * Render a map from a set of attributes.
	 *
	 * @param array<string, mixed> $atts Raw shortcode/block attributes.
	 *
	 * @return string Map container HTML.
	 */
	public function render( array $atts ): string {
		$atts = $this->normalise_atts( $atts );

		$markers = $this->build_markers( $atts );

		/**
		 * Filter the markers for a map.
		 *
		 * Lets a PHP snippet add markers to a named map (multi-marker support).
		 *
		 * @param array<int, array<string, mixed>> $markers  The markers array.
		 * @param string                           $map_name The map's name.
		 * @param array<string, mixed>             $atts     Normalised attributes.
		 */
		$markers = apply_filters( 'easy_g_maps_markers', $markers, $atts['map_name'], $atts );

		$config = array(
			'mapName' => $atts['map_name'],
			'options' => array(
				'zoom'      => intval( $atts['zoom'] ),
				'mapTypeId' => $atts['map_type'],
				'center'    => $this->build_center( $atts ),
			),
			'markers' => array_values( (array) $markers ),
		);

		/**
		 * Filter the full map configuration before output.
		 *
		 * @param array<string, mixed> $config The map config (JSON-encoded into the container).
		 * @param array<string, mixed> $atts   Normalised attributes.
		 */
		$config = apply_filters( 'easy_g_maps_map_args', $config, $atts );

		// Make sure the front-end assets are loaded for this page.
		get_plugin()->get_frontend()->enqueue_map_assets();

		return $this->load_template( $config, $atts );
	}

	/**
	 * Apply defaults and normalise raw attributes.
	 *
	 * @param array<string, mixed> $atts Raw attributes.
	 *
	 * @return array<string, mixed> Normalised attributes.
	 */
	private function normalise_atts( array $atts ): array {
		$defaults = array(
			'map_name'     => '',
			'address'      => '',
			'lat'          => '',
			'lng'          => '',
			'place_id'     => '',
			'zoom'         => strval( DEF_ZOOM ),
			'height'       => DEF_HEIGHT,
			'width'        => DEF_WIDTH,
			'map_type'     => DEF_MAP_TYPE,
			'marker'       => 'yes',
			'marker_title' => '',
			'marker_text'  => '',
			'marker_color' => '',
		);

		$atts = shortcode_atts( $defaults, $atts, SHORTCODE_TAG );

		// Auto-name unnamed maps so marker filters can target them.
		if ( '' === trim( strval( $atts['map_name'] ) ) ) {
			++self::$auto_index;
			$atts['map_name'] = 'egm-map-' . self::$auto_index;
		}

		// Constrain the map type to the allowed set.
		if ( ! in_array( $atts['map_type'], MAP_TYPES, true ) ) {
			$atts['map_type'] = DEF_MAP_TYPE;
		}

		// Bare numbers become pixel values.
		$atts['height'] = $this->normalise_dimension( strval( $atts['height'] ) );
		$atts['width']  = $this->normalise_dimension( strval( $atts['width'] ) );

		return $atts;
	}

	/**
	 * Turn a bare number into a pixel value, leaving CSS units untouched.
	 *
	 * @param string $value Dimension value.
	 *
	 * @return string
	 */
	private function normalise_dimension( string $value ): string {
		$value = trim( $value );

		if ( is_numeric( $value ) ) {
			$value .= 'px';
		}

		return $value;
	}

	/**
	 * Build the explicit map centre from lat/lng, if both are present.
	 *
	 * @param array<string, mixed> $atts Normalised attributes.
	 *
	 * @return array<string, float>|null
	 */
	private function build_center( array $atts ): ?array {
		$center = null;

		if ( '' !== trim( strval( $atts['lat'] ) ) && '' !== trim( strval( $atts['lng'] ) ) ) {
			$center = array(
				'lat' => floatval( $atts['lat'] ),
				'lng' => floatval( $atts['lng'] ),
			);
		}

		return $center;
	}

	/**
	 * Build the (single) marker array from the attributes.
	 *
	 * Produces a one-element array (or empty); the HOOK_MARKERS filter can add
	 * more markers for the named map.
	 *
	 * @param array<string, mixed> $atts Normalised attributes.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function build_markers( array $atts ): array {
		$markers = array();

		$has_coords   = '' !== trim( strval( $atts['lat'] ) ) && '' !== trim( strval( $atts['lng'] ) );
		$has_address  = '' !== trim( strval( $atts['address'] ) );
		$has_place_id = '' !== trim( strval( $atts['place_id'] ) );
		$show_marker  = filter_var( $atts['marker'], FILTER_VALIDATE_BOOLEAN );

		if ( $show_marker && ( $has_coords || $has_address || $has_place_id ) ) {
			$marker = array();

			if ( $has_coords ) {
				$marker['lat'] = floatval( $atts['lat'] );
				$marker['lng'] = floatval( $atts['lng'] );
			}
			if ( $has_address ) {
				$marker['address'] = strval( $atts['address'] );
			}
			if ( $has_place_id ) {
				$marker['placeId'] = strval( $atts['place_id'] );
			}
			if ( '' !== trim( strval( $atts['marker_title'] ) ) ) {
				$marker['title'] = strval( $atts['marker_title'] );
			}
			if ( '' !== trim( strval( $atts['marker_text'] ) ) ) {
				$marker['text'] = strval( $atts['marker_text'] );
			}
			if ( '' !== trim( strval( $atts['marker_color'] ) ) ) {
				$marker['color'] = strval( $atts['marker_color'] );
			}

			$markers[] = $marker;
		}

		return $markers;
	}

	/**
	 * Render the map container template, honouring theme overrides.
	 *
	 * @param array<string, mixed> $config Map configuration.
	 * @param array<string, mixed> $atts   Normalised attributes.
	 *
	 * @return string
	 */
	private function load_template( array $config, array $atts ): string {
		$template_name  = 'map.php';
		$theme_template = locate_template( 'easy-g-maps/' . $template_name );
		$template       = '' !== $theme_template ? $theme_template : EGM_PUBLIC_TEMPLATES_DIR . $template_name;

		$args = array(
			'config' => $config,
			'atts'   => $atts,
		);

		ob_start();
		if ( is_readable( $template ) ) {
			include $template;
		}

		return strval( ob_get_clean() );
	}
}
