# Easy G Maps - Project Tracker

**Version:** 0.2.0 (in development; 1.0.0 = first-release target)
**Last Updated:** 29 May 2026
**Current Phase:** Milestones 1–6 complete (pending review); M7 (docs + 1.0.0 release) next
**Overall Progress:** ~85%

---

## Overview

Easy G Maps is a lightweight WordPress plugin that makes it easy to drop a Google Map into any content, via a **shortcode** or a **Gutenberg block**. It is deliberately page-builder agnostic so it works across Elementor, GeneratePress, classic editor, and full block themes without special-casing any of them.

Design goals:

- **One settings page, one API key.** A single admin page holds the Google Maps API key plus a "Test key" button. The key is expected to be protected in Google Cloud Console by **HTTP referer** restriction.
- **Two ways to place a map** that share one renderer: the `[egm_map]` shortcode and an `easy-g-maps/map` block.
- **Three ways to locate a map**: a human `address` (geocoded client-side), explicit `lat`/`lng` coordinates, and a Google `place_id`. This means the API key needs both the **Maps JavaScript API** and the **Places API** enabled (plus Geocoding for address lookups).
- **Multi-marker capable from day one, single-marker by default.** The shortcode/block produce a one-element markers array; that array is run through a `easy_g_maps_markers` filter keyed by a `map_name`, so a PHP snippet can add further markers to a named map in code.
- **No build system** — no npm, no Composer, no bundler. Plain CSS/JS only. The Gutenberg block is a no-build dynamic block (PHP `render_callback` + `wp.element.createElement`, with `ServerSideRender` for the editor preview).

### Key Technical Decisions

1. **Client-side key test (not server-side).** A referer-restricted key cannot be validated from PHP — `wp_remote_get` sends no whitelisted referer, so Google returns `REQUEST_DENIED` regardless of whether the key is valid. The "Test key" button therefore runs in the browser: it loads the Maps JS API with the saved key (catching `gm_authFailure`), then exercises a Geocoding lookup and a Places lookup, reporting each API's result. This is the opposite of the Bullfix ERP server-side AJAX test pattern, and is a deliberate, forced choice.
2. **Single shared renderer.** Both the shortcode handler and the block `render_callback` funnel into one `Renderer::render( array $atts ): string`, so behaviour and markup never diverge.
3. **Config travels as JSON in a data attribute.** The renderer emits a `<div class="egm-map" data-egm-map='{…}'>` containing the resolved map config (centre, zoom, map type, markers). The front-end script reads it and builds the map — mirroring the proven approach from the wp-tutorials.tech Google Maps tutorial.
4. **Lazy asset loading.** The Google Maps loader script is only enqueued when a map is actually rendered on the page (a once-guard in the front-end controller), so non-map pages stay clean.

### Identity / Conventions

- **Namespace:** `Easy_G_Maps`
- **`@package`:** `EasyGMaps`
- **Text Domain:** `easy-g-maps`
- **Global constant prefix:** `EGM_` (`EGM_NAME`, `EGM_VERSION`, `EGM_DIR`, `EGM_URL`, `EGM_ASSETS_URL`, `EGM_ADMIN_TEMPLATES_DIR`, `EGM_PUBLIC_TEMPLATES_DIR`)
- **Function / option prefix:** `egm` / `egm_`
- **PHPCS prefixes:** `easy_g_maps`, `egm`, `Easy_G_Maps`
- **Global instance:** `$egm_plugin`, launched via `egm_plugin_run()`
- **Shortcode tag:** `[egm_map]`
- **Block name:** `easy-g-maps/map`
- **PHP:** 8.0+ (no `declare(strict_types=1)`), **WordPress:** 6.3+ (for `wp_enqueue_script` async strategy + iframe editor)

---

## Active TODO Items

- [x] **GitHub remote origin** — `git@github.com:headwalluk/easy-g-maps.git`; repo initialised on `main`, initial docs commit pushed (29 May 2026).
- [ ] **Decide final plugin display name** — "Easy G Maps" is assumed throughout; confirm before first release.

---

## Milestones

