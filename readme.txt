=== PlotBird Analytics ===
Contributors: plotbird
Tags: analytics, statistics, privacy, cookieless, self-hosted
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Adds the PlotBird tracking code to your site. PlotBird itself is self-hosted, cookie-free analytics that runs on ordinary PHP and MySQL hosting.

== Description ==

This plugin adds the PlotBird tracking code to every page of your site. It does
not collect, store or send anything on its own: the statistics live in your own
PlotBird installation.

**PlotBird is not a hosted service.** You need an installation you control,
either on the same hosting account or on another server. The plugin walks you
through it: put the PlotBird folder on your hosting, finish its browser
installer, paste the address and the website id here, then check that visits
arrive.

This is the WordPress client of the PlotBird project
(github.com/plotbird/analytics). The tracking script it loads is served by your
own installation, at the address you enter on the setup screen: the plugin loads
no code from a server of ours, and it sends nothing anywhere except the
installation you named.

What the plugin does:

* Adds one script tag to the page head, about 1.6 KB, loaded asynchronously.
* Skips the WordPress admin, feeds, previews and JSON requests.
* Can skip logged-in administrators, so your own visits do not land in the
  statistics.
* Offers three read-only checks: is there a PlotBird installation at that address,
  does it accept the share link, and did the tracking code reach your front page.

What the plugin does not do:

* No account, no sign-up, no external service of ours.
* No cookies, no fingerprinting, no data of your visitors leaves your server.
* No dashboard inside WordPress: the dashboard is PlotBird itself.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP through
   Plugins, Add New, Upload Plugin.
2. Activate the plugin.
3. Open Settings, PlotBird.
4. Install PlotBird on your hosting if you have not already: download the
   archive from the project page, unpack it into a folder of its own such as
   `/plotbird/`, open that address and finish the installer.
5. Paste the PlotBird address and the website id, save.
6. Use the two check buttons to confirm the address answers and the tracking
   code is on your front page.

== Frequently Asked Questions ==

= Do I need a separate server for PlotBird? =

No. PlotBird runs on PHP and MySQL, so the hosting account your WordPress uses
is usually enough: put it in a folder of its own, for example
`https://example.com/plotbird/`.

= Will this slow my site down? =

The tracker is about 1.6 KB, loads asynchronously and never blocks rendering.

= Does it set cookies? =

No. PlotBird counts visitors with a salted daily hash and stores nothing in the
browser.

= Can I use it with a caching plugin? =

Yes. If the tracking code does not show up on the front page after saving,
clear the cache and run the front page check again.

= What happens when I delete the plugin? =

It removes its two settings and nothing else. The PlotBird installation keeps
running, because it is a separate application.

== Screenshots ==

1. The setup screen, with the three steps and the two checks.

== Changelog ==

= 1.0.2 =
* The description states where the tracking script comes from and that the
  plugin belongs to the PlotBird project, which is what the submission form asks
  about first.

= 1.0.1 =
* Passes Plugin Check with no errors and no warnings: the tracking script is
  registered through wp_enqueue_script(), generated markup is marked as such,
  and the readme declares a major.minor "Tested up to".
* The tracker URL now carries a version, so an update is picked up instead of
  being served from a browser cache.

= 0.1.0 =
* First release: tracking code, setup wizard, address and front page checks.

== Upgrade Notice ==

= 0.1.0 =
First release.
