<?php
/**
 * Plugin Name: SpiritWP Connect
 * Plugin URI: https://spiritwp.com
 * Description: Clientexec Bridge Plugin
 * Version: 1.1.0
 * Author: spiritualagency
 * Author URI: https://spiritwp.com
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SPWP_CONNECT_VERSION', '1.1.0' );
define( 'SPWP_CONNECT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPWP_CONNECT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// BUG-014 Fix: Fallback PSR-4 autoloader for composerless installs
if ( file_exists( SPWP_CONNECT_PLUGIN_DIR . 'vendor/autoload.php' ) ) {
    require_once SPWP_CONNECT_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    spl_autoload_register( function( $class ) {
        $prefix   = 'SpiritWP\\Connect\\';
        $base_dir = SPWP_CONNECT_PLUGIN_DIR . 'includes/';
        $len      = strlen( $prefix );
        
        if ( strncmp( $prefix, $class, $len ) !== 0 ) {
            return;
        }
        
        $relative = substr( $class, $len );
        $file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';
        
        if ( file_exists( $file ) ) {
            require $file;
        }
    } );
}

function spwp_connect_activate() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();

    $table_plan_map = $wpdb->prefix . 'spwp_plan_map';
    $sql_plan_map = "CREATE TABLE $table_plan_map (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        ce_product_id bigint(20) NOT NULL,
        ce_product_name varchar(255) NOT NULL DEFAULT '',
        wc_product_id bigint(20) NOT NULL DEFAULT 0,
        wc_product_name varchar(255) NOT NULL DEFAULT '',
        sync_direction varchar(20) NOT NULL DEFAULT 'ce_to_wp',
        status varchar(20) NOT NULL DEFAULT 'active',
        last_synced datetime DEFAULT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY (id),
        KEY ce_product_id (ce_product_id)
    ) $charset_collate;";

    $table_api_log = $wpdb->prefix . 'spwp_api_log';
    $sql_api_log = "CREATE TABLE $table_api_log (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        method varchar(10) NOT NULL,
        endpoint varchar(255) NOT NULL,
        status_code int(5),
        response_time float,
        error_message text,
        created_at datetime NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_plan_map );
    dbDelta( $sql_api_log );
    
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'spwp_connect_activate' );

\SpiritWP\Connect\Core\Plugin::get_instance();
