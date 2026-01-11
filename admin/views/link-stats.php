<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Link Statistics', 'secure-sharelink'); ?></h1>

    <div style="margin-bottom: 30px;">
        <a href="<?php echo esc_url(admin_url('admin.php?page=sharelink')); ?>" class="button"><?php esc_html_e('Back to Dashboard', 'secure-sharelink'); ?></a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=sharelink-edit&id=' . $link['id'])); ?>" class="button"><?php esc_html_e('Edit Link', 'secure-sharelink'); ?></a>
    </div>

    <!-- Link Details Box -->
    <div style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 20px; margin-bottom: 30px;">
        <h2 style="margin-top: 0;"><?php esc_html_e('Link Details', 'secure-sharelink'); ?></h2>
        
        <table class="widefat">
            <tbody>
                <tr>
                    <td style="width: 200px; font-weight: bold;"><?php esc_html_e('Link ID', 'secure-sharelink'); ?></td>
                    <td><?php echo esc_html($link['id']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Token', 'secure-sharelink'); ?></td>
                    <td><code><?php echo esc_html($link['token']); ?></code></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Share URL', 'secure-sharelink'); ?></td>
                    <td>
                        <a href="<?php echo esc_url(site_url('shareurl/' . $link['token'])); ?>" target="_blank">
                            <?php echo esc_html(site_url('shareurl/' . $link['token'])); ?>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Resource Type', 'secure-sharelink'); ?></td>
                    <td><?php echo esc_html($link['resource_type']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Resource Value', 'secure-sharelink'); ?></td>
                    <td><?php echo esc_html(maybe_unserialize($link['resource_value'])); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Created Date', 'secure-sharelink'); ?></td>
                    <td><?php echo esc_html($link['created_at']); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Password Protected', 'secure-sharelink'); ?></td>
                    <td><?php echo $link['password'] ? esc_html__('Yes', 'secure-sharelink') : esc_html__('No', 'secure-sharelink'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('IP Whitelist', 'secure-sharelink'); ?></td>
                    <td>
                        <?php 
                        $ips = maybe_unserialize($link['ip_whitelist']);
                        echo !empty($ips) ? esc_html(implode(', ', (array)$ips)) : esc_html__('None', 'secure-sharelink');
                        ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Max Uses', 'secure-sharelink'); ?></td>
                    <td><?php echo $link['max_uses'] > 0 ? esc_html($link['max_uses']) : esc_html__('Unlimited', 'secure-sharelink'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Expiration Date', 'secure-sharelink'); ?></td>
                    <td><?php echo $link['expires_at'] ? esc_html($link['expires_at']) : esc_html__('Never', 'secure-sharelink'); ?></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Burn After Reading', 'secure-sharelink'); ?></td>
                    <td><?php echo $link['burn_after_reading'] ? esc_html__('Yes', 'secure-sharelink') : esc_html__('No', 'secure-sharelink'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Usage Statistics -->
    <div style="background: #fff; border: 1px solid #ddd; border-radius: 4px; padding: 20px; margin-bottom: 30px;">
        <h2 style="margin-top: 0;"><?php esc_html_e('Usage Statistics', 'secure-sharelink'); ?></h2>
        
        <table class="widefat">
            <tbody>
                <tr>
                    <td style="width: 200px; font-weight: bold;"><?php esc_html_e('Total Uses', 'secure-sharelink'); ?></td>
                    <td style="font-size: 24px; font-weight: bold; color: #0073aa;">
                        <?php echo esc_html($link['used_count']); ?>
                        <?php if ($link['max_uses'] > 0): ?>
                            / <?php echo esc_html($link['max_uses']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;"><?php esc_html_e('Last Accessed', 'secure-sharelink'); ?></td>
                    <td><?php echo $link['last_accessed'] ? esc_html($link['last_accessed']) : esc_html__('Never accessed', 'secure-sharelink'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Access Logs -->
    <h2><?php esc_html_e('Access Logs', 'secure-sharelink'); ?></h2>
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
        <tr>
            <th><?php esc_html_e('Date/Time', 'secure-sharelink'); ?></th>
            <th><?php esc_html_e('IP Address', 'secure-sharelink'); ?></th>
            <th><?php esc_html_e('User Agent', 'secure-sharelink'); ?></th>
            <th><?php esc_html_e('Status', 'secure-sharelink'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->accessed_at); ?></td>
                    <td><code><?php echo esc_html($log->ip_address); ?></code></td>
                    <td><?php echo esc_html(substr($log->user_agent, 0, 50)); ?></td>
                    <td>
                        <?php 
                        $status_color = 'success' === $log->status ? '#008000' : '#ff0000';
                        echo '<span style="color: ' . esc_attr($status_color) . '; font-weight: bold;">';
                        echo esc_html(ucwords(str_replace('_', ' ', $log->status)));
                        echo '</span>';
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">
                    <?php esc_html_e('No access logs yet.', 'secure-sharelink'); ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($pagination)): ?>
        <div class="tablenav-pages">
            <?php echo wp_kses_post($pagination); ?>
        </div>
    <?php endif; ?>
</div>
