<?php
/**
 * Plugin-scope settings.
 *
 * @package EasyGMaps
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

/**
 * Settings management for Easy G Maps.
 *
 * Provides type-safe option access, admin-page rendering, and nonce-verified
 * saving for the single settings page.
 */
class Settings {

	/**
	 * Settings action name for the nonce.
	 *
	 * @var string
	 */
	public string $settings_action;

	/**
	 * Settings nonce field name.
	 *
	 * @var string
	 */
	public string $settings_nonce;

	/**
	 * Capability required to manage settings.
	 *
	 * @var string
	 */
	protected string $settings_cap = 'manage_options';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	protected string $settings_page_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings_action    = 'egmstngsact' . EGM_NAME;
		$this->settings_nonce     = 'egmstngsnce' . EGM_NAME;
		$this->settings_page_name = SETTINGS_PAGE_SLUG;
	}

	/**
	 * Get the capability required to manage settings.
	 *
	 * @return string
	 */
	public function get_settings_cap(): string {
		return $this->settings_cap;
	}

	/**
	 * Get the settings page slug.
	 *
	 * @return string
	 */
	public function get_settings_page_name(): string {
		return $this->settings_page_name;
	}

	/**
	 * Get the settings page URL.
	 *
	 * @return string
	 */
	public function get_settings_page_url(): string {
		return admin_url( 'options-general.php?page=' . $this->settings_page_name );
	}

	/**
	 * Get a string option.
	 *
	 * @param string $option_name Option name.
	 * @param string $fallback    Default value if the option is unset.
	 *
	 * @return string
	 */
	public function get_string( string $option_name, string $fallback = '' ): string {
		return strval( get_option( $option_name, $fallback ) );
	}

	/**
	 * Set a string option (deletes the option when the value is empty).
	 *
	 * @param string     $option_name Option name.
	 * @param string     $value       Value to set.
	 * @param mixed|null $autoload    Autoload setting.
	 *
	 * @return bool
	 */
	public function set_string( string $option_name, string $value = '', mixed $autoload = null ): bool {
		if ( '' !== $value ) {
			$result = update_option( $option_name, $value, $autoload );
		} else {
			$result = delete_option( $option_name );
		}

		return $result;
	}

	/**
	 * Get the configured Google Maps API key.
	 *
	 * @return string
	 */
	public function get_api_key(): string {
		return $this->get_string( OPT_API_KEY );
	}

	/**
	 * Maybe save settings if the settings form was submitted.
	 *
	 * Verifies the nonce and capability before saving.
	 *
	 * @return void
	 */
	public function maybe_save_settings(): void {
		if ( ! is_admin() || wp_doing_ajax() ) {
			return;
		}

		$nonce = isset( $_POST[ $this->settings_nonce ] )
			? sanitize_text_field( wp_unslash( $_POST[ $this->settings_nonce ] ) )
			: '';

		if ( '' === $nonce || ! wp_verify_nonce( $nonce, $this->settings_action ) ) {
			return;
		}

		if ( ! current_user_can( $this->settings_cap ) ) {
			return;
		}

		$this->save_settings();
	}

	/**
	 * Save the settings.
	 *
	 * Authentication, capability and nonce checks have already passed in
	 * {@see maybe_save_settings()}, so here we just parse and store $_POST.
	 *
	 * @return void
	 */
	public function save_settings(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified in maybe_save_settings().
		$api_key = isset( $_POST[ OPT_API_KEY ] )
			? sanitize_text_field( wp_unslash( $_POST[ OPT_API_KEY ] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		$this->set_string( OPT_API_KEY, $api_key );

		add_settings_error(
			SETTINGS_PAGE_SLUG,
			'egm_settings_saved',
			__( 'Settings saved.', 'easy-g-maps' ),
			'success'
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( $this->settings_cap ) ) {
			printf( '<p>%s</p>', esc_html__( 'You do not have permission to access this page.', 'easy-g-maps' ) );
		} else {
			// Make the settings object available to the template.
			$settings = $this;

			require EGM_ADMIN_TEMPLATES_DIR . 'settings-page.php';
		}
	}
}
