<?php
class ShareLink_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_sharelink_create', [$this, 'handle_create']);
        add_action('admin_post_sharelink_delete', [$this, 'handle_delete']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function menu() {
        add_menu_page('ShareLink', 'Secure ShareLink', 'manage_options', 'sharelink', [$this, 'dashboard'], 'dashicons-lock');
        add_submenu_page('sharelink', 'Create Link', 'Create Link', 'manage_options', 'sharelink-create', [$this, 'create']);
        add_submenu_page('sharelink', 'Logs', 'Logs', 'manage_options', 'sharelink-logs', [$this, 'logs']);
    }

    public function dashboard() {
        global $wpdb;

        // Pagination variables
        $per_page = 20;
        $paged    = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset   = ($paged - 1) * $per_page;

        // Count total sharelinks
        $total_links = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}secure_sharelinks");

        // Fetch paginated sharelinks
        $links = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}secure_sharelinks
             ORDER BY id DESC
             LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        // Generate pagination links
        $total_pages = ceil($total_links / $per_page);
        $pagination  = paginate_links([
            'base'      => remove_query_arg('paged', add_query_arg(null, null)) . '%_%',
            'format'    => '&paged=%#%',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => __('« Previous', 'secure-sharelink'),
            'next_text' => __('Next »', 'secure-sharelink'),
        ]);

        include SHARELINK_DIR . 'admin/views/dashboard.php';
    }


    public function create() {
        include SHARELINK_DIR . 'admin/views/create-link.php';
    }

    public function logs() {
        global $wpdb;

        // Set up pagination variables
        $per_page = 20; // number of logs per page
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;

        // Count total logs
        $total_logs = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}secure_sharelink_logs");

        // Fetch paginated logs
        $logs = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}secure_sharelink_logs 
             ORDER BY accessed_at DESC 
             LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );

        // Generate pagination links
        $total_pages = ceil($total_logs / $per_page);
        $pagination = paginate_links([
            'base'      => add_query_arg('paged', '%#%'),
            'format'    => '',
            'current'   => $paged,
            'total'     => $total_pages,
            'prev_text' => __('« Previous', 'secure-sharelink'),
            'next_text' => __('Next »', 'secure-sharelink'),
        ]);

        include SHARELINK_DIR . 'admin/views/logs.php';
    }

    public function handle_create() {
        check_admin_referer('sharelink_create');

        $errors = [];

        // Resource type validation
        $resource_type = sanitize_text_field($_POST['resource_type'] ?? '');
        $valid_types = ['file', 'route', 'data'];
        if (!in_array($resource_type, $valid_types, true)) {
            $errors[] = "Invalid resource type.";
        }

        // Resource value validation
        $resource_value = trim(sanitize_text_field($_POST['resource_value'] ?? ''));
        if (empty($resource_value)) {
            $errors[] = "Resource value is required.";
        }

        // Password (optional)
        $password = !empty($_POST['password']) ? sanitize_text_field($_POST['password']) : null;

        // IP whitelist validation
        $ip_whitelist_raw = sanitize_text_field($_POST['ip_whitelist'] ?? '');
        $ip_whitelist = [];
        if (!empty($ip_whitelist_raw)) {
            foreach (explode(',', $ip_whitelist_raw) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $ip_whitelist[] = $ip;
                } else {
                    $errors[] = "Invalid IP address: {$ip}";
                }
            }
        }

        // Max uses
        $max_uses = intval($_POST['max_uses'] ?? 0);
        if ($max_uses < 0) {
            $errors[] = "Max uses must be zero or a positive number.";
        }

        // Expiry validation
        $expires_at = null;
        if (!empty($_POST['expires_at'])) {
            $expires_at_raw = sanitize_text_field($_POST['expires_at']);
            $expires_at_time = strtotime($expires_at_raw);

            if ($expires_at_time === false) {
                $errors[] = "Invalid expiry date format.";
            } elseif ($expires_at_time < time()) {
                $errors[] = "Expiry date must be in the future.";
            } else {
                $expires_at = wp_date('Y-m-d H:i:s', $expires_at_time);
            }
        }

        // Burn after reading
        $burn_after_reading = isset($_POST['burn_after_reading']) ? 1 : 0;

        // If errors, redirect back with messages
        if (!empty($errors)) {
            $redirect_url = add_query_arg([
                'page'   => 'sharelink-create',
                'errors' => urlencode(json_encode($errors))
            ], admin_url('admin.php'));

            wp_redirect($redirect_url);
            exit;
        }

        // Create link
        $manager = new ShareLink_Manager();
        $url = $manager->create_link($resource_type, $resource_value, [
            'password'           => $password,
            'ip_whitelist'       => $ip_whitelist,
            'max_uses'           => $max_uses,
            'expires_at'         => $expires_at,
            'burn_after_reading' => $burn_after_reading,
        ]);

        wp_redirect(admin_url('admin.php?page=sharelink&created=1&url=' . urlencode($url)));
        exit;
    }

    public function handle_delete() {
        check_admin_referer('sharelink_delete');
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'secure_sharelinks', ['id' => intval($_POST['id'])]);
        wp_redirect(admin_url('admin.php?page=sharelink&deleted=1'));
        exit;
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'sharelink') === false) return;

        wp_enqueue_media();
        wp_enqueue_script('sharelink-admin', SHARELINK_URL . 'assets/js/admin.js', ['jquery'], SHARELINK_VERSION, true);
        wp_enqueue_style('sharelink-admin', SHARELINK_URL . 'assets/css/admin.css', [], SHARELINK_VERSION);
    }
}
