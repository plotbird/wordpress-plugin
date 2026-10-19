<?php
/**
 * Checks that the server is really a PlotBird installation and that the tag
 * reached the page.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

/**
 * Two checks, both read-only.
 */
final class PlotBird_Checker {

    /** Transient that carries the last result back to the settings page. */
    public const TRANSIENT = 'plotbird_analytics_check';

    /**
     * Ask the given address for the tracker file.
     *
     * A PlotBird installation always serves /assets/tracker.js, so a successful
     * request with the right content is proof enough that the address is real -
     * and it says nothing about the database, which stays private.
     *
     * @param string $server Address chosen by the operator.
     * @return array{ok:bool,message:string}
     */
    public static function server(string $server): array {
        $server = rtrim(trim($server), '/');

        if (!PlotBird_Settings::is_server_url($server)) {
            return [
                'ok'      => false,
                'message' => __('Enter the full address first, for example https://example.com/plotbird.', 'plotbird-analytics'),
            ];
        }

        $response = wp_remote_get($server . '/assets/tracker.js', [
            'timeout'     => 10,
            'redirection' => 3,
            'headers'     => ['Accept' => 'text/javascript,*/*'],
        ]);

        if (is_wp_error($response)) {
            return [
                'ok'      => false,
                'message' => sprintf(
                    /* translators: %s: error message from WordPress. */
                    __('Could not reach the address: %s', 'plotbird-analytics'),
                    $response->get_error_message()
                ),
            ];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = (string) wp_remote_retrieve_body($response);

        if ($code === 404) {
            return [
                'ok'      => false,
                'message' => __('Nothing answers at that address. Check that the PlotBird folder was uploaded where the address points.', 'plotbird-analytics'),
            ];
        }

        if ($code !== 200) {
            return [
                'ok'      => false,
                'message' => sprintf(
                    /* translators: %d: HTTP status code. */
                    __('The address answered with HTTP %d. A PlotBird installation answers 200 for /assets/tracker.js.', 'plotbird-analytics'),
                    $code
                ),
            ];
        }

        if (stripos($body, 'plotbird') === false) {
            return [
                'ok'      => false,
                'message' => __('Something answers there, but it is not PlotBird: the tracker file is missing its signature.', 'plotbird-analytics'),
            ];
        }

        return [
            'ok'      => true,
            'message' => __('PlotBird answered. Now paste the website id from its dashboard.', 'plotbird-analytics'),
        ];
    }

    /**
     * Fetch the front page and look for the tag the plugin should have printed.
     *
     * @return array{ok:bool,message:string}
     */
    public static function page(): array {
        if (PlotBird_Settings::state() !== 'ready') {
            return [
                'ok'      => false,
                'message' => __('Finish the two steps above first.', 'plotbird-analytics'),
            ];
        }

        $response = wp_remote_get(add_query_arg('plotbird_check', (string) time(), home_url('/')), [
            'timeout'     => 10,
            'redirection' => 3,
        ]);

        if (is_wp_error($response)) {
            return [
                'ok'      => false,
                'message' => sprintf(
                    /* translators: %s: error message from WordPress. */
                    __('Could not load your front page: %s', 'plotbird-analytics'),
                    $response->get_error_message()
                ),
            ];
        }

        $body = (string) wp_remote_retrieve_body($response);

        if (strpos($body, PlotBird_Settings::website_id()) === false) {
            return [
                'ok'      => false,
                'message' => __('The tracking code is not in the page yet. If you use a caching plugin, clear its cache and check again.', 'plotbird-analytics'),
            ];
        }

        return [
            'ok'      => true,
            'message' => __('The tracking code is on the page. Open the site in another window and the first visit should appear in PlotBird within a minute.', 'plotbird-analytics'),
        ];
    }

    /**
     * Remember the outcome for the redirect that follows.
     *
     * @param string $key   Check name.
     * @param array{ok:bool,message:string} $result Outcome.
     */
    public static function remember(string $key, array $result): void {
        set_transient(self::TRANSIENT, ['key' => $key, 'result' => $result], 60);
    }

    /**
     * Read and clear the stored outcome.
     *
     * @return array{key:string,result:array{ok:bool,message:string}}|null
     */
    public static function recall(): ?array {
        $stored = get_transient(self::TRANSIENT);

        if (!is_array($stored) || !isset($stored['key'], $stored['result'])) {
            return null;
        }

        delete_transient(self::TRANSIENT);

        return [
            'key'    => (string) $stored['key'],
            'result' => [
                'ok'      => (bool) ($stored['result']['ok'] ?? false),
                'message' => (string) ($stored['result']['message'] ?? ''),
            ],
        ];
    }
}
