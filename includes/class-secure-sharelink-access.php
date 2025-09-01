<?php
class ShareLink_Access {
    public function __construct() {
        add_action('template_redirect', [$this, 'handle_request']);
    }

    public function handle_request() {
        if (empty($_GET['sharelink'])) return;

        $token = sanitize_text_field($_GET['sharelink']);
        $password = isset($_POST['sharelink_password']) ? sanitize_text_field($_POST['sharelink_password']) : null;

        $manager = new ShareLink_Manager();
        $result = $manager->validate_link($token, $password);

        // If validation failed
        if (is_wp_error($result)) {
            $this->render_message($result->get_error_message(), 'error', $token);
            exit;
        }

        // At this point, access is granted
        $resource = $result['value'];
        $type     = $result['type'];

        // Log success
        ShareLink_Logger::log($result['id'], $token, 'success');

        if ($type === 'file') {
            $this->serve_file($resource);
        } elseif ($type === 'route') {
            wp_redirect(home_url($resource));
            exit;
        } elseif ($type === 'data') {
            $this->render_message(esc_html($resource), 'success');
        }

        exit;
    }

    private function render_message($message, $status = 'info', $token = null) {
        get_header();
        echo '<div class="sharelink-message" style="max-width:600px;margin:80px auto;padding:20px;border:1px solid #ccc;background:#fff;">';

        if ($status === 'error') {
            echo "<h2 style='color:red;'>Access Denied</h2><p>" . esc_html($message) . "</p>";
        } elseif ($status === 'success') {
            echo "<h2>Resource</h2><p>" . $message . "</p>";
        } else {
            echo "<p>" . esc_html($message) . "</p>";
        }

        // Password form if needed
        if ($message === 'Password required or invalid' && $token) {
            echo '<form method="post" style="margin-top:20px;">
                <label>Enter Password:</label><br>
                <input type="password" name="sharelink_password">
                <button type="submit">Access</button>
            </form>';
        }

        echo '</div>';
        get_footer();
    }

    private function serve_file($file_url) {
        $file_path = str_replace(site_url('/'), ABSPATH, $file_url);

        if (!file_exists($file_path)) {
            $this->render_message("File not found.", 'error');
            exit;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . mime_content_type($file_path));
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));

        readfile($file_path);
        exit;
    }
}
