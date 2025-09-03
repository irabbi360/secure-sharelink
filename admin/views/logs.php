<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Access Logs</h1>
    <hr class="wp-header-end">
    <table class="wp-list-table widefat fixed striped table-view-list posts margin-top-10">
        <thead>
        <tr>
            <th>ID</th>
            <th>Link ID</th>
            <th>IP</th>
            <th>Status</th>
            <th>User Agent</th>
            <th>Accessed</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($logs as $log): ?>
            <tr>
                <td><?php echo esc_html( $log->id); ?></td>
                <td><?php echo esc_html( $log->link_id); ?></td>
                <td><?php echo esc_html( $log->ip_address); ?></td>
                <td><?php echo esc_html( $log->status); ?></td>
                <td><?php echo esc_html( $log->user_agent); ?></td>
                <td><?php echo esc_html( $log->accessed_at); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (!empty($pagination)) : ?>
        <div class="tablenav-pages">
            <?php echo wp_kses_post($pagination); ?>
        </div>
    <?php endif; ?>
</div>