### Milestone 1: Plugin Scaffold & Bootstrap 📋

**Status:** Not Started
**Priority:** High
**Target:** v0.1.0

**Goal:** Stand up a clean, activatable plugin skeleton following the Bullfix ERP house style, with nothing user-facing yet beyond "plugin activates without error".

#### Implementation Checklist

- [ ] Create main file `easy-g-maps.php` with full plugin header (`@package EasyGMaps`, Requires PHP 8.0, Requires at least 6.3, Text Domain `easy-g-maps`)
- [ ] Define globals in main file: `const EGM_NAME`, `const EGM_VERSION`; `define()` `EGM_DIR`, `EGM_URL`, `EGM_ASSETS_URL`, `EGM_ADMIN_TEMPLATES_DIR`, `EGM_PUBLIC_TEMPLATES_DIR`
- [ ] `require_once` order: `constants.php` → `functions-private.php` → class files
- [ ] Launch via `egm_plugin_run()` setting the `$egm_plugin` global and calling `Plugin::run()`
- [ ] Register deactivation hook (no cron in v1 — keep as a no-op placeholder for tidiness)
- [ ] Create `constants.php` (namespaced `Easy_G_Maps`, sectioned: Admin & Settings, Options `OPT_`, Defaults `DEF_`, AJAX/nonce actions `ACT_`, shortcode/block, asset handles)
- [ ] Create `functions-private.php` with `get_plugin()`, `get_settings_controller()`, `egm_get_now_h()`, and a control-id helper for form fields
- [ ] Create `includes/class-plugin.php` — orchestrator; all hooks registered in `run()`; lazy-loaded component getters (`get_settings_controller()`, `get_renderer()`, `get_frontend()`, etc.)
- [ ] Create `phpcs.xml` (WordPress ruleset, short-array allowed, prefixes `easy_g_maps`/`egm`/`Easy_G_Maps`, exclude `assets/`, `dev-notes/`, `.git/`)
- [ ] Create `.gitignore` and `.distignore` (already present — verify contents)
- [ ] Create `languages/` directory
- [ ] Stub `README.md`, `readme.txt`, `CHANGELOG.md`
- [ ] Verify: plugin activates with zero PHP notices; `phpcs` reports clean

---

### Milestone 2: Settings Page & API Key Management ✅

**Status:** Complete (pending user review)
**Priority:** High
**Target:** v0.2.0

**Goal:** A single admin page (under **Settings → Easy G Maps**) that stores the Google Maps API key securely, plus the scaffolding for the test button (wired in M3).

#### Implementation Checklist

- [x] Constants: `OPT_API_KEY` (`egm_api_key`), `SETTINGS_PAGE_SLUG` — **no `ACT_TEST_KEY`**: the key test is client-side, so there is no AJAX action to name
- [x] `includes/class-settings.php` — type-safe `get_string()`/`set_string()` (+ `get_api_key()`); nonce action/name set in constructor; `maybe_save_settings()` → `save_settings()`; `render_settings_page()` includes the template with `$settings = $this`
- [x] Capability gate: `manage_options` on render and save
- [x] `includes/class-admin-hooks.php` — `enqueue_scripts( $current_page )` guards on `settings_page_easy-g-maps`; enqueues admin CSS. **Script + `wp_localize_script` deferred to M3** (they belong with the client-side test)
- [x] Admin menu: `add_options_page()` (single page) registered via `Plugin::register_admin_menu()`
- [x] `admin-templates/settings-page.php` — code-first `printf`/`echo`: API key text field, Save Changes button, a "Test key" button + `#egm-test-result` box, and a help panel explaining required APIs (Maps JS + Places + Geocoding) and referer restriction setup
- [x] ~~`includes/form-helpers.php`~~ — **not created**: Bullfix's tab templates use direct `printf`/`echo` rather than the form helpers, so the settings page follows that (cleaner, and avoids the escape-on-echo pitfall). Revisit if a future page needs reusable field markup
- [x] `assets/admin/admin.css` — minimal settings page styling
- [x] `phpcs` clean (7/7 files, zero violations); `php -l` clean
- [ ] Verify in browser: key saves and round-trips; settings-saved notice shows — *pending user review*

