=== Easy G Maps ===
Contributors: Headwall
Tags: google maps, maps, shortcode, block, gutenberg
Requires at least: 6.3
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily add Google Maps to your content with a shortcode or a Gutenberg block. One settings page, one API key.

== Description ==

Easy G Maps is a lightweight way to drop a Google Map into any content. It is page-builder agnostic, so it works the same across Elementor, GeneratePress, the classic editor, and full block themes.

A single admin page holds your Google Maps API key (protected in Google Cloud Console by HTTP referrer) and a button to test it from the browser.

= Features =

* One settings page, one API key, with a built-in "Test key" button
* The `[egm_map]` shortcode and a matching "Easy G Map" block, sharing one renderer
* Locate a map by address (geocoded in the browser), by lat/lng coordinates, or by Google Place ID
* Multi-marker capable: add more markers in code via the `easy_g_maps_markers` filter, keyed by `map_name`
* Google Maps script only loads on pages that contain a map
* No build system: plain PHP, CSS, and JavaScript

== Installation ==

1. Upload the `easy-g-maps` folder to `/wp-content/plugins/`, or install the zip via Plugins → Add New → Upload Plugin.
2. Activate the plugin.
3. In Google Cloud Console, create an API key and enable the Maps JavaScript API, Places API, and Geocoding API. Restrict the key by HTTP referrer to your domain.
4. Go to Settings → Easy G Maps, paste your key, save, and click "Test key".
5. Add a map with the `[egm_map]` shortcode or the "Easy G Map" block.

== Frequently Asked Questions ==

= Why is the key test done in the browser? =

A key restricted by HTTP referrer cannot be validated from the server, because server requests carry no whitelisted referrer. The Test key button loads Google Maps in your browser (where the referrer is your own admin domain) and reports whether the Maps JavaScript, Geocoding, and Places APIs respond.

= Is it safe that the API key appears in the page source? =

Yes. A Maps JavaScript API key is always sent to the browser; that is how the map loads. Protect it with HTTP referrer restrictions in Google Cloud Console rather than treating it as a secret.

= How do I show more than one marker? =

Give the map a `map_name` and add markers with the `easy_g_maps_markers` filter. See the readme on GitHub for a code example.

= Can I change the map container markup? =

Yes. Copy `templates/map.php` into your theme as `easy-g-maps/map.php`.

== Changelog ==

= 1.0.0 =
* Initial release: settings page with client-side API key test, the `[egm_map]` shortcode, the Easy G Map block, address/lat-lng/Place ID locations, and the `easy_g_maps_markers` / `easy_g_maps_map_args` filters.
