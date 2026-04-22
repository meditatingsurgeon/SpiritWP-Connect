<?php
namespace SpiritWP\Connect\Modules;

use SpiritWP\Connect\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Purchase_Handler {
    public function __construct() {
        add_action( 'woocommerce_order_status_completed', [ $this, 'process_purchase' ], 10, 1 );
    }

    public function process_purchase( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Check if already provisioned
        if ( 'true' === $order->get_meta( 'spwp_ce_provisioned' ) ) {
            return;
        }

        $ce_userid = $order->get_meta( 'spwp_ce_userid' );
        if ( empty( $ce_userid ) ) {
            $order->update_meta_data( 'spwp_ce_provisioned', 'false' );
            $order->update_meta_data( 'spwp_ce_error', 'No CE User ID mapped.' );
            $order->save_meta_data();
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'spwp_plan_map';
        $api = Plugin::get_instance()->api;
        
        $provisioned_something = false;
        $errors = [];

        foreach ( $order->get_items() as $item_id => $item ) {
            $product_id = $item->get_product_id();
            
            // Check if product is mapped
            $mapping = $wpdb->get_row( $wpdb->prepare( "SELECT ce_product_id FROM {$table_name} WHERE wc_product_id = %d AND status = 'active'", $product_id ) );
            
            if ( $mapping ) {
                $ce_product_id = $mapping->ce_product_id;
                
                // Add package to CE
                $result = $api->add_package( $ce_userid, $ce_product_id );
                
                if ( is_wp_error( $result ) ) {
                    $errors[] = $result->get_error_message();
                    $order->add_order_note( sprintf( __( 'CE Provisioning Failed for Product #%d: %s', 'spiritwp-connect' ), $product_id, $result->get_error_message() ) );
                } else if ( isset( $result['success'] ) && false === $result['success'] ) {
                    $errors[] = isset( $result['message'] ) ? $result['message'] : 'Unknown CE Error';
                    $order->add_order_note( sprintf( __( 'CE Provisioning Failed for Product #%d: %s', 'spiritwp-connect' ), $product_id, $errors[ count($errors)-1 ] ) );
                } else {
                    $provisioned_something = true;
                    // Clientexec might return package ID or custom success wrapper
                    // Note: CE API docs say it returns {success, message}. 
                    $package_id = isset( $result['packageid'] ) ? $result['packageid'] : 'unknown';
                    
                    if ( 'unknown' !== $package_id ) {
                        $order->update_meta_data( 'spwp_ce_package_id', $package_id );
                    }
                    $order->add_order_note( sprintf( __( 'CE package provisioned: %s', 'spiritwp-connect' ), $package_id ) );
                }
            }
        }

        if ( $provisioned_something && empty( $errors ) ) {
            $order->update_meta_data( 'spwp_ce_provisioned', 'true' );
            $order->delete_meta_data( 'spwp_ce_error' );
        } elseif ( ! empty( $errors ) ) {
            $order->update_meta_data( 'spwp_ce_provisioned', 'false' );
            $order->update_meta_data( 'spwp_ce_error', implode( ' | ', $errors ) );
        }
        
        $order->save_meta_data();
    }
}