---

### Milestone 3: Client-Side API Key Test ✅

**Status:** Complete (pending user review)
**Priority:** High
**Target:** v0.3.0

**Goal:** A "Test key" button that genuinely validates a referer-restricted key from the browser and tells the user which Google APIs are working.

#### Implementation Checklist

- [ ] `assets/admin/admin.js`: on click, read the key from the field (so a key can be tested before saving)
- [ ] Define `window.gm_authFailure` before loading the API to detect invalid key / referer-not-allowed
- [ ] Dynamically inject `https://maps.googleapis.com/maps/api/js?key=…&libraries=places&callback=…&loading=async`
- [ ] On callback: instantiate a throwaway hidden `google.maps.Map` (confirms Maps JS API)
- [ ] Probe **Geocoding** via `Geocoder.geocode({ address: <sample> })` and report OK / status
- [ ] Probe **Places** via `PlacesService.findPlaceFromQuery()` and report OK / status
- [ ] Render a per-API pass/fail summary into the notice box (success = green, failure = red)
- [ ] Handle the "API already loaded" case: if `window.google.maps` exists, run the service probes against the loaded instance and show a "reload to test a different key" note (Google JS API can only bootstrap once per page load)
- [ ] Clear, friendly messaging for the common `gm_authFailure` / `RefererNotAllowedMapError` case, pointing back to Cloud Console referer settings
- [ ] Verify: valid key → all green; key with wrong referer → clear failure; key missing Places → Maps green, Places red

---

### Milestone 4: Shared Map Renderer & Front-End ✅

**Status:** Complete (pending user review)
**Priority:** High
**Target:** v0.4.0

**Note:** `marker_color` is collected into the marker config but not yet rendered (classic `google.maps.Marker` needs an SVG/icon path for colour). It is available to the `easy_g_maps_markers` filter and to a future styling pass. Filter hooks are invoked as literal strings (`easy_g_maps_markers`, `easy_g_maps_map_args`), not constants, per the WPCS hook-name sniff.

**Goal:** The core engine both the shortcode and block sit on: turn attributes into a resolved map config, emit the container, and render the live map on the front end.

#### Implementation Checklist

- [ ] Constants: `DEF_ZOOM`, `DEF_HEIGHT`, `DEF_WIDTH`, `DEF_MAP_TYPE`, public asset handles (`HANDLE_PUBLIC`, `HANDLE_GOOGLE`)
- [ ] `includes/class-renderer.php`:
  - [ ] `normalise_atts()` — `shortcode_atts` defaults, map-type allow-list, numeric height/width → append `px`
  - [ ] `build_center()` — `{lat,lng}` when both supplied, else `null` (front-end derives from first marker)
  - [ ] `build_markers()` — assemble the single marker from `address`/`lat`/`lng`/`place_id` + title/text/colour, honouring a `marker` on/off attribute
  - [ ] `map_name` attribute with auto-generated fallback (`egm-map-N`, static counter)
  - [ ] Apply `easy_g_maps_markers` filter `( $markers, $map_name, $atts )` — **multi-marker extension point**
  - [ ] Apply `easy_g_maps_map_args` filter `( $config, $atts )`
  - [ ] Trigger front-end asset enqueue (once-guard) then return the rendered template
