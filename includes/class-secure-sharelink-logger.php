<?php
class ShareLink_Logger {
    public static function log($link_id, $token, $status) {
        global $wpdb;

        // Validate and sanitize IP
        $ip_address = '';
        if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
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
