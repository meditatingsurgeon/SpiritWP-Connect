<?php
namespace SpiritWP\Connect\Modules;
use SpiritWP\Connect\Core\Plugin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class User_Provisioning {
    public function __construct() {
        add_action( 'user_register', [ $this, 'on_user_register' ], 20, 1 );
        add_action( 'wp_login', [ $this, 'on_wp_login' ], 20, 2 );
        add_action( 'profile_update', [ $this, 'on_profile_update' ], 20, 2 );
    }

    public function on_user_register( $user_id ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) { return; }
        $this->provision_user( $user );
    }

    public function on_wp_login( $user_login, $user ) {
        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );
        if ( ! empty( $ce_userid ) ) { return; }
        $api = Plugin::get_instance()->api;
        $ce_user = $api->get_user_by_email( $user->user_email );
        if ( is_wp_error( $ce_user ) ) { return; }
        if ( isset( $ce_user['userid'] ) && $ce_user['userid'] > 0 ) {
            update_user_meta( $user->ID, 'spwp_ce_userid', absint( $ce_user['userid'] ) );
        }
    }

    public function on_profile_update( $user_id, $old_data ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) { return; }
        $ce_userid = get_user_meta( $user_id, 'spwp_ce_userid', true );
        if ( empty( $ce_userid ) ) { return; }
        $result = Plugin::get_instance()->api->update_user( $ce_userid, [
            'email'     => $user->user_email,
            'firstname' => $user->first_name ?: $user->display_name,
            'lastname'  => $user->last_name ?: '-',
        ] );
        if ( is_wp_error( $result ) ) { error_log( 'SpiritWP Connect: CE profile sync failed: ' . $result->get_error_message() ); }
    }

    private function provision_user( $user ) {
        $api = Plugin::get_instance()->api;
        $ce_user = $api->get_user_by_email( $user->user_email );
        if ( is_wp_error( $ce_user ) ) { error_log( 'SpiritWP Connect: CE lookup failed: ' . $ce_user->get_error_message() ); return false; }
        if ( isset( $ce_user['userid'] ) && $ce_user['userid'] > 0 ) {
            update_user_meta( $user->ID, 'spwp_ce_userid', absint( $ce_user['userid'] ) );
            return absint( $ce_user['userid'] );
        }
        $result = $api->create_user( [
            'email'     => $user->user_email,
            'firstname' => $user->first_name ?: $user->display_name ?: $user->user_login,
            'lastname'  => $user->last_name ?: '-',
            'password'  => wp_generate_password( 16, true, true ),
            'status'    => 1,
        ] );
        if ( is_wp_error( $result ) ) { error_log( 'SpiritWP Connect: CE create_user failed: ' . $result->get_error_message() ); return false; }
        if ( isset( $result['userid'] ) && $result['userid'] > 0 ) {
            update_user_meta( $user->ID, 'spwp_ce_userid', absint( $result['userid'] ) );
            return absint( $result['userid'] );
        }
        return false;
    }
}
