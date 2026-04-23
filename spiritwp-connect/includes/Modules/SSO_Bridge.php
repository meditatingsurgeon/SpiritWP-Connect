<?php
namespace SpiritWP\Connect\Modules;

use SpiritWP\Connect\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SSO_Bridge {
    public function __construct() {
        add_action( 'init', [ $this, 'add_rewrite_rule' ] );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
        add_action( 'template_redirect', [ $this, 'handle_sso_endpoint' ] );
    }

    public function add_rewrite_rule() {
        add_rewrite_rule( '^ce-login/?$', 'index.php?ce-login=1', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'ce-login';
        return $vars;
    }

    public function handle_sso_endpoint() {
        global $wp_query;
        if ( ! isset( $wp_query->query_vars['ce-login'] ) ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            auth_redirect();
            exit;
        }

        $user = wp_get_current_user();
        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );

        // BUG-012 Fix: Lazy link if missing
        if ( empty( $ce_userid ) ) {
            $api     = Plugin::get_instance()->api;
            $ce_user = $api->get_user_by_email( $user->user_email );
            
            if ( ! is_wp_error( $ce_user ) && isset( $ce_user['userid'] ) ) {
                update_user_meta( $user->ID, 'spwp_ce_userid', $ce_user['userid'] );
            } else {
                wp_die( esc_html__( 'Unable to locate your billing account. Please contact support.', 'spiritwp-connect' ) );
            }
        }

        $goto = isset( $_GET['goto'] ) ? sanitize_text_field( wp_unslash( $_GET['goto'] ) ) : get_option( 'spwp_ce_sso_default', 'dashboard' );
        
        $sso_url = Plugin::get_instance()->api->generate_sso_url( $user->user_email, $goto );

        if ( empty( $sso_url ) ) {
            wp_die( esc_html__( 'SSO is not completely configured.', 'spiritwp-connect' ) );
        }

        wp_redirect( esc_url_raw( $sso_url ) );
        exit;
    }
}
