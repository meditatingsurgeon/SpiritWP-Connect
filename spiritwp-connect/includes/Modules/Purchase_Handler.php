<?php
namespace SpiritWP\Connect\Modules;

use SpiritWP\Connect\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Purchase_Handler {
    public function __construct() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }
        add_action( 'woocommerce_order_status_completed', [ $this, 'process_purchase' ], 10, 1 );
    }

    public function process_purchase( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // BUG-013 Fix: Idempotency — skip if already provisioned
        if ( 'true' === $order->get_meta( 'spwp_ce_provisioned' ) ) {
            return;
        }

        $ce_userid = $order->get_meta( 'spwp_ce_userid' );
        if ( empty( $ce_userid ) ) {
            $user_id = $order->get_customer_id();
            if ( $user_id ) {
                $ce_userid = get_user_meta( $user_id, 'spwp_ce_userid', true );
            }
        }

        if ( empty( $ce_userid ) ) {
            $order->update_meta_data( 'spwp_ce_error', 'Missing Clientexec User ID for provisioning' );
            $order->save_meta_data();
            return;
        }

        $api = Plugin::get_instance()->api;
        global $wpdb;
        $table = $wpdb->prefix . 'spwp_plan_map';
        $provisioned = false;
        $errors = [];

        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            $mapping = $wpdb->get_row( $wpdb->prepare( "SELECT ce_product_id FROM {$table} WHERE wc_product_id = %d AND status = 'active'", $product_id ) );

            if ( $mapping ) {
                $quantity = $item->get_quantity();
                for ( $i = 0; $i < $quantity; $i++ ) {
                    $result = $api->add_package( $ce_userid, $mapping->ce_product_id );
                    
                    // BUG-008 Fix: is_wp_error guard
                    if ( is_wp_error( $result ) ) {
                        $errors[] = 'Package ID ' . $mapping->ce_product_id . ' failed: ' . $result->get_error_message();
                    } else {
                        $provisioned = true;
                    }
                }
            }
        }

        if ( $provisioned && empty( $errors ) ) {
            $order->update_meta_data( 'spwp_ce_provisioned', 'true' );
        } elseif ( ! empty( $errors ) ) {
            $order->update_meta_data( 'spwp_ce_provisioned', 'false' );
            $order->update_meta_data( 'spwp_ce_error', implode( ' | ', $errors ) );
        }
        $order->save_meta_data();
    }
}
