<?php
/**
 * Registers the tracking script with WordPress.
 *
 * The script lives on the PlotBird installation, which may be on another domain,
 * so WordPress cannot bundle it. Enqueuing it is still the right way to do this:
 * the handle, the attributes and the ordering all stay in the plugin's hands, and
 * the tag is printed by WordPress rather than pasted into the head.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

/**
 * Registers, enqueues and decorates the tracking script.
 */
final class PlotBird_Tracker {

    /** Handle of the script. */
    public const HANDLE = 'plotbird-analytics';

    /**
     * Attach to the front end.
     */
    public static function register(): void {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue']);
        add_filter('script_loader_tag', [self::class, 'add_attributes'], 10, 3);
    }

    /**
     * Whether the tracking code belongs on this request.
     */
    public static function should_print(): bool {
        if (PlotBird_Settings::state() !== 'ready') {
            return false;
        }

        if (is_admin() || is_feed() || is_robots() || is_preview() || is_customize_preview()) {
            return false;
        }

        if (function_exists('wp_is_json_request') && wp_is_json_request()) {
            return false;
        }

        return !PlotBird_Settings::skips_current_user();
    }

    /**
     * Queue the script.
     */
    public static function enqueue(): void {
        if (!self::should_print()) {
            return;
        }

        wp_enqueue_script(
            self::HANDLE,
            PlotBird_Settings::tracker_url(),
            [],
            PLOTBIRD_ANALYTICS_VERSION,
            false
        );
    }

    /**
     * Add the tracking attributes to the tag WordPress prints.
     *
     * @param string $tag    The script tag.
     * @param string $handle Script handle.
     * @param string $src    Script source.
     */
    public static function add_attributes(string $tag, string $handle, string $src): string {
        if ($handle !== self::HANDLE) {
            return $tag;
        }

        $attributes = sprintf(
            ' async defer data-website-id="%s"',
            esc_attr(PlotBird_Settings::website_id())
        );

        // Injected at the src attribute, so the plugin never builds a script tag
        // by hand: WordPress owns the tag, this only decorates it.
        return str_replace(' src=', $attributes . ' src=', $tag);
    }
}
