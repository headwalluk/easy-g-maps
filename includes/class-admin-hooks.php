<?php
/**
 * Admin-area hooks.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Handles admin-area asset loading.
 */
class Admin_Hooks {

	/**
	 * Enqueue admin assets on the plugin settings page only.
	 *
	 * @param string $current_page The current admin page hook suffix.
	 *
	 * @return void
	 */
	public function enqueue_scripts( string $current_page ): void {
		if ( 'settings_page_' . SETTINGS_PAGE_SLUG !== $current_page ) {
			return;
		}

		wp_enqueue_style(
			HANDLE_ADMIN,
			EGM_ASSETS_URL . 'admin/admin.css',
			array(),
			EGM_VERSION
		);

		wp_enqueue_script(
			HANDLE_ADMIN,
			EGM_ASSETS_URL . 'admin/admin.js',
			array(),
			EGM_VERSION,
			true
		);

		wp_localize_script(
			HANDLE_ADMIN,
			'egmAdmin',
			array(
				'sampleAddress' => 'Trafalgar Square, London, UK',
				'i18n'          => array(
					'noKey'        => __( 'Enter an API key first, then click Test key.', 'easy-g-maps' ),
					'testing'      => __( 'Loading Google Maps and checking APIs...', 'easy-g-maps' ),
					'mapsOk'       => __( 'Maps JavaScript API: working', 'easy-g-maps' ),
					'geoOk'        => __( 'Geocoding API: working', 'easy-g-maps' ),
					'geoDenied'    => __( 'Geocoding API: request denied - check the Geocoding API is enabled and this domain is allowed by the key\'s referrer restrictions.', 'easy-g-maps' ),
					'geoErr'       => __( 'Geocoding API: error', 'easy-g-maps' ),
					'placesOk'     => __( 'Places API: working', 'easy-g-maps' ),
					'placesDenied' => __( 'Places API: request denied - check the Places API is enabled and this domain is allowed by the key\'s referrer restrictions.', 'easy-g-maps' ),
					'placesErr'    => __( 'Places API: error', 'easy-g-maps' ),
					'authFail'     => __( 'Authentication failed - the key is invalid, or this domain is not allowed by the key\'s HTTP referrer restrictions.', 'easy-g-maps' ),
					'authHint'     => __( 'Check the key and its referrer restrictions in Google Cloud Console, then reload this page and test again.', 'easy-g-maps' ),
					'loadError'    => __( 'Could not load the Google Maps script. Check your network connection and the key.', 'easy-g-maps' ),
					'staleKey'     => __( 'Note: Google Maps was already loaded on this page with an earlier key. Reload the page to test a changed key.', 'easy-g-maps' ),
					'timeout'      => __( 'Timed out waiting for Google Maps. Check the key, its referrer restrictions, and your network.', 'easy-g-maps' ),
				),
			)
		);
	}

	/**
	 * Enqueue the block editor assets.
	 *
	 * Loads the block script plus the front-end map assets, so the block's
	 * ServerSideRender preview becomes a live map in the editor.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets(): void {
		wp_enqueue_script(
			HANDLE_BLOCK,
			EGM_ASSETS_URL . 'admin/block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n' ),
			EGM_VERSION,
			true
		);

		wp_localize_script(
			HANDLE_BLOCK,
			'egmBlock',
			array(
				'hasKey' => '' !== get_settings_controller()->get_api_key(),
			)
		);

		// Load the map assets so the editor preview renders a live map.
		get_frontend()->enqueue_map_assets();
	}
}
