<?php
/**
 * ShareLink Password Form Template
 *
 * Override by placing in your theme: sharelink-password-form.php
 *
 * Variables available:
 * - $password_input (string|null) → previously entered password
 */
?>

<div class="sharelink-password-form" style="max-width:400px;margin:100px auto;text-align:center;">
    <h2><?php esc_html_e('Protected Link', 'secure-sharelink'); ?></h2>

    <?php if (!empty($password_input)): ?>
        <p style="color:red;"><?php esc_html_e('Incorrect password, try again.', 'secure-sharelink'); ?></p>
    <?php endif; ?>

    <form method="post">
        <div style="display: flex; align-items: center; justify-content: center;">
            <input type="password" name="sharelink_password" class="h-auto" placeholder="<?php esc_attr_e('Enter password', 'secure-sharelink'); ?>" required />
            <button type="submit" style="margin-left: 10px;"><?php esc_html_e('Access', 'secure-sharelink'); ?></button>
        </div>
    </form>
</div>
