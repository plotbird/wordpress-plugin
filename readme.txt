=== PlotBird Analytics ===
Contributors: plotbird
Tags: analytics, statistics, privacy, cookieless, self-hosted
Requires at least: 6.0
Tested up to: 7.1.3
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Adds the PlotBird tracking code to your site and shows the statistics inside the WordPress admin. PlotBird itself is self-hosted, cookie-free analytics that runs on ordinary PHP and MySQL hosting.

== Description ==

This plugin connects a WordPress site to a PlotBird installation you control. It
adds the tracking code to your pages and, through a read-only share link, shows
the numbers inside the admin.

**PlotBird is not a hosted service.** You need an installation you control,
either on the same hosting account or on another server. It is a folder of PHP
files: unpack it, open it in a browser, finish the installer. The plugin walks
you through the rest in four steps.

What the plugin does:

* Adds one script tag to the page head, about 1.6 KB, loaded asynchronously.
* Skips the WordPress admin, feeds, previews and JSON requests.
* Can skip logged-in administrators, so your own visits do not land in the
  statistics.
* Shows pageviews, visitors, sessions, average time, bounce rate, a traffic
  chart, top pages, sources, countries, devices, browsers and goals in the admin.
* Offers three checks that test the real thing: does a PlotBird installation
  answer at that address, does it accept the share link, and did the tracking
  code reach your front page.

What the plugin does not do:

* No account, no sign-up, no external service of ours.
* No cookies, no fingerprinting; no data of your visitors leaves your server.
* It never stores an administrator password. Statistics are read through a share
  link, which is read-only and can be revoked at any time.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install the ZIP through
   Plugins, Add New, Upload Plugin.
2. Activate the plugin.
3. Open Settings, PlotBird.
4. Install PlotBird on your hosting if you have not already: download the
   archive from the project page, unpack it into a folder of its own such as
   `/plotbird/`, open that address and finish the installer.
5. Paste the PlotBird address and the website id, save.
6. Create a share link in PlotBird under Websites, Links and goals, and paste it
   too. Leave the date empty for a link that does not expire.
7. Use the check buttons to confirm the address answers, the share link reads
   data, and the tracking code is on your front page.

== Frequently Asked Questions ==

= Do I need a separate server for PlotBird? =

No. PlotBird runs on PHP and MySQL, so the hosting account your WordPress uses
is usually enough: put it in a folder of its own, for example
`https://example.com/plotbird/`.

= I see no statistics. Why? =

The most common reason: you are looking at your own site while logged in. With
"Do not track logged-in administrators" enabled, which is the default, the
plugin does not print the tracking code for you at all. Open the site in a
private window, or untick the option.

Other things to check: a caching plugin may serve a page rendered before the
code was added; a hit appears in the dashboard within a minute; and the website
in PlotBird has to accept the host you browse, `www` included.

= Will this slow my site down? =

The tracker is about 1.6 KB, loads asynchronously and never blocks rendering. The
statistics screen is a single request, cached for a minute, and none of it is
loaded on the public side of the site.

= Does it set cookies? =

No. PlotBird counts visitors with a salted daily hash and stores nothing in the
browser.

= Can I use it with a caching plugin? =

Yes. If the tracking code does not show up on the front page after saving, clear
the cache and run the front page check again.

= What happens when I delete the plugin? =

It removes its own settings and nothing else. The PlotBird installation keeps
running, because it is a separate application.

= Is the plugin itself translated? =

It ships English and Russian, follows the language of your site, and lets you
choose either one on the setup screen.

== Screenshots ==

1. The statistics screen in the WordPress admin: metrics with sparklines, a traffic chart and lists of pages, sources, countries, devices, browsers and goals.

== Changelog ==

= 1.0.0 =
* Statistics inside the admin: metrics, traffic chart, top pages, sources, countries, devices, browsers and goals.
* Share-link reader with clear messages for a revoked, expired or foreign link.
* Four-step setup with three checks that test the real connection.
* English and Russian, following the site language or chosen explicitly.
* The project's bird as the admin menu icon.

== Upgrade Notice ==

= 1.0.0 =
First public release.
