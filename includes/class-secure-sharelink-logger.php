<?php
class ShareLink_Logger {
    public static function log($link_id, $token, $status) {
        global $wpdb;

        // Validate and sanitize IP
        $ip_address = '';
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        // Check for duplicate log within the last 5 minutes from the same IP
        $recent_log = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}secure_sharelink_logs 
             WHERE link_id = %d AND token = %s AND ip_address = %s 
             AND status = %s
             AND accessed_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
             ORDER BY accessed_at DESC LIMIT 1",
            $link_id,
            $token,
            $ip_address,
            $status
        ));

        // Skip logging if duplicate found
        if ($recent_log) {
            return;
        }

        // Validate and sanitize user agent
        $user_agent = '';
        if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
            $user_agent = sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
        }

        $wpdb->insert(
            $wpdb->prefix . 'secure_sharelink_logs',
            [
                'link_id'     => absint( $link_id ),
                'token'       => sanitize_text_field( $token ),
                'ip_address'  => $ip_address,
                'user_agent'  => $user_agent,
                'status'      => sanitize_text_field( $status ),
                'accessed_at' => current_time( 'mysql' ),
            ]
        );
    }
}
