<?php
class ShareLink_Manager {

    private $table;

    public function __construct() {
        global $wpdb;
        // Sanitize table name once and reuse everywhere
        $this->table = esc_sql($wpdb->prefix . 'secure_sharelinks');
        add_shortcode('sharelink', array($this, 'generate_shortcode'));
    }

    public function generate_token($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    public function create_link($resource_type, $resource_value, $args = []) {
        global $wpdb;

        // Use custom token if provided, otherwise generate one
        $token = isset($args['custom_token']) && !empty($args['custom_token']) ? $args['custom_token'] : $this->generate_token();
        
        $data = [
            'token'             => $token,
            'resource_type'     => $resource_type,
            'resource_value'    => maybe_serialize($resource_value),
            'password'          => isset($args['password']) ? wp_hash_password($args['password']) : null,
            'ip_whitelist'      => (!empty($args['ip_whitelist'])) ? maybe_serialize($args['ip_whitelist']) : null,
            'max_uses'          => $args['max_uses'] ?? 0,
            'expires_at'        => $args['expires_at'] ?? null,
            'burn_after_reading'=> $args['burn_after_reading'] ?? 0
        ];

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->insert($this->table, $data);

        return site_url("shareurl/{$token}");
    }

    public function validate_link($token, $password = null) {
        global $wpdb;

        $cache_key = 'secure_sharelink_' . md5($token);
        $link = wp_cache_get($cache_key, 'secure_sharelinks');

        if (false === $link) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $link = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table} WHERE token = %s",
                    $token
                )
            );

            if ($link) {
                wp_cache_set($cache_key, $link, 'secure_sharelinks', 3600);
            }
        }

        if (!$link) return new WP_Error('invalid', 'Invalid link');
        if ($link->expires_at && strtotime($link->expires_at) < time()) return new WP_Error('expired', 'Link expired');
        if ($link->max_uses > 0 && $link->used_count >= $link->max_uses) return new WP_Error('exceeded', 'Usage limit reached');

        // Password check
        if ($link->password) {
            if (empty($password) || !wp_check_password($password, $link->password)) {
                ShareLink_Logger::log($link->id, $token, 'password_required');
                return new WP_Error('forbidden', 'Password required or invalid');
            }
        }

        // Burn after reading
        if ($link->burn_after_reading) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->delete($this->table, ['id' => $link->id]);
            wp_cache_delete($cache_key, 'secure_sharelinks');
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $wpdb->update(
                $this->table,
                [
                    'used_count'    => $link->used_count + 1,
                    'last_accessed' => current_time('mysql')
                ],
                ['id' => $link->id]
            );
            wp_cache_delete($cache_key, 'secure_sharelinks');
        }

        return [
            'id'    => $link->id,
            'type'  => $link->resource_type,
            'value' => maybe_unserialize($link->resource_value)
        ];
    }

    public function generate_shortcode($atts) {
        $atts = shortcode_atts([
            'type'  => 'file',
            'value' => ''
        ], $atts);

        $url = $this->create_link($atts['type'], $atts['value']);
        return "<a href='" . esc_url($url) . "'>Secure Link</a>";
    }

    /**
     * Get link data by token (with caching)
     */
    public function get_link($token) {
        global $wpdb;

        $cache_key = 'secure_sharelink_' . md5($token);
        $row = wp_cache_get($cache_key, 'secure_sharelinks');

        if (false === $row) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$this->table} WHERE token = %s",
                    $token
                ),
                ARRAY_A
            );

            if ($row) {
                wp_cache_set($cache_key, $row, 'secure_sharelinks', 3600);
            }
        }

        return $row ? $row : false;
    }

    /**
     * Increment usage count and invalidate cache
     */
    public function increment_usage($token) {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->table} SET used_count = used_count + 1 WHERE token = %s",
                $token
            )
        );

        $cache_key = 'secure_sharelink_' . md5($token);
        wp_cache_delete($cache_key, 'secure_sharelinks');
    }
}
