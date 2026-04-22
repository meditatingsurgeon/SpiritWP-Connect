<?php
// Uninstall script for SpiritWP Connect

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop custom tables
$plan_map_table = $wpdb->prefix . 'spwp_plan_map';
$api_log_table = $wpdb->prefix . 'spwp_api_log';

$wpdb->query( "DROP TABLE IF EXISTS {$plan_map_table}" );
$wpdb->query( "DROP TABLE IF EXISTS {$api_log_table}" );

// Delete plugin options
delete_option( 'spwp_ce_base_url' );
delete_option( 'spwp_ce_app_key' );
delete_option( 'spwp_ce_modules' );
delete_option( 'spwp_ce_sso_default' );
delete_option( 'spwp_ce_cache_ttl' );
delete_option( 'spwp_ce_cron_freq' );
delete_option( 'spwp_ce_debug_log' );
