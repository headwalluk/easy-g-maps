<?php
/**
 * Plugin orchestrator.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Main plugin class.
 *
 * Central orchestrator. All WordPress hooks are registered in {@see run()}.
 * Components (Settings, Admin_Hooks, Renderer, Frontend, Shortcode, Block) and
 * their hook wiring are added here as each milestone lands; component instances
 * are lazy-loaded via getters.
 */
class Plugin {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load the plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'easy-g-maps', false, EGM_NAME . '/languages' );
	}
}
