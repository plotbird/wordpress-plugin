<?php
/**
 * The statistics screen shown inside WordPress.
 *
 * @var array{ok:bool,error:string,message:string,data:array<string,mixed>} $result What the API answered.
 * @var string $preset Active period.
 * @var array<string,mixed> $settings Stored settings.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

$plotbird_data      = (array) ($result['data'] ?? []);
$plotbird_summary   = (array) ($plotbird_data['summary'] ?? []);
$plotbird_series    = (array) ($plotbird_data['series'] ?? []);
$plotbird_spark     = (array) ($plotbird_data['sparklines'] ?? []);
$plotbird_top       = (array) ($plotbird_data['top'] ?? []);
$plotbird_breakdown = (array) ($plotbird_data['breakdown'] ?? []);
$plotbird_goals     = (array) ($plotbird_data['goals'] ?? []);
$plotbird_site      = (array) ($plotbird_data['site'] ?? []);
$plotbird_realtime  = (array) ($plotbird_data['realtime'] ?? []);
$plotbird_previous  = (array) ($plotbird_data['previous'] ?? []);

$plotbird_periods = [
    'today'  => __('Today', 'plotbird-analytics'),
    'last7'  => __('7 days', 'plotbird-analytics'),
    'last30' => __('30 days', 'plotbird-analytics'),
];

/**
 * Build one row set for the bar lists.
 *
 * @param list<array<string,mixed>> $rows  Raw rows from the payload.
 * @param string                    $field Field that holds the number.
 * @param callable|null             $decorate Builds the label markup from a row.
 * @return list<array{label:string,value:string,count:float,share:float,html:string}>
 */
$plotbird_rows = static function (array $rows, string $field, ?callable $decorate = null): array {
    $rows  = array_values($rows);
    $total = 0.0;
    foreach ($rows as $row) {
        $total += (float) ($row[$field] ?? 0);
    }

    $prepared = [];
    foreach ($rows as $row) {
        $count = (float) ($row[$field] ?? 0);
        $label = (string) ($row['value'] ?? '');
        $html  = $decorate !== null ? (string) $decorate($row) : esc_html($label === '' ? '—' : $label);

        $prepared[] = [
            'label' => $label,
            'value' => PlotBird_Stats::number($count),
            'count' => $count,
            'share' => $total > 0 ? $count / $total : 0.0,
            'html'  => $html,
        ];
    }

    return $prepared;
};

