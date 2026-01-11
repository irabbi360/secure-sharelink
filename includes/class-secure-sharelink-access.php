<?php

class ShareLink_Access
{
    public function __construct()
    {
        add_action('template_redirect', [$this, 'handle_request']);
    }

    public function handle_request()
    {
        // Get the sharelink token from query var
        $token = get_query_var('sharelink', false);
        
        // Fallback: Parse from REQUEST_URI if rewrite rule didn't work
        if (!$token && isset($_SERVER['REQUEST_URI'])) {
            $request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            if (preg_match('#/shareurl/([a-zA-Z0-9_-]+)/?$#', $request_uri, $matches)) {
                $token = $matches[1];
            }
        }
        
        // Debug: Log what we're receiving
        error_log('ShareLink handle_request called. Token: ' . var_export($token, true));
        
        if ($token !== false && !empty($token)) {
            // Sanitize token
            $token = sanitize_text_field($token);
            $manager = new ShareLink_Manager();
            $data = $manager->get_link($token);

            if (!$data) {
                wp_die(esc_html__('Invalid or expired share link.', 'secure-sharelink'));
            }

            // Get WP timezone
            $timezone = wp_timezone();
            $now      = new DateTime('now', $timezone);

            if (!empty($data['expires_at'])) {
                $expires = new DateTime($data['expires_at'], $timezone);
            } else {
                $expires = clone $now; // or use a fallback time
            }

            // Check expiry
            if ($expires < $now) {
                wp_die(esc_html__('This link has expired.', 'secure-sharelink'));
            }

            // Check password
            if (!empty($data['password'])) {
                if (
                    isset($_POST['sharelink_access_nonce']) &&
                    !wp_verify_nonce(
                        sanitize_text_field(wp_unslash($_POST['sharelink_access_nonce'])),
                        'sharelink_access'
                    )
                ) {
                    if (isset($_POST['sharelink_access_nonce'])) {
                        wp_die(esc_html__('Security check failed. Please try again.', 'secure-sharelink'));
                    }
                }

                $password_input = isset($_POST['sharelink_password'])
                    ? sanitize_text_field(wp_unslash($_POST['sharelink_password']))
                    : null;

                if (empty($password_input) || !wp_check_password($password_input, $data['password'])) {
                    ShareLink_Logger::log($data['id'], $token, 'wrong_password');

                    get_header();

                    $template = locate_template('sharelink-password-form.php');
                    if (!$template) {
                        $template = plugin_dir_path(__FILE__) . 'templates/sharelink-password-form.php';
                    }

                    $args = ['password_input' => $password_input];
                    if (file_exists($template)) {
                        $this->load_template_with_args($template, $args);
                    }

                    get_footer();
                    exit;
                }
            }

            // Check IP whitelist
            if (!empty($data['ip_whitelist'])) {
                $ips     = array_map('trim', explode(',', $data['ip_whitelist']));
                $user_ip = isset($_SERVER['REMOTE_ADDR'])
                    ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
                    : '';
                if (!in_array($user_ip, $ips, true)) {
                    wp_die(esc_html__('Access denied from your IP.', 'secure-sharelink'));
                }
            }

            // Check max uses
            if (!empty($data['max_uses']) && $data['used_count'] >= $data['max_uses']) {
                wp_die(esc_html__('This link has reached its usage limit.', 'secure-sharelink'));
            }

            // Increment usage
            $manager->increment_usage($token);

            // Burn after reading
            if (!empty($data['burn_after_reading'])) {
                $manager->delete_link($token);
                ShareLink_Logger::log($data['id'], $token, 'burn_after_reading_deleted');
            }

            // Log successful access
            ShareLink_Logger::log($data['id'], $token, 'success');

            // Handle 301 redirect - do this before any output
            if ($data['resource_type'] === '301_redirect') {
                status_header(301);
                header('Location: ' . esc_url_raw($data['resource_value']));
                exit;
            }

            // Output the resource
            get_header();

            // Locate template (theme override first, plugin fallback)
            $template = locate_template('sharelink-resource.php');
            if (!$template) {
                $template = plugin_dir_path(__FILE__) . 'templates/sharelink-resource.php';
            }

            // Pass variables
            $args = ['data' => $data];
            if (file_exists($template)) {
                $this->load_template_with_args($template, $args);
            }

            get_footer();
            exit;
        }
    }

    private function load_template_with_args($template, $args = []) {
        if (is_array($args)) {
            extract($args); // makes $password_input available inside template
        }
        include $template;
    }
}
