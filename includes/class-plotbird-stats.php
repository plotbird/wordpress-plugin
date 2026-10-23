<?php
/**
 * Reads statistics from a PlotBird installation through a share link.
 *
 * The share link is the credential: it is read-only, revocable and can expire,
 * which is exactly what a plugin may hold. Nothing here can change anything on
 * the server.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

/**
 * Fetches, caches and validates the statistics payload.
 */
final class PlotBird_Stats {

    /** How long a response stays in the transient. */
    public const CACHE_SECONDS = 60;

    /** Presets the plugin offers. */
    public const PRESETS = ['today', 'last7', 'last30'];

    /**
     * The share token, taken from the stored link or typed on its own.
     */
    public static function token(): string {
        $value = trim((string) PlotBird_Settings::get('share_link'));

        if ($value === '') {
            return '';
        }

        // A full link is the normal case; a bare token is accepted too.
        if (strpos($value, '://') !== false || strpos($value, '/') !== false) {
            $parts = wp_parse_url($value);
            $path  = isset($parts['path']) ? trim((string) $parts['path'], '/') : '';
            $value = $path === '' ? '' : (string) substr($path, (int) strrpos($path, '/') + 1);
        }

        return preg_match('/^[A-Za-z0-9]{16,128}$/', $value) === 1 ? $value : '';
    }

    /**
     * Whether a share link has been stored at all.
     */
    public static function has_token(): bool {
        return self::token() !== '';
    }

    /**
     * Address the plugin reads from.
     *
     * @param string $preset One of PRESETS.
     */
    public static function api_url(string $preset): string {
        $server = PlotBird_Settings::server_url();

        return $server . '/api/stats.php?' . http_build_query([
            'share'  => self::token(),
            'preset' => $preset,
        ]);
    }

    /**
     * Fetch the payload, or a clear reason why there is none.
     *
     * @param string $preset One of PRESETS.
     * @param bool   $force  Ignore the cache.
     * @return array{ok:bool,error:string,message:string,data:array<string,mixed>}
     */
    public static function fetch(string $preset, bool $force = false): array {
        $empty = ['ok' => false, 'error' => '', 'message' => '', 'data' => []];

        if (!in_array($preset, self::PRESETS, true)) {
            $preset = 'last7';
        }

        if (PlotBird_Settings::server_url() === '') {
            return ['error' => 'no_server'] + $empty;
        }

        if (!self::has_token()) {
            return ['error' => 'no_link'] + $empty;
        }

        $cacheKey = 'plotbird_stats_' . md5(self::token() . '|' . $preset);
        if (!$force) {
            $cached = get_transient($cacheKey);
            if (is_array($cached)) {
                return ['ok' => true, 'error' => '', 'message' => '', 'data' => $cached];
            }
        }

        $response = wp_remote_get(self::api_url($preset), [
            'timeout'     => 10,
            'redirection' => 2,
            'headers'     => ['Accept' => 'application/json'],
        ]);

        if (is_wp_error($response)) {
            return ['error' => 'unreachable', 'message' => $response->get_error_message()] + $empty;
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);

        if ($code === 403 || $code === 404) {
            return ['error' => 'invalid_link'] + $empty;
        }

        if ($code !== 200) {
            return ['error' => 'http', 'message' => 'HTTP ' . $code] + $empty;
        }

        $data = json_decode($body, true);

        if (!is_array($data) || isset($data['error'])) {
            return ['error' => 'not_json'] + $empty;
        }

        if (($data['scope'] ?? '') !== 'share') {
            return ['error' => 'not_share'] + $empty;
        }

        // The link may belong to another website of the same installation, which
        // would silently show somebody else's numbers.
        $linkedSite = (string) ($data['site']['public_id'] ?? '');
        $wantedSite = PlotBird_Settings::website_id();
        if ($linkedSite !== '' && $wantedSite !== '' && $linkedSite !== $wantedSite) {
            return ['error' => 'other_site'] + $empty;
        }

        set_transient($cacheKey, $data, self::CACHE_SECONDS);

        return ['ok' => true, 'error' => '', 'message' => '', 'data' => $data];
    }

    /**
     * Drop the cached payload for every preset.
     */
    public static function flush(): void {
        foreach (self::PRESETS as $preset) {
            delete_transient('plotbird_stats_' . md5(self::token() . '|' . $preset));
        }
    }

    /**
     * A sentence explaining a failure, in the reader's language.
     */
    public static function explain(string $error): string {
        switch ($error) {
            case 'no_server':
                return __('Enter the PlotBird address first.', 'plotbird-analytics');
            case 'no_link':
                return __('Statistics are read through a share link. Create one in PlotBird under Websites, then Links and goals.', 'plotbird-analytics');
            case 'invalid_link':
                return __('PlotBird does not accept this link: it was revoked, it expired, or it points at another installation.', 'plotbird-analytics');
            case 'other_site':
                return __('This link belongs to a different website of the same installation. Create a link for the site whose id is above.', 'plotbird-analytics');
            case 'not_share':
                return __('That address answered, but not with share statistics. Check that the link was copied from PlotBird.', 'plotbird-analytics');
            case 'not_json':
                return __('That address did not answer with statistics. A PlotBird installation answers /api/stats.php with JSON.', 'plotbird-analytics');
            case 'unreachable':
                return __('PlotBird could not be reached from this server.', 'plotbird-analytics');
            default:
                return __('PlotBird answered with an error.', 'plotbird-analytics');
        }
    }

    /**
     * Number formatting that follows the language of the screens.
     *
     * @param mixed $value Raw value.
     * @param int   $decimals Digits after the decimal separator.
     */
    public static function number($value, int $decimals = 0): string {
        $locale = (string) get_locale();

        if (strpos($locale, 'ru') === 0) {
            return number_format((float) $value, $decimals, ',', ' ');
        }

        if (strpos($locale, 'en') === 0) {
            return number_format((float) $value, $decimals, '.', ',');
        }

        return number_format_i18n((float) $value, $decimals);
    }

    /**
     * Seconds as "1 m 17 s".
     *
     * @param mixed $seconds Raw value.
     */
    public static function duration($seconds): string {
        $seconds = (int) round((float) $seconds);
        if ($seconds < 60) {
            return sprintf(
                /* translators: %d: number of seconds. */
                __('%d s', 'plotbird-analytics'),
                $seconds
            );
        }

        $minutes = (int) floor($seconds / 60);
        $rest    = $seconds % 60;

        return $rest === 0
            ? sprintf(
                /* translators: %d: number of minutes. */
                __('%d min', 'plotbird-analytics'),
                $minutes
            )
            : sprintf(
                /* translators: 1: minutes, 2: seconds. */
                __('%1$d min %2$d s', 'plotbird-analytics'),
                $minutes,
                $rest
            );
    }

    /**
     * A percentage with one decimal.
     *
     * @param mixed $ratio Raw value between 0 and 1.
     */
    public static function percent($ratio): string {
        return self::number((float) $ratio * 100, 1) . '%';
    }
}
