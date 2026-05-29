<?php
/**
 * Map container template.
 *
 * Outputs the element that the front-end script (assets/public/maps.js) turns
 * into a live Google map. Override by copying to your theme as
 * easy-g-maps/map.php.
 *
 * @package EasyGMaps
 *
 * @var array<string, mixed> $args Provided by Renderer::load_template() with
 *                                 'config' (map config) and 'atts' (attributes).
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

$egm_config = isset( $args['config'] ) ? $args['config'] : array();
$egm_atts   = isset( $args['atts'] ) ? $args['atts'] : array();

$egm_height = isset( $egm_atts['height'] ) ? strval( $egm_atts['height'] ) : DEF_HEIGHT;
$egm_width  = isset( $egm_atts['width'] ) ? strval( $egm_atts['width'] ) : DEF_WIDTH;

printf(
	'<div class="egm-map" style="%s" data-egm-map="%s"></div>',
	esc_attr( sprintf( 'height:%s;width:%s;', $egm_height, $egm_width ) ),
	esc_attr( strval( wp_json_encode( $egm_config ) ) )
);
