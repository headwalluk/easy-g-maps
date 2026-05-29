<?php
/**
 * Settings page (single page).
 *
 * @package EasyGMaps
 *
 * @var Settings $settings Provided by Settings::render_settings_page().
 */

namespace Easy_G_Maps;

defined( 'ABSPATH' ) || die();

echo '<div class="wrap egm-settings">';
printf( '<h1>%s</h1>', esc_html( get_admin_page_title() ) );

settings_errors( SETTINGS_PAGE_SLUG );

printf(
	'<p>%s</p>',
	esc_html__( 'Add your Google Maps API key below, then drop a map into any content with the [egm_map] shortcode or the Easy G Map block.', 'easy-g-maps' )
);

// ---------------------------------------------------------------------------
// Settings form.
// ---------------------------------------------------------------------------

echo '<form method="post" action="">';
wp_nonce_field( $settings->settings_action, $settings->settings_nonce );

echo '<table class="form-table" role="presentation"><tr><th scope="row">';
printf(
	'<label for="%s">%s</label>',
	esc_attr( OPT_API_KEY ),
	esc_html__( 'Google Maps API key', 'easy-g-maps' )
);
echo '</th><td>';

printf(
	'<input type="text" name="%1$s" id="%1$s" value="%2$s" class="regular-text egm-api-key" autocomplete="off" spellcheck="false" />',
	esc_attr( OPT_API_KEY ),
	esc_attr( $settings->get_api_key() )
);

printf(
	'<p class="description">%s</p>',
	esc_html__( 'Restrict this key by HTTP referrer in Google Cloud Console. The key is sent to the browser whenever a map loads, so it is not a secret — the referrer restriction is what protects it.', 'easy-g-maps' )
);

echo '</td></tr></table>';

submit_button( __( 'Save changes', 'easy-g-maps' ) );
echo '</form>';

// ---------------------------------------------------------------------------
// API key test (wired up client-side in Milestone 3).
// ---------------------------------------------------------------------------

printf( '<h2>%s</h2>', esc_html__( 'Test your key', 'easy-g-maps' ) );

printf(
	'<p>%s</p>',
	esc_html__( 'This loads Google Maps in your browser using the key above and checks the Maps JavaScript, Geocoding, and Places APIs. Because the key is referrer-restricted, it can only be tested from the browser — not from the server.', 'easy-g-maps' )
);

printf(
	'<button type="button" class="button button-secondary egm-test-key">%s</button>',
	esc_html__( 'Test key', 'easy-g-maps' )
);
echo '<span class="spinner egm-spinner"></span>';
echo '<div id="egm-test-result" class="egm-test-result" aria-live="polite"></div>';

// ---------------------------------------------------------------------------
// Help.
// ---------------------------------------------------------------------------

printf( '<h2>%s</h2>', esc_html__( 'Setting up your key', 'easy-g-maps' ) );

echo '<ol class="egm-help">';
printf(
	'<li>%s</li>',
	esc_html__( 'In Google Cloud Console, create (or open) a project and generate an API key.', 'easy-g-maps' )
);
printf(
	'<li>%s</li>',
	esc_html__( 'Enable these APIs for the project: Maps JavaScript API, Places API, and Geocoding API.', 'easy-g-maps' )
);
printf(
	'<li>%s</li>',
	esc_html__( 'Under the key\'s Application restrictions, choose "HTTP referrers" and add your site domain (for example: *.example.com/*).', 'easy-g-maps' )
);
printf(
	'<li>%s</li>',
	esc_html__( 'Paste the key above, save, then use the Test key button to confirm each API responds.', 'easy-g-maps' )
);
echo '</ol>';

printf(
	'<p><a href="%1$s" target="_blank" rel="noopener">%2$s</a></p>',
	esc_url( 'https://console.cloud.google.com/google/maps-apis/credentials' ),
	esc_html__( 'Open Google Cloud Console credentials', 'easy-g-maps' )
);

echo '</div>';
