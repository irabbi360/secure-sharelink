<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">ShareLink Manager</h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=sharelink-create' ) ); ?>" class="page-title-action">
        + Create New Link
    </a>
    <hr class="wp-header-end">
    <table class="wp-list-table widefat fixed striped table-view-list posts margin-top-10">
        <thead>
        <tr>
            <th>ID</th>
            <th>Resource</th>
            <th>Link</th>
            <th>Uses</th>
            <th>Expires</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($links as $link): ?>
            <tr>
                <td><?php echo esc_html( $link->id ); ?></td>
                <td><?php echo esc_html($link->resource_type); ?></td>
                <td>
                    <a href="<?php echo esc_url( site_url( 'shareurl?sharelink=' . $link->token ) ); ?>" target="_blank">Open</a>
                </td>
                <td><?php echo esc_html("{$link->used_count}/" . ($link->max_uses ?: '∞')); ?></td>
                <td><?php echo esc_html( $link->expires_at ?: 'Never'); ?></td>
                <td>
                    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
                        <?php wp_nonce_field('sharelink_delete'); ?>
                        <input type="hidden" name="action" value="sharelink_delete">
                        <input type="hidden" name="id" value="<?php echo esc_html( $link->id); ?>">
                        <button class="button-link-delete">Delete</button>
                    </form>
                </td>
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
