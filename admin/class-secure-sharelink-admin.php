<?php
class ShareLink_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_sharelink_create', [$this, 'handle_create']);
        add_action('admin_post_sharelink_edit', [$this, 'handle_edit']);
        add_action('admin_post_sharelink_delete', [$this, 'handle_delete']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_menu', [$this, 'register_hidden_pages']);
    }

    public function register_hidden_pages() {
        // Register edit page without displaying it in menu
        add_submenu_page(null, 'Edit Link', 'Edit Link', 'manage_options', 'sharelink-edit', [$this, 'edit']);
        // Register stats page without displaying it in menu
        add_submenu_page(null, 'Link Statistics', 'Link Statistics', 'manage_options', 'sharelink-stats', [$this, 'view_stats']);
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

    public function edit() {
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$id) {
            wp_die(esc_html__('Invalid link ID', 'secure-sharelink'));
        }

        $manager = new ShareLink_Manager();
        $link = $manager->get_link_by_id($id);

        if (!$link) {
            wp_die(esc_html__('Link not found', 'secure-sharelink'));
        }

        // Unserialize data
        $link['ip_whitelist'] = !empty($link['ip_whitelist']) ? maybe_unserialize($link['ip_whitelist']) : [];

        // Handle errors display
        $errors = [];
        if (isset($_GET['errors'])) {
            $errors = json_decode(urldecode(sanitize_text_field(wp_unslash($_GET['errors']))), true) ?: [];
        }

        include SHARELINK_DIR . 'admin/views/edit-link.php';
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

    public function view_stats() {
        global $wpdb;

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if (!$id) {
            wp_die(esc_html__('Invalid link ID', 'secure-sharelink'));
        }

        $manager = new ShareLink_Manager();
        $link = $manager->get_link_by_id($id);

        if (!$link) {
            wp_die(esc_html__('Link not found', 'secure-sharelink'));
        }

        // Pagination variables
        $per_page = 20;
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($paged - 1) * $per_page;

        // Count total logs for this link
        $total_logs = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}secure_sharelink_logs WHERE link_id = %d",
            $id
        ));

        // Fetch paginated logs for this link
        $logs = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}secure_sharelink_logs 
             WHERE link_id = %d
             ORDER BY accessed_at DESC 
             LIMIT %d OFFSET %d",
            $id,
            $per_page,
            $offset
        ));

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

        include SHARELINK_DIR . 'admin/views/link-stats.php';
    }

    public function handle_create() {
        check_admin_referer('sharelink_create');

        $errors = [];

        // Resource type validation
        $resource_type = sanitize_text_field(!empty(wp_unslash($_POST['resource_type'])) ? wp_unslash($_POST['resource_type']) : '');
        $valid_types = ['file', 'url', 'data', '301_redirect'];
        if (!in_array($resource_type, $valid_types, true)) {
            $errors[] = "Invalid resource type.";
        }

        // Resource value validation
        $resource_value = trim(sanitize_text_field(!empty(wp_unslash($_POST['resource_value'])) ? wp_unslash($_POST['resource_value']) : ''));
        if (empty($resource_value)) {
            $errors[] = "Resource value is required.";
        }

        // Password (optional)
        $password = !empty(sanitize_text_field(wp_unslash($_POST['password']))) ? sanitize_text_field(wp_unslash($_POST['password'])) : null;

        // IP whitelist validation
        $ip_whitelist_raw = sanitize_text_field(!empty(wp_unslash($_POST['ip_whitelist'])) ? wp_unslash($_POST['ip_whitelist']) : '');
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
            $expires_at_raw = sanitize_text_field(wp_unslash($_POST['expires_at']));
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

        // Custom token validation (optional)
        $custom_token = '';
        if (!empty($_POST['custom_token'])) {
            $custom_token = sanitize_text_field(wp_unslash($_POST['custom_token']));
            
            // Validate custom token: alphanumeric only, min 3 chars
            if (!preg_match('/^[a-zA-Z0-9-_]{3,64}$/', $custom_token)) {
                $errors[] = "Custom token must be 3-64 characters and contain only letters, numbers, hyphens, and underscores.";
            }
            
            // Check if custom token already exists
            global $wpdb;
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}secure_sharelinks WHERE token = %s",
                $custom_token
            ));
            if ($existing) {
                $errors[] = "This URL Slug is already in use. Please choose a different one.";
            }
        }

        // If errors, redirect back with messages and nonce
        if (!empty($errors)) {
            $redirect_url = add_query_arg([
                'page'     => 'sharelink-create',
                'errors'   => urlencode(json_encode($errors)),
                '_wpnonce' => wp_create_nonce('sharelink_errors'),
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
            'custom_token'       => $custom_token,
        ]);

        // Redirect on success
        wp_redirect(admin_url('admin.php?page=sharelink&created=1&url=' . urlencode($url)));
        exit;
    }

    public function handle_delete() {
        check_admin_referer('sharelink_delete');
        global $wpdb;

        $id = !empty(sanitize_text_field(wp_unslash($_POST['id']))) ? intval(sanitize_text_field(wp_unslash($_POST['id']))) : 0;

        if (!$id) {
            wp_die(esc_html__('Invalid link ID', 'secure-sharelink'));
        }

        // Delete the sharelink
        $deleted = $wpdb->delete($wpdb->prefix . 'secure_sharelinks', ['id' => $id]);

        if ($deleted !== false) {
            // Delete related logs
            $wpdb->delete($wpdb->prefix . 'secure_sharelink_logs', ['link_id' => $id]);
        }

        wp_redirect(admin_url('admin.php?page=sharelink&deleted=1'));
        exit;
    }

    public function handle_edit() {
        check_admin_referer('sharelink_edit');

        $id = !empty(sanitize_text_field(wp_unslash($_POST['id']))) ? intval(sanitize_text_field(wp_unslash($_POST['id']))) : 0;

        if (!$id) {
            wp_die(esc_html__('Invalid link ID', 'secure-sharelink'));
        }

        $errors = [];
        $manager = new ShareLink_Manager();
        $link = $manager->get_link_by_id($id);

        if (!$link) {
            wp_die(esc_html__('Link not found', 'secure-sharelink'));
        }

        // Resource value validation
        $resource_value = trim(sanitize_text_field(!empty(wp_unslash($_POST['resource_value'])) ? wp_unslash($_POST['resource_value']) : ''));
        if (empty($resource_value)) {
            $errors[] = "Resource value is required.";
        }

        // Password (optional)
        $password = !empty(sanitize_text_field(wp_unslash($_POST['password']))) ? sanitize_text_field(wp_unslash($_POST['password'])) : null;

        // IP whitelist validation
        $ip_whitelist_raw = sanitize_text_field(!empty(wp_unslash($_POST['ip_whitelist'])) ? wp_unslash($_POST['ip_whitelist']) : '');
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
            $expires_at_raw = sanitize_text_field(wp_unslash($_POST['expires_at']));
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
                'page'     => 'sharelink-edit',
                'id'       => $id,
                'errors'   => urlencode(json_encode($errors)),
                '_wpnonce' => wp_create_nonce('sharelink_errors'),
            ], admin_url('admin.php'));

            wp_redirect($redirect_url);
            exit;
        }

        // Update link
        $manager->update_link($id, [
            'resource_value'     => $resource_value,
            'password'           => $password,
            'ip_whitelist'       => $ip_whitelist,
            'max_uses'           => $max_uses,
            'expires_at'         => $expires_at,
            'burn_after_reading' => $burn_after_reading,
        ]);

        // Redirect on success
        wp_redirect(admin_url('admin.php?page=sharelink&updated=1'));
        exit;
    }

    public function enqueue_assets($hook) {
        if (strpos($hook, 'sharelink') === false) return;

        wp_enqueue_media();
        wp_enqueue_script('sharelink-admin', SHARELINK_URL . 'assets/js/secure-sharelink-admin.js', ['jquery'], SHARELINK_VERSION, true);
        wp_enqueue_style('sharelink-admin', SHARELINK_URL . 'assets/css/secure-sharelink-admin.css', [], SHARELINK_VERSION);
    }
}
