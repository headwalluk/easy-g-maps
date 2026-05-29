# Easy G Maps

![WordPress](https://img.shields.io/badge/WordPress-6.3%2B-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-GPLv2%20or%20later-blue)

Easily add Google Maps to WordPress content with a **shortcode** or a **Gutenberg block**. One settings page, one API key — and it works the same across Elementor, GeneratePress, the classic editor, and full block themes.

Built by [Headwall Hosting](https://headwall-hosting.com).

## Features

- **One settings page, one API key** (Settings → Easy G Maps) with a built-in **Test key** button.
- **Shortcode** `[egm_map]` and a matching **Easy G Map** block — both share a single renderer, so output is identical.
- **Three ways to locate a map**: a human `address` (geocoded in the browser), explicit `lat`/`lng`, or a Google `place_id`.
- **Multi-marker capable**: a map starts with one marker, and the `easy_g_maps_markers` filter lets you add more in code, keyed by `map_name`.
- **Lazy loading**: the Google Maps script is only loaded on pages that actually contain a map.
- **No build step**: plain PHP/CSS/JS — no npm, no Composer, no bundler.

## Requirements

- WordPress 6.3+
- PHP 8.0+
- A Google Maps API key with the **Maps JavaScript API**, **Places API**, and **Geocoding API** enabled. Restrict the key by **HTTP referrer** to your domain in Google Cloud Console. (The key is sent to the browser when a map loads, so it is not a secret — the referrer restriction is what protects it.)

## Usage

### Shortcode

```text
[egm_map address="Trafalgar Square, London" zoom="15" marker_title="We are here"]
[egm_map lat="51.5034" lng="-0.1276" zoom="16"]
[egm_map place_id="ChIJ68f-Lil2dkgR-vsB4PII8oA"]
```

**Attributes:** `map_name`, `address`, `lat`, `lng`, `place_id`, `zoom`, `height`, `width`, `map_type` (`roadmap`/`satellite`/`hybrid`/`terrain`), `marker` (`yes`/`no`), `marker_title`, `marker_text`, `marker_color`.

### Block

Insert the **Easy G Map** block and set the location, map, and marker options in the sidebar. The editor shows a live preview once your API key is saved.

### Adding markers in code (multi-marker)

Give a map a `map_name`, then add markers to it with the `easy_g_maps_markers` filter:

```php
add_filter(
	'easy_g_maps_markers',
	function ( array $markers, string $map_name, array $atts ): array {
		if ( 'branches' === $map_name ) {
			$markers[] = array(
				'address' => '221B Baker Street, London',
				'title'   => 'North branch',
				'text'    => 'Open weekends',
			);
		}
		return $markers;
	},
	10,
	3
);
```

```text
[egm_map map_name="branches" address="10 Downing Street, London"]
```

### Theme override

Copy `templates/map.php` into your theme as `easy-g-maps/map.php` to customise the container markup.

## Developer documentation

- [CLAUDE.md](CLAUDE.md) — architecture, conventions, and key decisions
- [dev-notes/00-project-tracker.md](dev-notes/00-project-tracker.md) — milestones and roadmap
- [CHANGELOG.md](CHANGELOG.md) — per-version release notes

### Coding standards

`phpcs`/`phpcbf` are installed globally on the host (no Composer in this project). Run `phpcs` before committing — config is in `phpcs.xml`.

### CI / Releases

- **CI** (`.github/workflows/ci.yml`) runs `php -l` and PHPCS on every push and pull request (PHP 8.0 and 8.3). WordPress Coding Standards are installed by cloning them, not via Composer.
- **Releases** (`.github/workflows/release.yml`) build a clean distribution zip (honouring `.distignore`) and attach it to the GitHub Release when a `v*` tag is pushed.

## License

Licensed under [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html). Maintained by [Headwall Hosting](https://headwall-hosting.com).
