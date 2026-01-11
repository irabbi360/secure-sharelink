<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Edit Secure Link', 'secure-sharelink'); ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="notice notice-error is-dismissible">
            <p><strong><?php esc_html_e('Errors:', 'secure-sharelink'); ?></strong></p>
            <ul style="margin: 10px 0 0 20px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
        <?php wp_nonce_field('sharelink_edit'); ?>
        <input type="hidden" name="action" value="sharelink_edit">
        <input type="hidden" name="id" value="<?php echo esc_attr($link['id']); ?>">

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="resource_type"><?php esc_html_e('Resource Type', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <span><?php echo esc_html($link['resource_type']); ?></span>
                    <p class="description"><?php esc_html_e('Resource type cannot be changed after creation.', 'secure-sharelink'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="resource_value"><?php esc_html_e('Resource Value', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <input type="text" id="resource_value" name="resource_value" class="regular-text" value="<?php echo esc_attr(maybe_unserialize($link['resource_value'])); ?>">
                    <button type="button" class="button select-file"><?php esc_html_e('Select from Media', 'secure-sharelink'); ?></button>
                    <p class="description"><?php esc_html_e('Update the resource value (file path or URL).', 'secure-sharelink'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="password"><?php esc_html_e('Password Protection (Optional)', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <input type="password" id="password" name="password" placeholder="<?php esc_attr_e('Leave empty to remove password', 'secure-sharelink'); ?>" style="width: 100%; max-width: 300px;">
                    <p class="description"><?php esc_html_e('Set a new password for this link. Leave empty to remove password protection.', 'secure-sharelink'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="ip_whitelist"><?php esc_html_e('IP Whitelist (Optional)', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <textarea id="ip_whitelist" name="ip_whitelist" placeholder="<?php esc_attr_e('192.168.1.1, 10.0.0.1, etc.', 'secure-sharelink'); ?>" style="width: 100%; height: 80px;"><?php echo esc_textarea(implode(', ', (array) $link['ip_whitelist'])); ?></textarea>
                    <p class="description"><?php esc_html_e('Comma-separated list of IP addresses. Leave empty to allow all IPs.', 'secure-sharelink'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="max_uses"><?php esc_html_e('Maximum Uses', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <input type="number" id="max_uses" name="max_uses" value="<?php echo esc_attr($link['max_uses']); ?>" min="0" style="width: 100%; max-width: 300px;">
                    <p class="description"><?php esc_html_e('Set to 0 for unlimited uses.', 'secure-sharelink'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="expires_at"><?php esc_html_e('Expiration Date (Optional)', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" id="expires_at" name="expires_at" value="<?php echo esc_attr($link['expires_at'] ? wp_date('Y-m-d\TH:i', strtotime($link['expires_at'])) : ''); ?>" style="width: 100%; max-width: 300px;">
                    <p class="description"><?php esc_html_e('Leave empty to never expire.', 'secure-sharelink'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="burn_after_reading"><?php esc_html_e('Burn After Reading', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="burn_after_reading" name="burn_after_reading" <?php checked($link['burn_after_reading']); ?>>
                    <label for="burn_after_reading"><?php esc_html_e('Delete this link after first access', 'secure-sharelink'); ?></label>
                    <p class="description"><?php esc_html_e('If enabled, this link will be automatically deleted after the first person accesses it.', 'secure-sharelink'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label><?php esc_html_e('Usage Statistics', 'secure-sharelink'); ?></label>
                </th>
                <td>
                    <p><strong><?php esc_html_e('Used Count:', 'secure-sharelink'); ?></strong> <?php echo esc_html($link['used_count']); ?></p>
                    <p><strong><?php esc_html_e('Created:', 'secure-sharelink'); ?></strong> <?php echo esc_html($link['created_at']); ?></p>
                    <p><strong><?php esc_html_e('Last Accessed:', 'secure-sharelink'); ?></strong> <?php echo esc_html($link['last_accessed'] ?: __('Never', 'secure-sharelink')); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Update Link', 'secure-sharelink'), 'primary', 'submit', true); ?>
    </form>

    <p>
        <a href="<?php echo esc_url(admin_url('admin.php?page=sharelink')); ?>" class="button"><?php esc_html_e('Back to Dashboard', 'secure-sharelink'); ?></a>
    </p>
</div>
