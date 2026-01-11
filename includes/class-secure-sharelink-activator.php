<?php
class ShareLink_Activator {
    public static function activate() {
        global $wpdb;
        
        // Only create tables if they don't exist (prevents data loss on updates)
        if ( get_option( 'sharelink_tables_created' ) ) {
            return;
        }
        
        $table_name = $wpdb->prefix . 'secure_sharelinks';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL UNIQUE,
            resource_type VARCHAR(50) NOT NULL,
            resource_value TEXT NOT NULL,
            password VARCHAR(255) NULL,
            ip_whitelist TEXT NULL,
            max_uses INT DEFAULT 0,
            used_count INT DEFAULT 0,
            expires_at DATETIME NULL,
            burn_after_reading TINYINT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_accessed DATETIME NULL
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $sql_logs = "CREATE TABLE {$wpdb->prefix}secure_sharelink_logs (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            link_id BIGINT UNSIGNED NOT NULL,
            token VARCHAR(64) NOT NULL,
            ip_address VARCHAR(100) NOT NULL,
            user_agent TEXT NULL,
            status VARCHAR(20) NOT NULL,
            accessed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        dbDelta($sql_logs);
        
        // Mark tables as created to prevent unnecessary recreation on updates
        update_option( 'sharelink_tables_created', true );
        
        // Flush rewrite rules to activate custom rewrite rules
        flush_rewrite_rules();
    }
}
