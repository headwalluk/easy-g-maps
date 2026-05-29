<?php
/**
 * The easy-g-maps/map block (dynamic, no build step).
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Registers and renders the Easy G Map block.
 *
 * Dynamic block: both the editor preview (via ServerSideRender) and the front
 * end render through {@see render()}, which reuses the shared Renderer so the
 * output always matches the [egm_map] shortcode.
 */
class Block {

	/**
	 * Register the block type with its render callback.
	 *
	 * Registered on `init` in both web and REST contexts so ServerSideRender
	 * can resolve the block.
	 *
	 * @return void
	 */
	public function register(): void {
		register_block_type(
			BLOCK_NAME,
			array(
				'api_version'     => 2,
				'attributes'      => $this->get_attributes(),
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	/**
	 * Block attribute schema (camelCase, mirrors the shortcode attributes).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_attributes(): array {
		return array(
			'mapName'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'address'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'lat'         => array(
				'type'    => 'string',
				'default' => '',
			),
			'lng'         => array(
				'type'    => 'string',
				'default' => '',
			),
			'placeId'     => array(
				'type'    => 'string',
				'default' => '',
			),
			'zoom'        => array(
				'type'    => 'number',
				'default' => DEF_ZOOM,
			),
			'height'      => array(
				'type'    => 'string',
				'default' => DEF_HEIGHT,
			),
			'mapType'     => array(
				'type'    => 'string',
				'default' => DEF_MAP_TYPE,
			),
			'marker'      => array(
				'type'    => 'boolean',
				'default' => true,
			),
			'markerTitle' => array(
				'type'    => 'string',
				'default' => '',
			),
			'markerText'  => array(
				'type'    => 'string',
				'default' => '',
			),
		);
	}

	/**
	 * Render the block by mapping its attributes onto the shared renderer.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 *
	 * @return string
	 */
	public function render( array $attributes ): string {
		$show_marker = ! isset( $attributes['marker'] ) || (bool) $attributes['marker'];

		$atts = array(
			'map_name'     => isset( $attributes['mapName'] ) ? $attributes['mapName'] : '',
			'address'      => isset( $attributes['address'] ) ? $attributes['address'] : '',
			'lat'          => isset( $attributes['lat'] ) ? $attributes['lat'] : '',
			'lng'          => isset( $attributes['lng'] ) ? $attributes['lng'] : '',
			'place_id'     => isset( $attributes['placeId'] ) ? $attributes['placeId'] : '',
			'zoom'         => isset( $attributes['zoom'] ) ? $attributes['zoom'] : DEF_ZOOM,
			'height'       => isset( $attributes['height'] ) ? $attributes['height'] : DEF_HEIGHT,
			'map_type'     => isset( $attributes['mapType'] ) ? $attributes['mapType'] : DEF_MAP_TYPE,
			'marker'       => $show_marker ? 'yes' : 'no',
			'marker_title' => isset( $attributes['markerTitle'] ) ? $attributes['markerTitle'] : '',
			'marker_text'  => isset( $attributes['markerText'] ) ? $attributes['markerText'] : '',
		);

		return get_renderer()->render( $atts );
	}
}
