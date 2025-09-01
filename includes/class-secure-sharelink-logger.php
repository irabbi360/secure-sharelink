<?php
class ShareLink_Logger {
    public static function log($link_id, $token, $status) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'secure_sharelink_logs', [
            'link_id' => $link_id,
            'token' => $token,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'status' => $status,
            'accessed_at' => current_time('mysql')
        ]);
    }
}