$plotbird_cards = [
    [
        'label' => __('Pageviews', 'plotbird-analytics'),
        'value' => PlotBird_Stats::number($plotbird_summary['pageviews'] ?? 0),
        'delta' => $plotbird_previous['delta']['pageviews'] ?? null,
        'spark' => (array) ($plotbird_spark['pageviews'] ?? []),
        'colour' => '#2271b1',
    ],
    [
        'label' => __('Visitors', 'plotbird-analytics'),
        'value' => PlotBird_Stats::number($plotbird_summary['visitors'] ?? 0),
        'delta' => $plotbird_previous['delta']['visitors'] ?? null,
        'spark' => (array) ($plotbird_spark['visitors'] ?? []),
        'colour' => '#00a32a',
    ],
    [
        'label' => __('Sessions', 'plotbird-analytics'),
        'value' => PlotBird_Stats::number($plotbird_summary['sessions'] ?? 0),
        'delta' => $plotbird_previous['delta']['sessions'] ?? null,
        'spark' => (array) ($plotbird_spark['sessions'] ?? []),
        'colour' => '#8c5ee8',
    ],
    [
        'label' => __('Average time', 'plotbird-analytics'),
        'value' => PlotBird_Stats::duration($plotbird_summary['avg_duration'] ?? 0),
        'delta' => null,
        'spark' => [],
        'colour' => '#996800',
    ],
    [
        'label' => __('Bounce rate', 'plotbird-analytics'),
        'value' => PlotBird_Stats::percent($plotbird_summary['bounce_rate'] ?? 0),
        'delta' => null,
        'spark' => [],
        'colour' => '#b32d2e',
    ],
];
?>
<div class="wrap plotbird-wrap plotbird-stats">

    <div class="plotbird-header">
        <div>
            <h1><?php esc_html_e('PlotBird', 'plotbird-analytics'); ?></h1>
            <p class="plotbird-lead">
                <strong><?php echo esc_html((string) ($plotbird_site['name'] ?? __('your website', 'plotbird-analytics'))); ?></strong>
                <?php if (!empty($plotbird_site['domain'])) : ?>
                    · <code><?php echo esc_html((string) $plotbird_site['domain']); ?></code>
                <?php endif; ?>
                · <?php echo esc_html((string) (($plotbird_data['period']['from'] ?? '') . ' — ' . ($plotbird_data['period']['to'] ?? ''))); ?>
                <?php if (!empty($plotbird_realtime['visitors'])) : ?>
                    <span class="plotbird-pill">
                        <i></i>
                        <?php
                        printf(
                            /* translators: %s: number of visitors in the last minutes. */
                            esc_html__('%s online now', 'plotbird-analytics'),
                            esc_html(PlotBird_Stats::number((int) $plotbird_realtime['visitors']))
                        );
                        ?>
                    </span>
                <?php endif; ?>
            </p>
        </div>

        <div class="plotbird-toolbar">
            <nav class="plotbird-periods" aria-label="<?php esc_attr_e('Period', 'plotbird-analytics'); ?>">
                <?php foreach ($plotbird_periods as $plotbird_key => $plotbird_label) : ?>
                    <a href="<?php echo esc_url(add_query_arg(['page' => PlotBird_Admin::STATS_SLUG, 'preset' => $plotbird_key, 'plotbird_refresh' => null])); ?>"
                       class="<?php echo $preset === $plotbird_key ? 'is-active' : ''; ?>">
                        <?php echo esc_html($plotbird_label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($result['ok']) : ?>
                <a class="button button-primary" href="<?php echo esc_url(PlotBird_Settings::server_url() . '/'); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Open the full dashboard', 'plotbird-analytics'); ?>
                </a>
            <?php endif; ?>

            <a class="button" href="<?php echo esc_url(add_query_arg(['page' => PlotBird_Admin::STATS_SLUG, 'preset' => $preset, 'plotbird_refresh' => '1'])); ?>">
                <?php esc_html_e('Refresh', 'plotbird-analytics'); ?>
            </a>
            <a class="button" href="<?php echo esc_url(admin_url('options-general.php?page=' . PlotBird_Admin::SLUG)); ?>">
                <?php esc_html_e('Setup', 'plotbird-analytics'); ?>
            </a>
        </div>
    </div>

    <?php if (!$result['ok']) : ?>
        <div class="notice notice-<?php echo $result['error'] === 'no_link' ? 'info' : 'error'; ?>">
            <p><?php echo esc_html(PlotBird_Stats::explain($result['error'])); ?></p>
            <?php if ($result['message'] !== '') : ?>
                <p class="description"><code><?php echo esc_html($result['message']); ?></code></p>
            <?php endif; ?>
            <p>
                <a class="button button-primary" href="<?php echo esc_url(admin_url('options-general.php?page=' . PlotBird_Admin::SLUG)); ?>">
                    <?php esc_html_e('Open the setup', 'plotbird-analytics'); ?>
                </a>
                <?php if (in_array($result['error'], ['invalid_link', 'other_site', 'not_share'], true)) : ?>
                    <a class="button" href="<?php echo esc_url(PlotBird_Settings::server_url() . '/'); ?>" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e('Create a new link in PlotBird', 'plotbird-analytics'); ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>
    <?php else : ?>

        <?php // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- charts and lists are markup built and escaped inside PlotBird_Chart. ?>
        <div class="plotbird-cards">
            <?php foreach ($plotbird_cards as $plotbird_card) : ?>
                <div class="plotbird-card plotbird-stat">
                    <span class="plotbird-stat-label"><?php echo esc_html($plotbird_card['label']); ?></span>
                    <div class="plotbird-stat-value">
                        <strong><?php echo esc_html((string) $plotbird_card['value']); ?></strong>
                        <?php if (is_array($plotbird_card['delta']) && isset($plotbird_card['delta']['text'])) : ?>
                            <span class="plotbird-delta is-<?php echo esc_attr((string) ($plotbird_card['delta']['direction'] ?? 'flat')); ?>">
                                <?php echo esc_html((string) $plotbird_card['delta']['text']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php
                    if (!empty($plotbird_card['spark'])) {
                        echo PlotBird_Chart::spark( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                            (array) $plotbird_card['spark'],
                            (string) $plotbird_card['colour']
                        );
                    }
                    ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="plotbird-card plotbird-card-wide">
            <h2><?php esc_html_e('Traffic', 'plotbird-analytics'); ?></h2>
            <?php
            echo PlotBird_Chart::plot( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                (array) ($plotbird_series['labels'] ?? []),
                [
                    [
                        'label'  => __('Pageviews', 'plotbird-analytics'),
                        'values' => (array) ($plotbird_series['pageviews'] ?? []),
                        'colour' => '#2271b1',
                        'total'  => array_sum(array_map('floatval', (array) ($plotbird_series['pageviews'] ?? []))),
                    ],
                    [
                        'label'  => __('Visitors', 'plotbird-analytics'),
                        'values' => (array) ($plotbird_series['visitors'] ?? []),
                        'colour' => '#00a32a',
                        'total'  => array_sum(array_map('floatval', (array) ($plotbird_series['visitors'] ?? []))),
                    ],
                ]
            );
            ?>
        </div>

        <div class="plotbird-columns">
            <div class="plotbird-card">
                <h2><?php esc_html_e('Top pages', 'plotbird-analytics'); ?></h2>
                <?php
                echo PlotBird_Chart::bars( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                    $plotbird_rows((array) ($plotbird_top['path'] ?? []), 'pageviews', static function (array $row): string {
                        $path = (string) ($row['value'] ?? '/');
                        return '<code>' . esc_html($path === '' ? '/' : $path) . '</code>';
                    })
                );
                ?>
            </div>

            <div class="plotbird-card">
                <h2><?php esc_html_e('Sources', 'plotbird-analytics'); ?></h2>
                <?php
                echo PlotBird_Chart::bars( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                    $plotbird_rows((array) ($plotbird_top['referrer'] ?? []), 'pageviews', static function (array $row): string {
                        $value = (string) ($row['value'] ?? '');
                        return esc_html($value === '' ? __('Direct / none', 'plotbird-analytics') : $value);
                    }),
                    '#8c5ee8'
                );
                ?>
            </div>

            <div class="plotbird-card">
                <h2><?php esc_html_e('Countries', 'plotbird-analytics'); ?></h2>
                <?php
                echo PlotBird_Chart::bars( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                    $plotbird_rows((array) ($plotbird_top['country'] ?? []), 'visitors', static function (array $row): string {
                        $code = (string) ($row['value'] ?? '');
                        $name = (string) ($row['name'] ?? '');
                        $flag = (string) ($row['flag_url'] ?? '');
                        $html = '';
                        if ($flag !== '') {
                            $html .= '<img class="plotbird-flag" src="' . esc_url($flag) . '" alt="" width="18" height="13" loading="lazy">';
                        }
                        $html .= esc_html($name !== '' ? $name : ($code === '' ? '—' : $code));

                        return $html;
                    }),
                    '#00a32a'
                );
                ?>
            </div>
        </div>

        <div class="plotbird-columns">
            <div class="plotbird-card">
                <h2><?php esc_html_e('Devices', 'plotbird-analytics'); ?></h2>
                <?php
                echo PlotBird_Chart::bars( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                    $plotbird_rows((array) ($plotbird_breakdown['device'] ?? []), 'visitors', static function (array $row): string {
                        $value = (string) ($row['value'] ?? '');
                        $known = [
                            'desktop' => __('Desktop', 'plotbird-analytics'),
                            'mobile'  => __('Mobile', 'plotbird-analytics'),
                            'tablet'  => __('Tablet', 'plotbird-analytics'),
                        ];
                        return esc_html($known[$value] ?? ucfirst($value));
                    })
                );
                ?>
            </div>

            <div class="plotbird-card">
                <h2><?php esc_html_e('Browsers', 'plotbird-analytics'); ?></h2>
                <?php
                echo PlotBird_Chart::bars( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                    $plotbird_rows((array) ($plotbird_breakdown['browser'] ?? []), 'visitors'),
                    '#996800'
                );
                ?>
            </div>

            <div class="plotbird-card">
                <h2><?php esc_html_e('Goals', 'plotbird-analytics'); ?></h2>
                <?php if (empty($plotbird_goals['has_goals'])) : ?>
                    <p class="plotbird-empty">
                        <?php esc_html_e('No goals yet. A goal counts visitors who reached a page or triggered an event.', 'plotbird-analytics'); ?>
                    </p>
                <?php else : ?>
                    <?php
                    echo PlotBird_Chart::bars( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
                        $plotbird_rows((array) ($plotbird_goals['rows'] ?? []), 'conversions', static function (array $row): string {
                            $name    = (string) ($row['name'] ?? '');
                            $rate    = isset($row['rate']) ? ' · ' . PlotBird_Stats::percent($row['rate']) : '';
                            return esc_html($name) . '<span class="plotbird-muted">' . esc_html($rate) . '</span>';
                        }),
                        '#b32d2e'
                    );
                    ?>
                <?php endif; ?>
            </div>
        </div>

        <?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <p class="description plotbird-footnote">
            <?php
            printf(
                /* translators: %s: time of the last request. */
                esc_html__('Read through a share link, cached for a minute. Last request: %s.', 'plotbird-analytics'),
                esc_html((string) ($plotbird_data['generated_at'] ?? ''))
            );
            ?>
        </p>
    <?php endif; ?>
</div>
