<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ShareLink Resource Template
 *
 * Variables available:
 * - $data (array) → contains resource info
 */
?>
<div class="sharelink-content" style="max-width:800px;margin:50px auto;text-align:center;">
    <h2><?php esc_html_e('Shared Resource', 'secure-sharelink'); ?></h2>

    <?php if ($data['resource_type'] === 'file'): ?>
    <p>
        <button
            id="sharelink-download"
            class="sharelink-download-btn"
            data-file-url="<?php echo esc_url($data['resource_value']); ?>"
            style="padding:10px 20px;font-size:16px;cursor:pointer;background:#0073aa;color:#fff;border:none;border-radius:4px;">
            <?php esc_html_e('Download File', 'secure-sharelink'); ?>
        </button>
    </p>
    <?php elseif ($data['resource_type'] === 'url'): ?>
        <p>
            <a href="<?php echo esc_url($data['resource_value']); ?>" target="_blank">
                <?php echo esc_html($data['resource_value']); ?>
            </a>
        </p>
    <?php elseif ($data['resource_type'] === '301_redirect'): ?>
        <script type="text/javascript">
            window.location.replace(<?php echo wp_json_encode($data['resource_value']); ?>);
        </script>
        <p><?php esc_html_e('Redirecting...', 'secure-sharelink'); ?></p>
    <?php else: ?>
        <p><?php echo esc_html($data['resource_value']); ?></p>
    <?php endif; ?>
</div>
