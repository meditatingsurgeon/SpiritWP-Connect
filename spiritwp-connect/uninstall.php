<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package SpiritWP_Connect
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// BUG-016 Fix: Drop tables
$tables = [
    $wpdb->prefix . 'spwp_plan_map',
    $wpdb->prefix . 'spwp_api_log',
];
foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

// BUG-016 Fix: Delete options
$options = [
    'spwp_ce_base_url', 
    'spwp_ce_app_key', 
    'spwp_ce_modules',
    'spwp_ce_sso_default', 
    'spwp_ce_cache_ttl', 
    'spwp_ce_cron_freq',
    'spwp_ce_debug_log',
];
foreach ( $options as $option ) {
    delete_option( $option );
}

// BUG-016 Fix: Purge CE API transients
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_ce\\_api\\_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_timeout\\_ce\\_api\\_%'" );
