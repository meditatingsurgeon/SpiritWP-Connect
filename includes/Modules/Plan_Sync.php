<?php
namespace SpiritWP\Connect\Modules;
use SpiritWP\Connect\Core\Plugin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Plan_Sync {
    public function __construct() {
        add_action( 'init', [ $this, 'schedule_cron' ] );
        add_action( 'spwp_plan_sync', [ $this, 'do_sync' ] );
        add_action( 'woocommerce_update_product', [ $this, 'on_product_update' ], 10, 1 );
    }

    public function schedule_cron() {
        if ( ! wp_next_scheduled( 'spwp_plan_sync' ) ) {
            wp_schedule_event( time(), get_option( 'spwp_ce_cron_freq', 'hourly' ), 'spwp_plan_sync' );
        }
    }

    public function do_sync() {
        global $wpdb;
        $t = $wpdb->prefix . 'spwp_plan_map';
        $mappings = $wpdb->get_results( "SELECT * FROM $t WHERE status = 'active'" );
        if ( empty( $mappings ) ) { return; }
        foreach ( $mappings as $m ) {
            $wpdb->update( $t, [ 'last_synced' => current_time( 'mysql' ) ], [ 'id' => $m->id ] );
        }
    }

    public function on_product_update( $product_id ) {
        global $wpdb;
        $t = $wpdb->prefix . 'spwp_plan_map';
        $m = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $t WHERE wc_product_id = %d AND status = 'active'", $product_id ) );
        if ( $m ) { do_action( 'spwp_wc_product_updated', $product_id, $m->ce_product_id ); }
    }
}
