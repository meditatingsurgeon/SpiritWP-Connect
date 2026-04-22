<?php
/**
 * Plugin Name:       SpiritWP Connect
 * Description:       Provides deep, bidirectional integration between WordPress/WooCommerce and Clientexec billing/hosting management installation.
 * Version:           1.0.0
 * Author:            SpiritWP
 * Text Domain:       spiritwp-connect
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'SPWP_CONNECT_VERSION', '1.0.0' );
define( 'SPWP_CONNECT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPWP_CONNECT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( file_exists( SPWP_CONNECT_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once SPWP_CONNECT_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Fallback autoloader for users who do not run 'composer install'
    spl_autoload_register( function( $class ) {
        $prefix = 'SpiritWP\\Connect\\';
        $base_dir = SPWP_CONNECT_PLUGIN_DIR . 'includes/';
        $len = strlen( $prefix );
        if ( strncmp( $prefix, $class, $len ) !== 0 ) {
            return;
        }
        $relative_class = substr( $class, $len );
        $file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';
        if ( file_exists( $file ) ) {
            require $file;
        }
    });
}

function spwp_connect_activate() {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();

    // Plan Map Table
    $plan_map_table = $wpdb->prefix . 'spwp_plan_map';
    $sql_plan_map = "CREATE TABLE $plan_map_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        ce_product_id bigint(20) NOT NULL,
        ce_product_name varchar(255) NOT NULL DEFAULT '',
        wc_product_id bigint(20) NOT NULL DEFAULT 0,
        wc_product_name varchar(255) NOT NULL DEFAULT '',
        sync_direction varchar(20) NOT NULL DEFAULT 'ce_to_wp',
        status varchar(20) NOT NULL DEFAULT 'active',
        last_synced datetime DEFAULT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY ce_product_id (ce_product_id),
        KEY wc_product_id (wc_product_id)
    ) $charset_collate;";

    // API Log Table
    $api_log_table = $wpdb->prefix . 'spwp_api_log';
    $sql_api_log = "CREATE TABLE $api_log_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        method varchar(10) NOT NULL,
        endpoint varchar(255) NOT NULL,
        status_code int(5),
        response_time float,
        error_message text,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY created_at (created_at)
    ) $charset_collate;";

    dbDelta( $sql_plan_map );
    dbDelta( $sql_api_log );
    
    // Flush rewrite rules on activation for SSO endpoints
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'spwp_connect_activate' );

function spwp_connect_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'spwp_connect_deactivate' );

function spwp_connect_init() {
    if ( class_exists( '\SpiritWP\Connect\Core\Plugin' ) ) {
        \SpiritWP\Connect\Core\Plugin::get_instance()->init();
    }
}
add_action( 'plugins_loaded', 'spwp_connect_init' );
