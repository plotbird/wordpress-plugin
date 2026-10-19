<?php
/**
 * Admin screens: the statistics page and the setup wizard.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

/**
 * Registers both pages, the setting and the check actions.
 */
final class PlotBird_Admin {

    /** Capability required to see and change anything here. */
    public const CAPABILITY = 'manage_options';

    /** Settings page slug. */
    public const SLUG = 'plotbird-analytics';

    /** Statistics page slug. */
    public const STATS_SLUG = 'plotbird-stats';

    /**
     * Hooks.
     */
    public static function register(): void {
        add_action('admin_menu', [self::class, 'menu']);
        add_action('admin_init', [self::class, 'settings']);
        add_action('admin_enqueue_scripts', [self::class, 'assets']);
        add_action('admin_post_plotbird_check_server', [self::class, 'handle_check_server']);
        add_action('admin_post_plotbird_check_page', [self::class, 'handle_check_page']);
        add_filter('plugin_action_links_' . plugin_basename(PLOTBIRD_ANALYTICS_FILE), [self::class, 'action_links']);
    }

    /**
     * A top-level entry for the statistics, settings underneath Settings.
     */
    public static function menu(): void {
        add_menu_page(
            __('PlotBird', 'plotbird-analytics'),
            __('PlotBird', 'plotbird-analytics'),
            self::CAPABILITY,
            self::STATS_SLUG,
            [self::class, 'render_stats'],
            'dashicons-chart-area',
            3
        );

        add_submenu_page(
            self::STATS_SLUG,
            __('Statistics', 'plotbird-analytics'),
            __('Statistics', 'plotbird-analytics'),
            self::CAPABILITY,
            self::STATS_SLUG,
            [self::class, 'render_stats']
        );

        add_submenu_page(
            self::STATS_SLUG,
            __('Setup', 'plotbird-analytics'),
            __('Setup', 'plotbird-analytics'),
            self::CAPABILITY,
            self::SLUG,
            [self::class, 'render']
        );

        // The same screen is reachable from Settings, where people look first.
        add_options_page(
            __('PlotBird', 'plotbird-analytics'),
            __('PlotBird', 'plotbird-analytics'),
            self::CAPABILITY,
            self::SLUG,
            [self::class, 'render']
        );
    }

    /**
     * Register the option so options.php can save it.
     */
    public static function settings(): void {
        register_setting('plotbird_analytics', PlotBird_Settings::OPTION, [
            'type'              => 'array',
            'sanitize_callback' => [PlotBird_Settings::class, 'sanitize'],
            'default'           => PlotBird_Settings::defaults(),
        ]);
    }

    /**
     * Styles for these pages only.
     *
     * @param string $hook Current admin page.
     */
    public static function assets(string $hook): void {
        // The menu icon is part of the sidebar, so it is needed on every screen.
        wp_enqueue_style(
            'plotbird-analytics-menu',
            PLOTBIRD_ANALYTICS_URL . 'admin/assets/menu.css',
            [],
            PLOTBIRD_ANALYTICS_VERSION
        );

        $ours = [
            'toplevel_page_' . self::STATS_SLUG,
            'plotbird_page_' . self::SLUG,
            'settings_page_' . self::SLUG,
        ];

        if (!in_array($hook, $ours, true)) {
            return;
        }

        wp_enqueue_style(
            'plotbird-analytics-admin',
            PLOTBIRD_ANALYTICS_URL . 'admin/assets/admin.css',
            ['plotbird-analytics-menu'],
            PLOTBIRD_ANALYTICS_VERSION
        );
    }

    /**
     * A direct link from the plugins list.
     *
     * @param list<string> $links Existing links.
     * @return list<string>
     */
    public static function action_links(array $links): array {
        array_unshift(
            $links,
            '<a href="' . esc_url(self::page_url()) . '">' . esc_html__('Setup', 'plotbird-analytics') . '</a>',
            '<a href="' . esc_url(self::stats_url()) . '">' . esc_html__('Statistics', 'plotbird-analytics') . '</a>'
        );
        return $links;
    }

    /**
     * Run the server check and come back.
     */
    public static function handle_check_server(): void {
        self::authorize();
        check_admin_referer('plotbird_check_server');

        $server = isset($_POST['server_url']) ? sanitize_text_field(wp_unslash((string) $_POST['server_url'])) : '';
        PlotBird_Checker::remember('server', PlotBird_Checker::server($server));

        wp_safe_redirect(self::page_url());
        exit;
    }

    /**
     * Run the front-page check and come back.
     */
    public static function handle_check_page(): void {
        self::authorize();
        check_admin_referer('plotbird_check_page');

        PlotBird_Checker::remember('page', PlotBird_Checker::page());

        wp_safe_redirect(self::page_url());
        exit;
    }

    /**
     * Draw the setup screen.
     */
    public static function render(): void {
        if (!current_user_can(self::CAPABILITY)) {
            return;
        }

        $settings = PlotBird_Settings::all();
        $state    = PlotBird_Settings::state();
        $notice   = PlotBird_Checker::recall();

        require PLOTBIRD_ANALYTICS_DIR . 'admin/views/settings-page.php';
    }

    /**
     * Draw the statistics screen.
     */
    public static function render_stats(): void {
        if (!current_user_can(self::CAPABILITY)) {
            return;
        }

        $preset = isset($_GET['preset']) ? sanitize_key(wp_unslash((string) $_GET['preset'])) : 'last7';
        if (!in_array($preset, PlotBird_Stats::PRESETS, true)) {
            $preset = 'last7';
        }

        $force = isset($_GET['plotbird_refresh']);
        if ($force) {
            PlotBird_Stats::flush();
        }

        $result  = PlotBird_Stats::fetch($preset, (bool) $force);
        $settings = PlotBird_Settings::all();

        require PLOTBIRD_ANALYTICS_DIR . 'admin/views/stats-page.php';
    }

    /**
     * Refuse anyone who may not be here.
     */
    private static function authorize(): void {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('You are not allowed to change these settings.', 'plotbird-analytics'), 403);
        }
    }

    /**
     * Address of the setup screen.
     */
    private static function page_url(): string {
        return admin_url('options-general.php?page=' . self::SLUG);
    }

    /**
     * Address of the statistics screen.
     */
    private static function stats_url(): string {
        return admin_url('admin.php?page=' . self::STATS_SLUG);
    }
}
