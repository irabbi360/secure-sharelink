<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Display success message with generated URL
if (isset($_GET['created']) && $_GET['created'] === '1' && isset($_GET['url'])) {
    $generated_url = sanitize_text_field(wp_unslash($_GET['url']));
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <strong><?php esc_html_e('Secure Link Created!', 'secure-sharelink'); ?></strong><br>
            <?php esc_html_e('Your shareable URL:', 'secure-sharelink'); ?><br>
            <code style="display: block; margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                <a href="<?php echo esc_url($generated_url); ?>" target="_blank" style="word-break: break-all;">
                    <?php echo esc_html($generated_url); ?>
                </a>
            </code>
            <button type="button" class="button button-small" onclick="navigator.clipboard.writeText('<?php echo esc_attr($generated_url); ?>'); alert('Copied to clipboard!');">
                <?php esc_html_e('Copy URL', 'secure-sharelink'); ?>
            </button>
        </p>
    </div>
    <?php
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
                    <a href="<?php echo esc_url( site_url( 'shareurl/' . $link->token ) ); ?>" target="_blank">Open</a>
                </td>
                <td><?php echo esc_html("{$link->used_count}/" . ($link->max_uses ?: '∞')); ?></td>
                <td><?php echo esc_html( $link->expires_at ?: 'Never'); ?></td>
                <td>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=sharelink-stats&id=' . $link->id)); ?>" class="button button-small">Stats</a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=sharelink-edit&id=' . $link->id)); ?>" class="button button-small">Edit</a>
                    <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:inline;">
                        <?php wp_nonce_field('sharelink_delete'); ?>
                        <input type="hidden" name="action" value="sharelink_delete">
                        <input type="hidden" name="id" value="<?php echo esc_html( $link->id); ?>">
                        <button class="button button-small button-link-delete">Delete</button>
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
