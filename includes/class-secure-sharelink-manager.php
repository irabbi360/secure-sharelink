<?php
class ShareLink_Manager {

    private $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'secure_sharelinks';
        add_shortcode( 'sharelink', array( $this, 'generate_shortcode' ) );
    }

    public function generate_token($length = 32) {
        return bin2hex(random_bytes($length/2));
    }

    public function create_link($resource_type, $resource_value, $args = []) {
        global $wpdb;
        $table = $wpdb->prefix . 'secure_sharelinks';
        
        $token = $this->generate_token();
        $data = [
            'token'             => $token,
            'resource_type'     => $resource_type,
            'resource_value'    => maybe_serialize($resource_value),
            'password'          => isset($args['password']) ? wp_hash_password($args['password']) : null,
            'ip_whitelist'      => (isset($args['ip_whitelist']) && !empty($args['ip_whitelist'])) ? maybe_serialize($args['ip_whitelist']) : null,
            'max_uses'          => $args['max_uses'] ?? 0,
            'expires_at'        => $args['expires_at'] ?? null,
            'burn_after_reading'=> $args['burn_after_reading'] ?? 0
        ];
        $wpdb->insert($table, $data);

        return site_url("shareurl?sharelink={$token}");
    }

    public function validate_link($token, $password = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'secure_sharelinks';
        $link = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE token=%s", $token));

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
            $wpdb->delete($table, ['id' => $link->id]);
        } else {
            $wpdb->update($table, ['used_count' => $link->used_count + 1, 'last_accessed' => current_time('mysql')], ['id' => $link->id]);
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
        return "<a href='$url'>Secure Link</a>";
    }

    /**
     * Get link data by token
     */
    public function get_link($token) {
        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE token = %s", $token), ARRAY_A);
        return $row ? $row : false;
    }

    /**
     * Increment usage count
     */
    public function increment_usage($token) {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->table} SET used_count = used_count + 1 WHERE token = %s",
                $token
            )
        );
    }
}
