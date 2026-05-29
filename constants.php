<?php
/**
 * Plugin-scope constants.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

// ============================================================================
// Admin & Settings
// ============================================================================

const SETTINGS_PAGE_SLUG = 'easy-g-maps';

// ============================================================================
// WordPress Options - prefix with OPT_
// ============================================================================

const OPT_API_KEY = 'egm_api_key';

// ============================================================================
// Shortcode & Block
// ============================================================================

const SHORTCODE_TAG = 'egm_map';
const BLOCK_NAME    = 'easy-g-maps/map';

// ============================================================================
// Map Defaults - prefix with DEF_
// ============================================================================

const DEF_ZOOM     = 14;
const DEF_HEIGHT   = '400px';
const DEF_WIDTH    = '100%';
const DEF_MAP_TYPE = 'roadmap';

// Allowed Google map type IDs.
const MAP_TYPES = array( 'roadmap', 'satellite', 'hybrid', 'terrain' );

// ============================================================================
// Google Maps JavaScript API
// ============================================================================

const GOOGLE_MAPS_API_URL   = 'https://maps.googleapis.com/maps/api/js';
const GOOGLE_MAPS_LIBRARIES = 'places';
const GOOGLE_MAPS_CALLBACK  = 'egmInitMaps';

// ============================================================================
// Asset Handles
// ============================================================================

const HANDLE_ADMIN  = 'easy-g-maps-admin';
const HANDLE_BLOCK  = 'easy-g-maps-block';
const HANDLE_PUBLIC = 'easy-g-maps';
const HANDLE_GOOGLE = 'easy-g-maps-google';

// ============================================================================
// Filter Hooks (public extension points)
// ============================================================================

/*
 * Invoked as literal strings (WordPress hook names should be literals so they
 * are greppable and the standards sniff can verify the prefix):
 *
 *   easy_g_maps_markers  ( array $markers, string $map_name, array $atts )
 *       Add or modify markers for a named map (multi-marker support).
 *
 *   easy_g_maps_map_args ( array $config, array $atts )
 *       Modify the full resolved map config before it is output.
 */
