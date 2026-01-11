<?php
/**
 * Plugin Name:       Secure ShareLink
 * Plugin URI:        https://github.com/irabbi360/secure-sharelink/
 * Description:       Generate secure, time-limited sharing links with auditing, password protection, and advanced security.
 * Version:           1.1.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Fazle Rabbi
 * Author URI:        https://github.com/irabbi360/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       secure-sharelink
 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'SHARELINK_VERSION', '1.0.0' );
define( 'SHARELINK_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHARELINK_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once SHARELINK_DIR . 'includes/class-secure-sharelink-activator.php';
require_once SHARELINK_DIR . 'includes/class-secure-sharelink-deactivator.php';
require_once SHARELINK_DIR . 'includes/class-secure-sharelink-manager.php';
require_once SHARELINK_DIR . 'includes/class-secure-sharelink-rest.php';
require_once SHARELINK_DIR . 'admin/class-secure-sharelink-admin.php';
require_once SHARELINK_DIR . 'includes/class-secure-sharelink-logger.php';
require_once SHARELINK_DIR . 'includes/class-secure-sharelink-access.php';

// Activation / Deactivation
register_activation_hook( __FILE__, array( 'ShareLink_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ShareLink_Deactivator', 'deactivate' ) );

// Init
function sharelink_init() {
    new ShareLink_Admin();
    new ShareLink_Manager();
    new ShareLink_REST();
    new ShareLink_Logger();
    new ShareLink_Access();
}
add_action( 'plugins_loaded', 'sharelink_init' );

// Enqueue frontend scripts
function sharelink_enqueue_frontend_scripts() {
    wp_enqueue_script(
        'secure-sharelink',
        SHARELINK_URL . 'assets/js/secure-sharelink.js',
        array(),
        SHARELINK_VERSION,
        true
    );

    wp_localize_script(
        'secure-sharelink',
        'shareLinkDownloadL10n',
        array(
            'message' => esc_js(__('Your download will start shortly.', 'secure-sharelink'))
        )
    );
}
add_action( 'wp_enqueue_scripts', 'sharelink_enqueue_frontend_scripts' );

// Register query vars first
add_filter('query_vars', function ($vars) {
    $vars[] = 'sharelink';
    return $vars;
});

// Add custom rewrite rule
add_action('init', function () {
    add_rewrite_rule('^shareurl/([a-zA-Z0-9_-]+)/?$', 'index.php?sharelink=$1', 'top');
}, 10, 0);