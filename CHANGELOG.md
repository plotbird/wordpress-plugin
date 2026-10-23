# Changelog

All notable changes to the WordPress plugin are recorded here. The format
follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the
project uses [semantic versioning](https://semver.org/).

## [1.0.4] - 2026-10-23

### Changed

* The admin menu entry moved from position 3 to the bottom of the menu. A
  top-level item is right for a dashboard, but it must not displace the core
  items — which is what the plugin directory flagged.
* The release archive no longer includes `languages/`. Translations for a plugin
  hosted on WordPress.org are generated and distributed by
  translate.wordpress.org; the `.po` and `.pot` files stay in this repository, so
  the Russian translation can be imported there instead of shipped.

## [1.0.3] - 2026-10-19

### Fixed

* The plugin URI and the author URI were the same address, which the plugin
  directory refuses at upload: the first describes this plugin, the second
  describes who made it.

## [1.0.2] - 2026-10-19

### Changed

* The description says where the tracking script comes from — the installation
  the user names — and that the plugin is the WordPress client of the PlotBird
  project. Both are questions the plugin directory asks before it looks at code.

## [1.0.1] - 2026-10-19

### Changed

* The tracking script is registered with `wp_enqueue_script()` and decorated
  through `script_loader_tag`, so WordPress owns the tag instead of the plugin
  printing one.
* The tracker URL carries a plugin version, so an update is fetched instead of
  being served from a browser cache.

### Fixed

* Plugin Check reports no errors and no warnings. The generated chart and list
  markup is marked as such, a non-escaping read of the step list is escaped, and
  the readme declares `Tested up to: 7.1` instead of a patch version.
* `load_plugin_textdomain()` is gone: WordPress loads the catalogue by itself, and
  the call is discouraged for plugins hosted on WordPress.org.

## [1.0.0] - 2026-10-19

First public release.

### Added

- Tracking code on public pages only: never in the admin, feeds, previews or
  robots responses, and never for logged-in administrators unless you turn that
  off.
- Four-step setup wizard with real checks: it fetches `tracker.js` from the
  installation and looks for the tracking tag on the front page.
- Statistics screen in the admin: five metrics with sparklines, a pageviews and
  visitors chart, top pages, sources, countries with flags, devices, browsers,
  and goals. Blocks sit three to a row, and the page uses the full width.
- Period switcher for today, 7 days and 30 days, with a one-minute cache and a
  refresh button.
- Share-link reader that refuses a link belonging to another website, and
  explains a revoked or expired one instead of showing an empty screen.
- English and Russian screens. The plugin follows the site language and can be
  forced to either from the setup screen.
- Bird icon in the admin menu, drawn from the project's own mark.

[1.0.3]: https://github.com/plotbird/wordpress-plugin/releases/tag/v1.0.3
[1.0.1]: https://github.com/plotbird/wordpress-plugin/releases/tag/v1.0.1
[1.0.2]: https://github.com/plotbird/wordpress-plugin/releases/tag/v1.0.2
[1.0.3]: https://github.com/plotbird/wordpress-plugin/releases/tag/v1.0.3
[1.0.1]: https://github.com/plotbird/wordpress-plugin/releases/tag/v1.0.1
[1.0.0]: https://github.com/plotbird/wordpress-plugin/releases/tag/v1.0.0
