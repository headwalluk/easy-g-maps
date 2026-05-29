<?php
/**
 * Front-end hooks and map asset loading.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Registers and lazily enqueues the public map assets.
 */
class Frontend {

	/**
	 * Whether the map assets have already been enqueued this request.
	 *
	 * @var bool
	 */
	private bool $assets_enqueued = false;

	/**
	 * Register the public assets (enqueued on demand when a map renders).
	 *
	 * @return void
	 */
	public function register_assets(): void {
		wp_register_style(
			HANDLE_PUBLIC,
			EGM_ASSETS_URL . 'public/maps.css',
			array(),
			EGM_VERSION
		);

		wp_register_script(
			HANDLE_PUBLIC,
			EGM_ASSETS_URL . 'public/maps.js',
			array(),
			EGM_VERSION,
			array( 'in_footer' => true )
		);
	}

	/**
	 * Enqueue the map assets and the Google Maps loader.
	 *
	 * Safe to call repeatedly; only acts once per request. Called by the
	 * renderer when a map is actually placed on the page, so non-map pages
	 * never load Google Maps.
	 *
	 * @return void
	 */
	public function enqueue_map_assets(): void {
		if ( $this->assets_enqueued ) {
			return;
		}
		$this->assets_enqueued = true;

		// Register on demand too, in case wp_enqueue_scripts has not run yet
		// (e.g. a block server-side render in the editor).
		if ( ! wp_script_is( HANDLE_PUBLIC, 'registered' ) ) {
			$this->register_assets();
		}

		wp_enqueue_style( HANDLE_PUBLIC );
		wp_enqueue_script( HANDLE_PUBLIC );

		$api_key = get_settings_controller()->get_api_key();

		if ( '' === $api_key ) {
			return;
		}

		$google_src = add_query_arg(
			array(
				'key'       => $api_key,
				'libraries' => GOOGLE_MAPS_LIBRARIES,
				'callback'  => GOOGLE_MAPS_CALLBACK,
				'loading'   => 'async',
			),
			GOOGLE_MAPS_API_URL
		);

		// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion -- The Google Maps loader is versioned by Google, not by us.
		wp_enqueue_script(
			HANDLE_GOOGLE,
			$google_src,
			array( HANDLE_PUBLIC ),
			null,
			array(
				'in_footer' => true,
				'strategy'  => 'async',
			)
		);
		// phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion
	}
}
