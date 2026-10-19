<?php
/**
 * Runs when the plugin is deleted from WordPress.
 *
 * Removing the plugin must leave nothing behind that a reinstalled copy could
 * pick up as stale state.
 *
 * @package PlotBird_Analytics
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('plotbird_analytics_settings');
delete_transient('plotbird_analytics_check');

// Multisite: the option is per site.
if (is_multisite()) {
    $plotbird_sites = get_sites(['fields' => 'ids', 'number' => 0]);

    foreach ($plotbird_sites as $plotbird_site_id) {
        switch_to_blog((int) $plotbird_site_id);
        delete_option('plotbird_analytics_settings');
        delete_transient('plotbird_analytics_check');
        restore_current_blog();
    }
}
