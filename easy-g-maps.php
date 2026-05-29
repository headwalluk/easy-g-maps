<?php
/**
 * Plugin Name:       Easy G Maps
 * Plugin URI:        https://headwall-hosting.com/
 * Description:       Easily add Google Maps to your content with a shortcode or a Gutenberg block. One settings page, one API key.
 * Version:           0.1.0
 * Requires at least: 6.3
 * Requires PHP:      8.0
 * Author:            Paul Faulkner
 * Author URI:        https://headwall-hosting.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       easy-g-maps
 * Domain Path:       /languages
 *
 * @package EasyGMaps
 */

defined( 'ABSPATH' ) || die();

const EGM_NAME    = 'easy-g-maps';
const EGM_VERSION = '0.1.0';

define( 'EGM_DIR', plugin_dir_path( __FILE__ ) );
define( 'EGM_URL', plugin_dir_url( __FILE__ ) );
define( 'EGM_ASSETS_URL', trailingslashit( EGM_URL . 'assets' ) );
define( 'EGM_ADMIN_TEMPLATES_DIR', trailingslashit( EGM_DIR . 'admin-templates' ) );
define( 'EGM_PUBLIC_TEMPLATES_DIR', trailingslashit( EGM_DIR . 'templates' ) );

// Load constants and helper functions.
require_once EGM_DIR . 'constants.php';
require_once EGM_DIR . 'functions-private.php';

// Load plugin classes.
require_once EGM_DIR . 'includes/class-plugin.php';

/**
 * When the plugin is deactivated, tidy up after ourselves.
 *
 * Placeholder for now (the plugin schedules no cron and creates no tables).
 * When client-side coordinate caching is added it will clear its transients
 * here. See dev-notes/00-project-tracker.md (Technical Debt).
 *
 * @return void
 */
function egm_deactivate(): void {
	// Nothing to clean up yet.
}
register_deactivation_hook( __FILE__, 'egm_deactivate' );

/**
 * Launch the plugin core.
 *
 * @return void
 */
function egm_plugin_run(): void {
	global $egm_plugin;

	$egm_plugin = new Easy_G_Maps\Plugin();
	$egm_plugin->run();
}
egm_plugin_run();
