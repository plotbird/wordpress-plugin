# Changelog

All notable changes to the WordPress plugin are recorded here. The format
follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the
project uses [semantic versioning](https://semver.org/).

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

[1.0.0]: https://github.com/plotbird/wordpress-plugin/releases/tag/v1.0.0
