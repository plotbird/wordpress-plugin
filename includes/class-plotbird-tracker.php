<?php
/**
 * Prints the tracking code in the page head.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

/**
 * Adds one script tag and nothing else.
 */
final class PlotBird_Tracker {

    /**
     * Attach the output to the head.
     */
    public static function register(): void {
        add_action('wp_head', [self::class, 'print_tag'], 20);
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
     * Output the tag.
     */
    public static function print_tag(): void {
        if (!self::should_print()) {
            return;
        }

        printf(
            '<script async defer src="%1$s" data-website-id="%2$s"></script>' . "\n",
            esc_url(PlotBird_Settings::tracker_url()),
            esc_attr(PlotBird_Settings::website_id())
        );
    }
}
