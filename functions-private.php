<?php
/**
 * Plugin-scope functions.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Get a handle to the core plugin object.
 *
 * @return Plugin
 */
function get_plugin(): Plugin {
	global $egm_plugin;
	return $egm_plugin;
}

/**
 * Get a handle to the plugin's settings controller.
 *
 * @return Settings
 */
function get_settings_controller(): Settings {
	global $egm_plugin;
	return $egm_plugin->get_settings_controller();
}

/**
 * Get the current timestamp in human-readable format.
 *
 * @param string $format DateTime format string.
 *
 * @return string Formatted timestamp (e.g. "2026-05-29 14:03:21 BST").
 */
function egm_get_now_h( string $format = 'Y-m-d H:i:s T' ): string {
	$now = new \DateTime( 'now', wp_timezone() );
	return $now->format( $format );
}
