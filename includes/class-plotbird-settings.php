<?php
/**
 * Stored settings and their validation.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

/**
 * Reads, validates and saves the two values the plugin needs.
 */
final class PlotBird_Settings {

    public const OPTION = 'plotbird_analytics_settings';

    /**
     * Defaults for a fresh install.
     *
     * @return array<string,mixed>
     */
    public static function defaults(): array {
        return [
            'server_url'   => '',
            'website_id'   => '',
            'skip_admins'  => 1,
            'share_link'   => '',
            ];
    }

    /**
     * Current settings, merged over the defaults so a missing key never breaks.
     *
     * @return array<string,mixed>
     */
    public static function all(): array {
        $stored = get_option(self::OPTION, []);

        if (!is_array($stored)) {
            $stored = [];
        }

        return array_merge(self::defaults(), $stored);
    }

    /**
     * One setting.
     *
     * @param string $key Setting name.
     * @return mixed
     */
    public static function get(string $key) {
        $all = self::all();
        return $all[$key] ?? null;
    }

    /**
     * The server address without a trailing slash, or an empty string.
     */
    public static function server_url(): string {
        return rtrim((string) self::get('server_url'), '/');
    }

    /**
     * The website identifier, or an empty string.
     */
    public static function website_id(): string {
        return (string) self::get('website_id');
    }

    /**
     * Address the tracker is loaded from.
     */
    public static function tracker_url(): string {
        $server = self::server_url();
        return $server === '' ? '' : $server . '/assets/tracker.js';
    }

    /**
     * Whether the visitor is an administrator who should not be counted.
     */
    public static function skips_current_user(): bool {
        return (int) self::get('skip_admins') === 1 && current_user_can('manage_options');
    }

    /**
     * Is a website id shaped like the identifier PlotBird generates?
     *
     * @param string $value Candidate.
     */
    public static function is_website_id(string $value): bool {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }

    /**
     * Is the address usable as a PlotBird server?
     *
     * @param string $value Candidate.
     */
    public static function is_server_url(string $value): bool {
        if ($value === '') {
            return false;
        }

        $parts = wp_parse_url($value);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        return in_array(strtolower((string) $parts['scheme']), ['http', 'https'], true);
    }

    /**
     * Sanitise everything that arrives from the settings form.
     *
     * @param mixed $input Raw form values.
     * @return array<string,mixed>
     */
    public static function sanitize($input): array {
        $input = is_array($input) ? $input : [];
        $clean = self::defaults();

        $server = isset($input['server_url']) ? trim((string) $input['server_url']) : '';
        if ($server !== '') {
            $server = untrailingslashit(esc_url_raw($server));
            if (!self::is_server_url($server)) {
                add_settings_error(
                    self::OPTION,
                    'plotbird_server_invalid',
                    __('The server address must start with http:// or https://.', 'plotbird-analytics'),
                    'error'
                );
                $server = '';
            }
        }
        $clean['server_url'] = $server;

        $website = isset($input['website_id']) ? trim((string) $input['website_id']) : '';
        if ($website !== '' && !self::is_website_id($website)) {
            add_settings_error(
                self::OPTION,
                'plotbird_website_invalid',
                __('A website id looks like 00000000-0000-0000-0000-000000000000.', 'plotbird-analytics'),
                'error'
            );
            $website = '';
        }
        $clean['website_id'] = strtolower($website);

        $clean['skip_admins'] = empty($input['skip_admins']) ? 0 : 1;

        // A share link is the only thing the plugin needs to read statistics, and
        // an empty value means "no statistics screen yet".
        $share = isset($input['share_link']) ? trim((string) $input['share_link']) : '';
        if ($share !== '') {
            $share = esc_url_raw($share);
            if (preg_match('#/share/[A-Za-z0-9]{16,128}$#', $share) !== 1) {
                add_settings_error(
                    self::OPTION,
                    'plotbird_share_invalid',
                    __('The share link looks like https://your-plotbird.example/share/abc123... Copy it from PlotBird under Websites, then Links and goals.', 'plotbird-analytics'),
                    'error'
                );
                $share = '';
            }
        }
        $clean['share_link'] = $share;


        return $clean;
    }

    /**
     * Where the setup stands, in the order the wizard walks through it.
     *
     * @return string missing_server|missing_website|ready
     */
    public static function state(): string {
        if (self::server_url() === '') {
            return 'missing_server';
        }

        if (!self::is_website_id(self::website_id())) {
            return 'missing_website';
        }

        return 'ready';
    }
}
