<?php
/**
 * Plugin Name:       PlotBird Analytics
 * Plugin URI:        https://github.com/plotbird/wordpress-plugin
 * Description:       Adds the PlotBird tracking code to your site. PlotBird is self-hosted, cookie-free web analytics that runs on ordinary PHP and MySQL hosting.
 * Version:           1.0.5
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            PlotBird
 * Author URI:        https://plotbird.github.io/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       plotbird-analytics
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

define('PLOTBIRD_ANALYTICS_VERSION', '1.0.5');
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
