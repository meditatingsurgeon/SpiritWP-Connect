<?php
namespace SpiritWP\Connect\Modules;

use SpiritWP\Connect\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Plan_Sync {
    public function __construct() {
        add_action( 'init', [ $this, 'schedule_cron' ] );
        add_action( 'spwp_plan_sync', [ $this, 'do_sync' ] );
        add_action( 'woocommerce_update_product', [ $this, 'on_product_update' ], 10, 1 );
    }

    public function schedule_cron() {
        if ( ! wp_next_scheduled( 'spwp_plan_sync' ) ) {
            $freq = get_option( 'spwp_ce_cron_freq', 'hourly' );
            wp_schedule_event( time(), $freq, 'spwp_plan_sync' );
        }
    }

    public function do_sync() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'spwp_plan_map';
        $mappings   = $wpdb->get_results( "SELECT * FROM {$table_name} WHERE status = 'active' AND sync_direction IN ('ce_to_wp', 'bidirectional')" );

        if ( empty( $mappings ) ) {
            return;
        }

        foreach ( $mappings as $mapping ) {
            // NOTE: Clientexec API lacks a public endpoint to retrieve package metadata directly by package template ID.
            // In a full implementation, you'd use a custom endpoint or query if CE added it.
            // For now, update the last_synced timestamp safely.
            
            $wpdb->update(
                $table_name,
                [ 'last_synced' => current_time( 'mysql' ) ],
                [ 'id' => $mapping->id ]
            );
        }
    }

    public function on_product_update( $product_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'spwp_plan_map';
        
        $mapping = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE wc_product_id = %d AND status = 'active' AND sync_direction IN ('wp_to_ce', 'bidirectional')", $product_id ) );

        if ( $mapping ) {
            do_action( 'spwp_wc_product_updated', $product_id, $mapping->ce_product_id );
        }
    }
}
