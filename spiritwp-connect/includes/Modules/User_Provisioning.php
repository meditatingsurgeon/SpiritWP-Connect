<?php
namespace SpiritWP\Connect\Modules;

use SpiritWP\Connect\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class User_Provisioning {
    public function __construct() {
        add_action( 'user_register', [ $this, 'on_user_register' ], 20, 1 );
        add_action( 'wp_login', [ $this, 'on_wp_login' ], 20, 2 );
        add_action( 'profile_update', [ $this, 'on_profile_update' ], 20, 2 );
        
        if ( class_exists( 'WooCommerce' ) ) {
            add_action( 'woocommerce_checkout_order_processed', [ $this, 'on_woo_checkout' ], 10, 3 );
        }
    }

    // BUG-003 Fix: Lazy link on login
    public function on_wp_login( $user_login, $user ) {
        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );
        if ( ! empty( $ce_userid ) ) {
            return; // already linked
        }
        
        $api     = Plugin::get_instance()->api;
        $ce_user = $api->get_user_by_email( $user->user_email );
        
        // BUG-008 Fix: is_wp_error guard
        if ( is_wp_error( $ce_user ) ) {
            return; // silent fail - don't block login
        }
        
        if ( isset( $ce_user['userid'] ) && $ce_user['userid'] > 0 ) {
            update_user_meta( $user->ID, 'spwp_ce_userid', absint( $ce_user['userid'] ) );
        }
    }

    public function on_user_register( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        $this->provision_user( $user );
    }

    public function on_profile_update( $user_id, $old_user_data ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) return;

        $ce_userid = get_user_meta( $user_id, 'spwp_ce_userid', true );
        
        if ( ! empty( $ce_userid ) ) {
            $data = [
                'email'     => $user->user_email,
                'firstname' => $user->first_name ?: $user->user_login,
                'lastname'  => $user->last_name ?: '-',
            ];
            
            $data['address'] = get_user_meta( $user_id, 'billing_address_1', true );
            $data['city']    = get_user_meta( $user_id, 'billing_city', true );
            $data['state']   = get_user_meta( $user_id, 'billing_state', true );
            $data['zipcode'] = get_user_meta( $user_id, 'billing_postcode', true );
            $data['country'] = get_user_meta( $user_id, 'billing_country', true );
            $data['phone']   = get_user_meta( $user_id, 'billing_phone', true );

            // BUG-008 Fix: guard
            $api = Plugin::get_instance()->api;
            $res = $api->update_user( $ce_userid, $data );
            if ( is_wp_error( $res ) ) {
                error_log( 'SpiritWP Connect: CE user update failed: ' . $res->get_error_message() );
            }
        }
    }

    public function on_woo_checkout( $order_id, $posted_data, $order ) {
        if ( ! $order ) return;

        $user_id = $order->get_customer_id();

        if ( $user_id ) {
            $user = get_userdata( $user_id );
            if ( $user ) {
                $ce_userid = get_user_meta( $user_id, 'spwp_ce_userid', true );
                if ( empty( $ce_userid ) ) {
                    $ce_userid = $this->provision_user( $user, $order );
                }
                if ( $ce_userid ) {
                    $order->update_meta_data( 'spwp_ce_userid', $ce_userid );
                    $order->save_meta_data();
                }
            }
        } else {
            // Guest checkout
            $email = $order->get_billing_email();
            if ( ! empty( $email ) ) {
                $api = Plugin::get_instance()->api;
                $ce_user = $api->get_user_by_email( $email );
                
                if ( ! is_wp_error( $ce_user ) && isset( $ce_user['userid'] ) ) {
                    $order->update_meta_data( 'spwp_ce_userid', $ce_user['userid'] );
                    $order->save_meta_data();
                } else {
                    $data = [
                        'email'     => $email,
                        'firstname' => $order->get_billing_first_name() ?: 'Guest',
                        'lastname'  => $order->get_billing_last_name() ?: '-',
                        'password'  => wp_generate_password(),
                        'address'   => $order->get_billing_address_1(),
                        'city'      => $order->get_billing_city(),
                        'state'     => $order->get_billing_state(),
                        'zipcode'   => $order->get_billing_postcode(),
                        'country'   => $order->get_billing_country(),
                        'phone'     => $order->get_billing_phone(),
                        'status'    => 1 // Active
                    ];

                    $result = $api->create_user( $data );
                    if ( ! is_wp_error( $result ) && isset( $result['userid'] ) ) {
                        $order->update_meta_data( 'spwp_ce_userid', $result['userid'] );
                        $order->save_meta_data();
                    }
                }
            }
        }
    }

    private function provision_user( $user, $order = null ) {
        $api = Plugin::get_instance()->api;

        $ce_user = $api->get_user_by_email( $user->user_email );
        
        if ( ! is_wp_error( $ce_user ) && isset( $ce_user['userid'] ) ) {
            update_user_meta( $user->ID, 'spwp_ce_userid', $ce_user['userid'] );
            return $ce_user['userid'];
        }

        $data = [
            'email'     => $user->user_email,
            'firstname' => $user->first_name ?: $user->user_login,
            'lastname'  => $user->last_name ?: '-',
            'password'  => wp_generate_password(),
            'status'    => 1 // Active
        ];

        if ( $order && class_exists( 'WooCommerce' ) ) {
            $data['address'] = $order->get_billing_address_1();
            $data['city']    = $order->get_billing_city();
            $data['state']   = $order->get_billing_state();
            $data['zipcode'] = $order->get_billing_postcode();
            $data['country'] = $order->get_billing_country();
            $data['phone']   = $order->get_billing_phone();
        } else {
            $data['address'] = get_user_meta( $user->ID, 'billing_address_1', true );
            $data['city']    = get_user_meta( $user->ID, 'billing_city', true );
            $data['state']   = get_user_meta( $user->ID, 'billing_state', true );
            $data['zipcode'] = get_user_meta( $user->ID, 'billing_postcode', true );
            $data['country'] = get_user_meta( $user->ID, 'billing_country', true );
            $data['phone']   = get_user_meta( $user->ID, 'billing_phone', true );
        }

        $result = $api->create_user( $data );

        if ( ! is_wp_error( $result ) && isset( $result['userid'] ) ) {
            update_user_meta( $user->ID, 'spwp_ce_userid', $result['userid'] );
            return $result['userid'];
        } else if ( is_wp_error( $result ) ) {
            error_log( 'SpiritWP Connect: User provision failed: ' . $result->get_error_message() );
        }

        return false;
    }
}
