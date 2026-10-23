<?php
/**
 * The settings screen.
 *
 * @var array<string,mixed> $settings Stored settings.
 * @var string              $state    missing_server|missing_website|ready.
 * @var array{key:string,result:array{ok:bool,message:string}}|null $notice Last check.
 *
 * @package PlotBird_Analytics
 */

defined('ABSPATH') || exit;

$plotbird_server  = (string) $settings['server_url'];
$plotbird_website = (string) $settings['website_id'];
$plotbird_ready   = $state === 'ready';

$plotbird_steps = [
    [
        'done'  => $state !== 'missing_server',
        'title' => __('Point the plugin at a PlotBird installation', 'plotbird-analytics'),
    ],
    [
        'done'  => $plotbird_ready,
        'title' => __('Connect your website', 'plotbird-analytics'),
    ],
    [
        'done'  => PlotBird_Stats::has_token(),
        'title' => __('Add a share link to see statistics inside WordPress', 'plotbird-analytics'),
    ],
    [
        'done'  => $plotbird_ready,
        'title' => __('Check that visits arrive', 'plotbird-analytics'),
    ],
];
?>
<div class="wrap plotbird-wrap">
    <h1><?php esc_html_e('PlotBird Analytics', 'plotbird-analytics'); ?></h1>

    <p class="plotbird-lead">
        <?php esc_html_e('PlotBird is self-hosted analytics: it needs an installation you control, on this hosting account or on another server. This plugin only adds the tracking code and never sends your data anywhere else.', 'plotbird-analytics'); ?>
    </p>

    <?php if ($notice !== null) : ?>
        <div class="notice <?php echo $notice['result']['ok'] ? 'notice-success' : 'notice-error'; ?> is-dismissible">
            <p><?php echo esc_html($notice['result']['message']); ?></p>
        </div>
    <?php endif; ?>

    <ol class="plotbird-steps">
        <?php foreach ($plotbird_steps as $plotbird_index => $plotbird_step) : ?>
            <li class="<?php echo $plotbird_step['done'] ? 'is-done' : 'is-pending'; ?>">
                <span class="plotbird-step-mark" aria-hidden="true"><?php echo esc_html($plotbird_step['done'] ? '✓' : (string) ($plotbird_index + 1)); ?></span>
                <span class="screen-reader-text">
                    <?php
                    echo $plotbird_step['done']
                        ? esc_html__('Done:', 'plotbird-analytics')
                        : esc_html__('To do:', 'plotbird-analytics');
                    ?>
                </span>
                <?php echo esc_html($plotbird_step['title']); ?>
            </li>
        <?php endforeach; ?>
    </ol>

    <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>" class="plotbird-card">
        <?php settings_fields('plotbird_analytics'); ?>

        <h2><?php esc_html_e('Step 1. Where PlotBird lives', 'plotbird-analytics'); ?></h2>

        <details class="plotbird-help" <?php echo $state === 'missing_server' ? 'open' : ''; ?>>
            <summary><?php esc_html_e('I do not have PlotBird installed yet', 'plotbird-analytics'); ?></summary>
            <ol>
                <li>
                    <?php
                    printf(
                        /* translators: %s: download link. */
                        esc_html__('Download the archive from %s.', 'plotbird-analytics'),
                        '<a href="https://github.com/plotbird/analytics/releases/latest" target="_blank" rel="noopener noreferrer">'
                        . esc_html__('the latest release', 'plotbird-analytics') . '</a>'
                    );
                    ?>
                </li>
                <li><?php esc_html_e('Unpack it on your hosting into a folder of its own, for example /plotbird/. Any folder works as long as the address is reachable from a browser.', 'plotbird-analytics'); ?></li>
                <li><?php esc_html_e('Open that address in a browser and finish the seven-step installer. It creates its own tables and asks for a database, an administrator account and your first website.', 'plotbird-analytics'); ?></li>
                <li><?php esc_html_e('Come back here and paste the address below.', 'plotbird-analytics'); ?></li>
            </ol>
            <p class="description">
                <?php esc_html_e('Keep PlotBird in a folder of its own rather than in the WordPress directory: the two applications then cannot interfere with each other.', 'plotbird-analytics'); ?>
            </p>
        </details>

        <p>
            <label for="plotbird-server-url"><?php esc_html_e('PlotBird address', 'plotbird-analytics'); ?></label><br>
            <input type="url" class="regular-text code" id="plotbird-server-url"
                   name="<?php echo esc_attr(PlotBird_Settings::OPTION); ?>[server_url]"
                   value="<?php echo esc_attr($plotbird_server); ?>"
                   placeholder="https://example.com/plotbird" autocomplete="off">
        </p>

        <h2><?php esc_html_e('Step 2. Website id', 'plotbird-analytics'); ?></h2>
        <p class="description">
            <?php esc_html_e('In PlotBird open Websites, then the code panel of the site you are connecting. The id is the long value in the snippet.', 'plotbird-analytics'); ?>
        </p>
        <p>
            <label for="plotbird-website-id"><?php esc_html_e('Website id', 'plotbird-analytics'); ?></label><br>
            <input type="text" class="regular-text code" id="plotbird-website-id"
                   name="<?php echo esc_attr(PlotBird_Settings::OPTION); ?>[website_id]"
                   value="<?php echo esc_attr($plotbird_website); ?>"
                   placeholder="00000000-0000-0000-0000-000000000000" autocomplete="off">
        </p>

        <p>
            <label>
                <input type="checkbox" name="<?php echo esc_attr(PlotBird_Settings::OPTION); ?>[skip_admins]" value="1"
                    <?php checked(1, (int) $settings['skip_admins']); ?>>
                <?php esc_html_e('Do not track logged-in administrators', 'plotbird-analytics'); ?>
            </label><br>
            <span class="description">
                <?php esc_html_e('Recommended: your own visits would otherwise land in the statistics.', 'plotbird-analytics'); ?>
            </span>
        </p>

        <h2><?php esc_html_e('Step 3. Statistics inside WordPress', 'plotbird-analytics'); ?></h2>
        <p class="description">
            <?php esc_html_e('The tracking code only sends visits. To read the numbers here, the plugin needs a share link: it is read-only, it can be revoked at any time, and it never gives access to change anything in PlotBird.', 'plotbird-analytics'); ?>
        </p>
        <details class="plotbird-help">
            <summary><?php esc_html_e('Where do I get one?', 'plotbird-analytics'); ?></summary>
            <ol>
                <li><?php esc_html_e('Open PlotBird and go to Websites.', 'plotbird-analytics'); ?></li>
                <li><?php esc_html_e('Open the code panel of your website and find Links and goals.', 'plotbird-analytics'); ?></li>
                <li><?php esc_html_e('Create a link. Leave the date empty for one that never expires.', 'plotbird-analytics'); ?></li>
                <li><?php esc_html_e('Copy the address and paste it below.', 'plotbird-analytics'); ?></li>
            </ol>
        </details>
        <p>
            <label for="plotbird-share-link"><?php esc_html_e('Share link', 'plotbird-analytics'); ?></label><br>
            <input type="url" class="large-text code" id="plotbird-share-link"
                   name="<?php echo esc_attr(PlotBird_Settings::OPTION); ?>[share_link]"
                   value="<?php echo esc_attr((string) $settings['share_link']); ?>"
                   placeholder="https://example.com/plotbird/share/0000000000000000" autocomplete="off">
        </p>
        <p class="description">
            <?php esc_html_e('This link is the key to your numbers. If you revoke it in PlotBird, this page stops showing statistics until you add a new one.', 'plotbird-analytics'); ?>
        </p>


        <?php submit_button($plotbird_ready ? __('Save changes', 'plotbird-analytics') : __('Save and continue', 'plotbird-analytics')); ?>
    </form>

    <div class="plotbird-card">
        <h2><?php esc_html_e('Step 4. Check it works', 'plotbird-analytics'); ?></h2>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="plotbird-inline-form">
            <?php wp_nonce_field('plotbird_check_server'); ?>
            <input type="hidden" name="action" value="plotbird_check_server">
            <input type="hidden" name="server_url" value="<?php echo esc_attr($plotbird_server); ?>">
            <button type="submit" class="button" <?php disabled($plotbird_server === ''); ?>>
                <?php esc_html_e('Check the address', 'plotbird-analytics'); ?>
            </button>
            <?php if ($plotbird_server !== '') : ?>
                <code><?php echo esc_html($plotbird_server . '/assets/tracker.js'); ?></code>
            <?php endif; ?>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="plotbird-inline-form">
            <?php wp_nonce_field('plotbird_check_page'); ?>
            <input type="hidden" name="action" value="plotbird_check_page">
            <button type="submit" class="button" <?php disabled(!$plotbird_ready); ?>>
                <?php esc_html_e('Check the code on my front page', 'plotbird-analytics'); ?>
            </button>
            <span class="description">
                <?php esc_html_e('Loads your home page and looks for the tracking code in it.', 'plotbird-analytics'); ?>
            </span>
        </form>

        <?php if ($plotbird_ready) : ?>
            <p class="plotbird-done">
                <?php
                printf(
                    /* translators: %s: website id. */
                    esc_html__('The code is added to every page for website %s. Visits appear in your PlotBird dashboard, usually within a minute.', 'plotbird-analytics'),
                    '<code>' . esc_html($plotbird_website) . '</code>'
                );
                ?>
            </p>
        <?php endif; ?>
    </div>
</div>
