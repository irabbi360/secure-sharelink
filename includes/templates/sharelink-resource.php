<?php
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
            <a href="<?php echo esc_url($data['resource_value']); ?>" download>
                <?php esc_html_e('Download File', 'secure-sharelink'); ?>
            </a>
        </p>
    <?php elseif ($data['resource_type'] === 'url'): ?>
        <p>
            <a href="<?php echo esc_url($data['resource_value']); ?>" target="_blank">
                <?php echo esc_html($data['resource_value']); ?>
            </a>
        </p>
    <?php else: ?>
        <p><?php echo esc_html($data['resource_value']); ?></p>
    <?php endif; ?>
</div>
