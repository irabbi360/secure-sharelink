<?php
/**
 * Plugin Name:       Secure Sharelink
 * Plugin URI:        https://github.com/irabbi360/secure-sharelink/
 * Description:       Generate secure, time-limited sharing links with auditing, password protection, and advanced security.
 * Version:           1.0.0
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
require_once SHARELINK_DIR . 'includes/test.php';

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

// Add custom rewrite endpoint /shareurl
add_action('init', function () {
    add_rewrite_rule('^shareurl?', 'index.php?shareurl=1', 'top');
    add_rewrite_tag('%sharelink%', '([^&]+)');
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'shareurl';
    $vars[] = 'sharelink';
    return $vars;
});

// Handle template rendering
