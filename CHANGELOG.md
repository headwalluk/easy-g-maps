# Changelog

All notable changes to Easy G Maps will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-05-29

### Added
- Single settings page (Settings → Easy G Maps) storing one Google Maps API key.
- Client-side **Test key** button that loads the Maps JS API in the browser, catches authentication failures, and probes the Maps JavaScript, Geocoding, and Places APIs (the correct way to validate a referrer-restricted key).
- `[egm_map]` shortcode with `map_name`, `address`, `lat`, `lng`, `place_id`, `zoom`, `height`, `width`, `map_type`, `marker`, `marker_title`, `marker_text`, and `marker_color` attributes.
- **Easy G Map** Gutenberg block (dynamic, no build step) with sidebar controls and a live `ServerSideRender` preview, reusing the shortcode renderer.
- Locate a map by address (geocoded in the browser), by `lat`/`lng`, or by Google Place ID.
- `easy_g_maps_markers` filter (keyed by `map_name`) for adding markers in code — multi-marker support — and `easy_g_maps_map_args` filter for the full map config.
- Lazy asset loading: the Google Maps script is only enqueued on pages that contain a map.
- Theme-overridable container template (`templates/map.php`).
- Continuous integration (PHPCS + `php -l`) and a tag-triggered release workflow that builds a distribution zip.

[1.0.0]: https://github.com/headwalluk/easy-g-maps/releases/tag/v1.0.0
