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

		// The settings-page script and its localized data (the API-key test)
		// are added in Milestone 3.
	}
}