- [ ] `templates/map.php` — code-first; `<div class="egm-map" style="height:…;width:…" data-egm-map='<json>'>`; theme-override-able via `locate_template( 'easy-g-maps/map.php' )`
- [ ] `includes/class-frontend.php`:
  - [ ] `register_assets()` on `wp_enqueue_scripts` (register, don't enqueue)
  - [ ] `enqueue_map_assets()` — once-guard; enqueues `maps.css` + `maps.js` + Google loader with key & `callback=egmInitMaps` (async strategy); no-ops gracefully if no API key is set (renders a maintainer-only notice)
- [ ] `assets/public/maps.css` — sensible default container styling
- [ ] `assets/public/maps.js`:
  - [ ] `window.egmInitMaps` callback → init every `.egm-map:not([data-egm-init])`
  - [ ] Resolve marker positions client-side: direct `lat`/`lng`, else `place_id` via Places `getDetails`, else `address` via Geocoder
  - [ ] Derive centre from config or first resolved marker; `fitBounds` when >1 marker
  - [ ] Markers + click info windows (title/text), escaped
  - [ ] `MutationObserver` to init maps added after load (covers AJAX content and the block editor preview)
- [ ] Verify: a hand-written `[egm_map]` in a post renders a live, centred, pinned map on the front end

---

### Milestone 5: Shortcode ✅

**Status:** Complete (pending user review)
**Priority:** High
**Target:** v0.5.0

**Goal:** Expose the renderer as `[egm_map]` for classic editor, Elementor text/shortcode widgets, GeneratePress elements, etc.

#### Implementation Checklist

- [ ] Constant: `SHORTCODE_TAG = 'egm_map'`
- [ ] `includes/class-shortcode.php` — register on `init`, map shortcode atts → `Renderer::render()`
- [ ] Supported attributes documented: `map_name`, `address`, `lat`, `lng`, `place_id`, `zoom`, `height`, `width`, `map_type`, `marker`, `marker_title`, `marker_text`, `marker_color`
- [ ] Verify in: classic editor, an Elementor shortcode widget, a GeneratePress block/element
- [ ] Document the multi-marker filter snippet (using `map_name`) in `readme.txt` / dev-notes

---

### Milestone 6: Gutenberg Block (no-build dynamic block) ✅

**Status:** Complete (pending user review)
**Priority:** High
**Target:** v0.6.0

**Goal:** A native block that reuses the renderer, with a sidebar control panel and a live editor preview — no build tooling.

#### Implementation Checklist

- [ ] `includes/class-block.php` — `register_block_type( 'easy-g-maps/map', [ attributes, render_callback ] )` on `init` (registered in both front and editor/REST contexts for `ServerSideRender`)
- [ ] `render()` maps block attributes (camelCase) → renderer atts (snake_case), reusing `Renderer::render()`
- [ ] `assets/admin/block.js` (no JSX): `registerBlockType` via `wp.blocks` + `wp.element.createElement`; `InspectorControls` panels (Location / Map / Marker) using `wp.components` controls; `ServerSideRender` preview; `save: () => null` (dynamic)
- [ ] `Admin_Hooks::enqueue_block_editor_assets()` — enqueue `block.js` (deps: `wp-blocks`, `wp-element`, `wp-block-editor`, `wp-components`, `wp-i18n`, `wp-server-side-render`) + the front-end preview assets + localized `hasKey` flag
- [ ] Editor messaging: friendly notice when no API key is configured, and when no location is set yet
- [ ] Verify: insert block, set an address → live preview map; attributes persist; front-end output matches shortcode

---

### Milestone 7: Docs, QA & 1.0.0 Release 📋

**Status:** Not Started
**Priority:** Medium
**Target:** v1.0.0

**Goal:** Polish, document, cross-environment test, and cut the first release.

#### Implementation Checklist

- [ ] `README.md` (GitHub-facing, badges + dev pointers) and `readme.txt` (WP.org style: features, installation, FAQ, screenshots placeholders)
- [ ] `CHANGELOG.md` — `[1.0.0]` entry (Keep a Changelog format)
- [ ] In-plugin help text on the settings page: how to create + restrict the key in Cloud Console (Maps JS + Places + Geocoding; referer restriction)
- [ ] Cross-environment test matrix:
  - [ ] Classic editor + shortcode
  - [ ] Block editor (block + preview)
  - [ ] Elementor page
  - [ ] GeneratePress / block theme
- [ ] Location-mode tests: address-only, lat/lng-only, place_id-only, mixed; `marker` on/off; multiple maps on one page; multi-marker via a filter snippet
- [ ] `phpcs` clean across the whole repo; `phpcbf` applied
- [ ] Bump `EGM_VERSION` + plugin header + `readme.txt` Stable tag to 1.0.0
- [ ] Tag and release on GitHub

---

### Milestone 8: GitHub Release Workflow 📋

**Status:** Not Started
**Priority:** Low
**Target:** TBC

**Goal:** A GitHub Actions workflow that, on a `v*` tag push, builds a distribution zip honouring `.distignore` (excluding `dev-notes/`, `.github/`, `phpcs.xml`, etc.) and attaches it to the GitHub Release — matching the pattern used on other Headwall plugin projects.

#### Tasks

- [x] Push initial `main` to the new GitHub repo (`headwalluk/easy-g-maps`)
- [ ] Add `.github/workflows/release.yml` (build zip on tag, attach to Release)
- [ ] Document the tag/release procedure in `README.md`

---

## Current Architecture Notes

### Planned Components

1. **Plugin Core** (`includes/class-plugin.php`) — orchestration; all hooks registered in `run()`; lazy-loaded component getters.
2. **Settings** (`includes/class-settings.php`) — type-safe option access (`get_string`/`set_string`), nonce verification, settings-page render + save.
3. **Admin Hooks** (`includes/class-admin-hooks.php`) — admin + block-editor asset enqueueing and `wp_localize_script` data.
4. **Renderer** (`includes/class-renderer.php`) — shared shortcode/block engine; builds map config, applies marker/config filters, loads the container template.
5. **Frontend** (`includes/class-frontend.php`) — registers + lazily enqueues the public map assets and the Google Maps loader.
6. **Shortcode** (`includes/class-shortcode.php`) — `[egm_map]` → renderer.
7. **Block** (`includes/class-block.php`) — `easy-g-maps/map` dynamic block → renderer; editor UI in `assets/admin/block.js`.
8. **Constants** (`constants.php`) — `OPT_`, `DEF_`, `ACT_`, shortcode/block, and asset-handle constants under the `Easy_G_Maps` namespace.

### Extension Points (filters)

| Filter | Args | Purpose |
|--------|------|---------|
| `easy_g_maps_markers` | `$markers, $map_name, $atts` | Add/modify markers for a named map (multi-marker support via PHP snippet) |
| `easy_g_maps_map_args` | `$config, $atts` | Modify the full resolved map config before it is emitted |

### Data Flow

```
[egm_map] / block  →  Renderer::render( $atts )
                        ├─ normalise atts
                        ├─ build single-marker array
                        ├─ apply 'easy_g_maps_markers' (map_name)   ← PHP snippet can add markers
                        ├─ apply 'easy_g_maps_map_args'
                        ├─ Frontend::enqueue_map_assets() (once)
                        └─ templates/map.php  →  <div class="egm-map" data-egm-map='{…}'>

Front end:  Google Maps JS loads → egmInitMaps() → for each .egm-map:
            parse JSON → resolve marker positions (latlng | place_id | address)
            → new google.maps.Map → drop markers → info windows
```

---

## Technical Debt

_None yet — pre-build._

Anticipated items to watch:

1. **`google.maps.Marker` deprecation** — Google now recommends `AdvancedMarkerElement` (requires a `mapId`). v1 uses the classic `Marker` for simplicity; revisit if/when classic markers are sunset.
2. **Translation files** — create `languages/` early; generate a `.pot` (`wp i18n make-pot`) before 1.0.0.
3. **Per-page Google API quota** — the key test and multiple address/place_id markers each cost Geocoding/Places lookups; consider caching resolved coordinates if quota becomes a concern.

---

## Notes for Development

- No build system — plain CSS/JS, no npm/Composer/bundler.
- The block is a **dynamic** block: `save()` returns `null`, server renders via `render_callback`, editor previews via `ServerSideRender`.
- The key test is **client-side by necessity** (referer-restricted keys can't be checked from PHP).
- Both shortcode and block must produce **identical** front-end markup — they share `Renderer::render()`.
- Text domain: `easy-g-maps`. Namespace: `Easy_G_Maps`. Global prefix: `egm` / `EGM_`.
- Reference implementation for house style: `../bullfix-erp/`.
</content>
</invoke>
