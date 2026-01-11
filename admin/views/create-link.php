<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check and display error messages with nonce verification
if (
    isset($_GET['_wpnonce'], $_GET['errors']) &&
    wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sharelink_errors')
) {
    $raw_errors = sanitize_text_field(wp_unslash($_GET['errors']));
    $errors = json_decode(stripslashes($raw_errors), true);

    if ($errors): ?>
        <div class="notice notice-error is-dismissible">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif;
}
?>

<div class="wrap">
    <h1>Create Secure Link</h1>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('sharelink_create'); ?>
        <input type="hidden" name="action" value="sharelink_create">

        <table class="form-table">
            <tr>
                <th>Resource Type</th>
                <td>
                    <select name="resource_type">
                        <option value="file">File</option>
                        <option value="url">URL</option>
                        <option value="data">Data</option>
                        <option value="301_redirect">301 Redirect</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>Resource Value</th>
                <td>
                    <input type="text" id="resource_value" name="resource_value" class="regular-text" placeholder="/wp-content/uploads/file.pdf">
                    <button type="button" class="button select-file">Select from Media</button>
                </td>
            </tr>
            <tr>
                <th>Custom URL Slug (Optional)</th>
                <td>
                    <input type="text" name="custom_token" class="regular-text" placeholder="leave blank to auto-generate">
                    <p style="font-size: 12px; color: #666;">Alphanumeric characters only. Must be unique.</p>
                </td>
            </tr>

            <tr><th>Password</th><td><input type="text" name="password"></td></tr>
            <tr><th>IP Whitelist</th><td><input type="text" name="ip_whitelist" placeholder="127.0.0.1,192.168.1.1"></td></tr>
            <tr><th>Max Uses</th><td><input type="number" name="max_uses"></td></tr>
            <tr><th>Expires At</th><td><input type="datetime-local" name="expires_at"></td></tr>
            <tr><th>Burn After Reading</th><td><input type="checkbox" name="burn_after_reading" value="1"> Yes</td></tr>
        </table>
        <p><button type="submit" class="button button-primary">Create Link</button></p>
    </form>
</div>
