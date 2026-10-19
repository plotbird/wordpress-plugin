<?php
/**
 * Plugin Name:       PlotBird Analytics
 * Plugin URI:        https://plotbird.github.io/
 * Description:       Adds the PlotBird tracking code to your site. PlotBird is self-hosted, cookie-free web analytics that runs on ordinary PHP and MySQL hosting.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            PlotBird
 * Author URI:        https://plotbird.github.io/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       plotbird-analytics
 * Domain Path:       /languages
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

define('PLOTBIRD_ANALYTICS_VERSION', '1.0.0');
define('PLOTBIRD_ANALYTICS_FILE', __FILE__);
define('PLOTBIRD_ANALYTICS_DIR', plugin_dir_path(__FILE__));
define('PLOTBIRD_ANALYTICS_URL', plugin_dir_url(__FILE__));

require_once PLOTBIRD_ANALYTICS_DIR . 'includes/class-plotbird-settings.php';
require_once PLOTBIRD_ANALYTICS_DIR . 'includes/class-plotbird-checker.php';
require_once PLOTBIRD_ANALYTICS_DIR . 'includes/class-plotbird-tracker.php';
require_once PLOTBIRD_ANALYTICS_DIR . 'includes/class-plotbird-stats.php';
require_once PLOTBIRD_ANALYTICS_DIR . 'includes/class-plotbird-chart.php';
require_once PLOTBIRD_ANALYTICS_DIR . 'includes/class-plotbird-admin.php';

/**
 * Load the translations, either for the site's language or for the one chosen
 * on the setup screen.
 *
 * Following the site is the default and needs nothing here: WordPress asks for
 * plotbird-analytics-<locale>.mo by itself, so a Russian site gets Russian
 * screens without anyone opening a setting.
 *
 * When a language is chosen explicitly the catalogue has to be loaded by hand.
 * WordPress 6.7 loads translations just in time using the site locale, which
 * ignores the plugin_locale filter, so an override has to name the file.
 */
add_action('init', static function (): void {
    $chosen = (string) PlotBird_Settings::get('language');
    $file   = PLOTBIRD_ANALYTICS_DIR . 'languages/plotbird-analytics-' . $chosen . '.mo';

    if ($chosen !== 'site' && is_file($file)) {
        unload_textdomain('plotbird-analytics');
        load_textdomain('plotbird-analytics', $file);
        return;
    }

    load_plugin_textdomain('plotbird-analytics', false, dirname(plugin_basename(PLOTBIRD_ANALYTICS_FILE)) . '/languages');
}, 1);

/**
 * Store the default settings on activation, without overwriting a repeat run.
 */
register_activation_hook(PLOTBIRD_ANALYTICS_FILE, static function (): void {
    $existing = get_option(PlotBird_Settings::OPTION, null);
    if ($existing === null) {
        add_option(PlotBird_Settings::OPTION, PlotBird_Settings::defaults());
    }
});

PlotBird_Tracker::register();
PlotBird_Admin::register();
