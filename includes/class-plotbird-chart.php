<?php
/**
 * Charts drawn as SVG, without a charting library.
 *
 * The page shows a few series and a sparkline per metric. Pulling in 200 KB of
 * JavaScript for that would be out of proportion for an admin screen, and the
 * plugin has to stay simple enough to pass a plugin review.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

/**
 * Turns series of numbers into SVG elements.
 */
final class PlotBird_Chart {

    /** Width of the drawing area in viewBox units. */
    private const WIDTH = 600;

    /**
     * A line chart with one or more series and a legend.
     *
     * @param list<string> $labels  X axis labels, one per point.
     * @param list<array{label:string,values:list<int|float>,colour:string}> $series Series to draw.
     * @param array<string,mixed> $args Optional height.
     */
    public static function plot(array $labels, array $series, array $args = []): string {
        $labels = array_values($labels);
        $count  = count($labels);
        $height = isset($args['height']) ? max(100, (int) $args['height']) : 200;

        if ($count === 0) {
            return self::empty_state();
        }

        $max = 1.0;
        foreach ($series as $item) {
            foreach ((array) ($item['values'] ?? []) as $value) {
                $max = max($max, (float) $value);
            }
        }

        $pad  = 12;
        $step = $count > 1 ? (self::WIDTH - $pad * 2) / ($count - 1) : 0.0;

        ob_start();
        ?>
<figure class="plotbird-plot">
    <?php if (count($series) > 1) : ?>
        <ul class="plotbird-legend">
            <?php foreach ($series as $item) : ?>
                <li>
                    <i style="background:<?php echo esc_attr((string) ($item['colour'] ?? '#2271b1')); ?>"></i>
                    <?php echo esc_html((string) ($item['label'] ?? '')); ?>
                    <?php if (isset($item['total'])) : ?>
                        <b><?php echo esc_html(PlotBird_Stats::number($item['total'])); ?></b>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <svg viewBox="0 0 <?php echo esc_attr((string) self::WIDTH); ?> <?php echo esc_attr((string) $height); ?>"
         preserveAspectRatio="none" role="img"
         aria-label="<?php echo esc_attr__('Traffic over time', 'plotbird-analytics'); ?>">
        <?php foreach ([0.25, 0.5, 0.75] as $fraction) : ?>
            <line x1="0" x2="<?php echo esc_attr((string) self::WIDTH); ?>"
                  y1="<?php echo esc_attr((string) round($height * $fraction, 1)); ?>"
                  y2="<?php echo esc_attr((string) round($height * $fraction, 1)); ?>"
                  stroke="#f0f0f1" stroke-width="1" vector-effect="non-scaling-stroke"></line>
        <?php endforeach; ?>

        <?php foreach ($series as $item) : ?>
            <?php
            $values = array_values((array) ($item['values'] ?? []));
            if ($values === []) {
                continue;
            }

            $points = [];
            foreach ($values as $index => $value) {
                $x = $count > 1 ? $pad + $step * $index : self::WIDTH / 2;
                $y = $height - $pad - (($height - $pad * 2) * ((float) $value / $max));
                $points[] = [round($x, 1), round($y, 1)];
            }

            $line = implode(' ', array_map(
                static fn (array $p): string => $p[0] . ',' . $p[1],
                $points
            ));

            $area = 'M ' . $points[0][0] . ' ' . ($height - $pad);
            foreach ($points as $point) {
                $area .= ' L ' . $point[0] . ' ' . $point[1];
            }
            $area .= ' L ' . $points[count($points) - 1][0] . ' ' . ($height - $pad) . ' Z';
            $colour = (string) ($item['colour'] ?? '#2271b1');
            ?>
            <path d="<?php echo esc_attr($area); ?>" fill="<?php echo esc_attr($colour); ?>" fill-opacity="0.10"></path>
            <polyline points="<?php echo esc_attr($line); ?>"
                      fill="none" stroke="<?php echo esc_attr($colour); ?>"
                      stroke-width="2" stroke-linejoin="round" stroke-linecap="round"
                      vector-effect="non-scaling-stroke"></polyline>
        <?php endforeach; ?>
    </svg>

    <div class="plotbird-plot-axis">
        <span><?php echo esc_html((string) ($labels[0] ?? '')); ?></span>
        <span><?php echo esc_html((string) ($labels[$count - 1] ?? '')); ?></span>
    </div>
</figure>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * A sparkline for a metric card: no axes, no labels.
     *
     * @param list<int|float> $values Points to draw.
     */
    public static function spark(array $values, string $colour = '#2271b1'): string {
        $values = array_values($values);
        $count  = count($values);

        if ($count < 2) {
            return '';
        }

        $width  = 120;
        $height = 32;
        $pad    = 3;
        $max    = max(1.0, (float) max($values));
        $step   = ($width - $pad * 2) / ($count - 1);

        $points = [];
        foreach ($values as $index => $value) {
            $x = $pad + $step * $index;
            $y = $height - $pad - (($height - $pad * 2) * ((float) $value / $max));
            $points[] = round($x, 1) . ',' . round($y, 1);
        }

        return sprintf(
            '<svg class="plotbird-spark" viewBox="0 0 %d %d" preserveAspectRatio="none" aria-hidden="true">'
            . '<polyline points="%s" fill="none" stroke="%s" stroke-width="1.5" '
            . 'stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"></polyline></svg>',
            $width,
            $height,
            esc_attr(implode(' ', $points)),
            esc_attr($colour)
        );
    }

    /**
     * A row of horizontal bars, the way a list of top pages usually looks.
     *
     * @param list<array{label:string,value:string,count:int|float,share:float}> $rows Prepared rows.
     */
    public static function bars(array $rows, string $colour = '#2271b1'): string {
        if ($rows === []) {
            return self::empty_state();
        }

        ob_start();
        ?>
<ul class="plotbird-bars">
    <?php foreach ($rows as $row) : ?>
        <?php $width = max(0.0, min(1.0, (float) $row['share'])); ?>
        <li>
            <span class="plotbird-bars-label" style="--plotbird-share:<?php echo esc_attr((string) round($width * 100, 1)); ?>%">
                <span class="plotbird-bars-fill" style="background:<?php echo esc_attr($colour); ?>"></span>
                <span class="plotbird-bars-text"><?php echo $row['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the caller. ?></span>
            </span>
            <span class="plotbird-bars-value"><?php echo esc_html((string) $row['value']); ?></span>
        </li>
    <?php endforeach; ?>
</ul>
        <?php
        return (string) ob_get_clean();
    }

    /**
     * The message shown when there is nothing to draw.
     */
    public static function empty_state(): string {
        return '<p class="plotbird-empty">' . esc_html__('Nothing here yet for this period.', 'plotbird-analytics') . '</p>';
    }
}
