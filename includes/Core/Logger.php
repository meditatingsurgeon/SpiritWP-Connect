<?php
namespace SpiritWP\Connect\Core;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Logger {
    public static function log( $method, $endpoint, $status_code, $response_time, $error_message = '' ) {
        if ( ! get_option( 'spwp_ce_debug_log', false ) ) { return; }
        global $wpdb;
        $table_name = $wpdb->prefix . 'spwp_api_log';
        if ( ! is_string( $error_message ) ) { $error_message = wp_json_encode( $error_message ); }
        $wpdb->insert( $table_name, [
            'method'        => sanitize_text_field( $method ),
            'endpoint'      => esc_url_raw( ltrim( $endpoint, '/' ) ),
            'status_code'   => absint( $status_code ),
            'response_time' => (float) $response_time,
            'error_message' => sanitize_textarea_field( $error_message ),
            'created_at'    => current_time( 'mysql' ),
        ], [ '%s', '%s', '%d', '%f', '%s', '%s' ] );
    }
}
