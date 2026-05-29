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
 * Component instances are lazy-loaded via getters and wired in per milestone.
 */
class Plugin {

	/**
	 * Settings controller.
	 *
	 * @var Settings|null
	 */
	private ?Settings $settings = null;

	/**
	 * Admin hooks handler.
	 *
	 * @var Admin_Hooks|null
	 */
	private ?Admin_Hooks $admin_hooks = null;

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function run(): void {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		if ( is_admin() ) {
			// Settings must exist before admin_init so it can handle form saves.
			$settings = $this->get_settings_controller();

			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_init', array( $settings, 'maybe_save_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this->get_admin_hooks(), 'enqueue_scripts' ) );
		}
	}

	/**
	 * Load the plugin text domain for translations.
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'easy-g-maps', false, EGM_NAME . '/languages' );
	}

	/**
	 * Register the single settings page under the Settings menu.
	 *
	 * @return void
	 */
	public function register_admin_menu(): void {
		$settings = $this->get_settings_controller();

		add_options_page(
			__( 'Easy G Maps', 'easy-g-maps' ),
			__( 'Easy G Maps', 'easy-g-maps' ),
			$settings->get_settings_cap(),
			SETTINGS_PAGE_SLUG,
			array( $settings, 'render_settings_page' )
		);
	}

	/**
	 * Get the settings controller (lazy-loaded).
	 *
	 * @return Settings
	 */
	public function get_settings_controller(): Settings {
		if ( is_null( $this->settings ) ) {
			$this->settings = new Settings();
		}

		return $this->settings;
	}

	/**
	 * Get the admin hooks handler (lazy-loaded).
	 *
	 * @return Admin_Hooks
	 */
	public function get_admin_hooks(): Admin_Hooks {
		if ( is_null( $this->admin_hooks ) ) {
			$this->admin_hooks = new Admin_Hooks();
		}

		return $this->admin_hooks;
	}
}
