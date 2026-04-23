<?php
namespace SpiritWP\Connect\Modules;
use SpiritWP\Connect\Core\Plugin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Purchase_Handler {
    public function __construct() {
        add_action( 'woocommerce_order_status_completed', [ $this, 'process_purchase' ], 10, 1 );
    }

    public function process_purchase( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) { return; }
        if ( 'true' === $order->get_meta( 'spwp_ce_provisioned' ) ) { return; }
        $ce_userid = $order->get_meta( 'spwp_ce_userid' );
        if ( empty( $ce_userid ) ) { $order->update_meta_data( 'spwp_ce_provisioned', 'false' ); $order->save_meta_data(); return; }
        global $wpdb;
        $t = $wpdb->prefix . 'spwp_plan_map';
        $api = Plugin::get_instance()->api;
        $provisioned = false; $errors = [];
        foreach ( $order->get_items() as $id => $item ) {
            $pid = $item->get_product_id();
            $m = $wpdb->get_row( $wpdb->prepare( "SELECT ce_product_id FROM $t WHERE wc_product_id = %d AND status = 'active'", $pid ) );
            if ( $m ) {
                $r = $api->add_package( $ce_userid, $m->ce_product_id );
                if ( is_wp_error( $r ) ) { $errors[] = $r->get_error_message(); } else { $provisioned = true; }
            }
        }
        if ( $provisioned && empty( $errors ) ) { $order->update_meta_data( 'spwp_ce_provisioned', 'true' ); }
        elseif ( ! empty( $errors ) ) { $order->update_meta_data( 'spwp_ce_provisioned', 'false' ); $order->update_meta_data( 'spwp_ce_error', implode( ' | ', $errors ) ); }
        $order->save_meta_data();
    }
}
