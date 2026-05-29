# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Easy G Maps is a lightweight WordPress plugin for adding Google Maps to content via a **shortcode** (`[egm_map]`) or a **Gutenberg block** (`easy-g-maps/map`). It is page-builder agnostic — designed to work across Elementor, GeneratePress, the classic editor, and full block themes. A single admin page holds one Google Maps API key (expected to be HTTP-referer-restricted in Google Cloud Console) and a button to test it.

- **Namespace:** `Easy_G_Maps`
- **`@package`:** `EasyGMaps`
- **Text Domain:** `easy-g-maps`
- **PHP:** 8.0+ (do NOT use `declare(strict_types=1)` — breaks WordPress interop)
- **WordPress:** 6.3+
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS/JS. The block is a no-build dynamic block.

## Commands

```bash
phpcs                  # Check WordPress coding standards compliance
phpcbf                 # Auto-fix coding standards violations
phpcs includes/        # Check a specific directory
```

Always run `phpcs` before committing. Config is in `phpcs.xml` — WordPress standards (short array syntax allowed) with prefixes: `easy_g_maps`, `egm`, `Easy_G_Maps`.

## Architecture

### Entry Point & Bootstrap

`easy-g-maps.php` is the main plugin file and is **not namespaced**. It defines global constants (`EGM_NAME`, `EGM_VERSION`, `EGM_DIR`, `EGM_URL`, `EGM_ASSETS_URL`, `EGM_ADMIN_TEMPLATES_DIR`, `EGM_PUBLIC_TEMPLATES_DIR`), `require_once`s `constants.php` → `functions-private.php` → all class files, then calls `egm_plugin_run()` which creates the global `$egm_plugin` instance and invokes `Plugin::run()`.

### Core Classes (namespace `Easy_G_Maps`)

- **`Plugin`** (`includes/class-plugin.php`) — Main orchestrator. All hooks are registered in `run()`. Holds lazy-loaded component getters (`get_settings_controller()`, `get_renderer()`, `get_frontend()`, …).
- **`Settings`** (`includes/class-settings.php`) — Type-safe option access (`get_string()`/`set_string()` etc.), nonce action/name set in the constructor, `maybe_save_settings()` → `save_settings()`, and `render_settings_page()` (includes the template with `$settings = $this`).
- **`Admin_Hooks`** (`includes/class-admin-hooks.php`) — Enqueues admin + block-editor CSS/JS (guarded by page slug) and provides `wp_localize_script` data.
- **`Renderer`** (`includes/class-renderer.php`) — The shared engine for both shortcode and block. Normalises attributes, builds the markers array + centre, applies filters, triggers asset enqueue, and loads the container template.
- **`Frontend`** (`includes/class-frontend.php`) — Registers public map assets and lazily enqueues them + the Google Maps loader **only when a map is on the page**.
- **`Shortcode`** (`includes/class-shortcode.php`) — Registers `[egm_map]`, maps atts → `Renderer::render()`.
- **`Block`** (`includes/class-block.php`) — Registers the `easy-g-maps/map` dynamic block; `render_callback` maps camelCase block attributes → renderer atts.

### One Renderer, Two Entry Points

The shortcode handler and the block `render_callback` both funnel into **`Renderer::render( array $atts ): string`**. Never render maps anywhere else — keeping a single renderer guarantees the shortcode and block produce identical front-end markup. The renderer emits:

```html
<div class="egm-map" style="height:…;width:…" data-egm-map='{"mapName":…,"options":{…},"markers":[…]}'></div>
```

`assets/public/maps.js` reads `data-egm-map`, resolves marker positions (direct `lat`/`lng`, else `place_id` via Places, else `address` via Geocoder), and builds the map. A `MutationObserver` re-inits maps added after page load (covers AJAX content and the block editor preview).

### Locating a Map

Three ways, all usable together; the API key needs **Maps JavaScript API + Places API + Geocoding API** enabled:

- `address` — geocoded client-side
- `lat` + `lng` — explicit coordinates
- `place_id` — Google Place ID, resolved via Places

### Markers & the `map_name` Filter

The shortcode/block build a **single-element** markers array. It is then passed through `apply_filters( 'easy_g_maps_markers', $markers, $map_name, $atts )` so a PHP snippet can add more markers to a specifically named map. Every map has a `map_name` (auto-generated as `egm-map-N` if not supplied) so filters can target the right one. The full config also passes through `easy_g_maps_map_args`.

### The API Key Test is Client-Side (important)

Unlike a typical server-side "test connection", a **referer-restricted** key cannot be validated from PHP — `wp_remote_get` has no whitelisted referer, so Google returns `REQUEST_DENIED`. The test in `assets/admin/admin.js` therefore runs in the browser: it loads the Maps JS API with the key (catching `gm_authFailure`), then probes Geocoding and Places, reporting each API's status. Do not "fix" this into a server-side AJAX call.

### Lazy Asset Loading

`Frontend::enqueue_map_assets()` has a once-guard and is called from `Renderer::render()`. The Google Maps loader (`…/maps/api/js?key=…&libraries=places&callback=egmInitMaps`) is only enqueued when a map is actually rendered, so non-map pages stay clean.

### Constants

All option keys, defaults, AJAX/nonce actions, shortcode/block names, and asset handles live in `constants.php` under the `Easy_G_Maps` namespace. Convention: `OPT_` for options, `DEF_` for defaults, `ACT_` for AJAX/nonce actions.

### Admin UI

Settings page rendered with `printf()`/`echo` code-first templates (no inline HTML mixed with PHP). Templates live in `admin-templates/` and receive a `$settings` variable. Reusable field markup is in `includes/form-helpers.php`.

## Key Conventions

- Register all hooks in `Plugin::run()`; implement in the respective classes.
- Use constants from `constants.php` — never hardcode option names or magic values.
- Store dates as human-readable strings (`Y-m-d H:i:s T`), not Unix timestamps.
- Use `filter_var( $val, FILTER_VALIDATE_BOOLEAN )` for boolean options/attributes.
- Templates (`admin-templates/`, `templates/`) must use `printf()`/`echo` — no inline HTML with PHP snippets.
- No inline JavaScript — all JS lives in `assets/` and is enqueued.
- `<button>` elements always include the `button` class.
- Security on all admin forms/AJAX: nonce verification, `manage_options` capability check, input sanitization, output escaping.
- Prefer Single-Entry-Single-Exit functions (one `return` at the end).
- The block is dynamic: `save()` returns `null`; the server renders via `render_callback`; the editor previews via `ServerSideRender`.

## Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `refactor:` `chore:` `docs:` `style:` `test:`

## Reference Files

- `dev-notes/00-project-tracker.md` — milestones, roadmap, and key technical decisions
- `.github/copilot-instructions.md` — portable WordPress coding standards guide
- `dev-notes/patterns/` — implementation pattern examples (JavaScript, settings API, templates)
- `dev-notes/workflows/` — code standards setup and git commit workflow
- `../bullfix-erp/` — reference plugin for house style (bootstrap, Settings class, Admin_Hooks, form helpers, code-first templates)
</content>
