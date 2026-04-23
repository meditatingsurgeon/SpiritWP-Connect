<?php
namespace SpiritWP\Connect\Modules;
use SpiritWP\Connect\Core\Plugin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Dashboard {
    public function __construct() {
        add_shortcode( 'spwp_dashboard', [ $this, 'render_shortcode' ] );
    }

    public function render_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in to view your dashboard.', 'spiritwp-connect' ) . '</p>';
        }
        $user      = wp_get_current_user();
        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );
        $api       = Plugin::get_instance()->api;

        if ( empty( $ce_userid ) ) {
            $ce_user = $api->get_user_by_email( $user->user_email );
            if ( ! is_wp_error( $ce_user ) && isset( $ce_user['userid'] ) && $ce_user['userid'] > 0 ) {
                $ce_userid = absint( $ce_user['userid'] );
                update_user_meta( $user->ID, 'spwp_ce_userid', $ce_userid );
            } else {
                return '<p>' . esc_html__( 'No billing account found. Please contact support or use the link below to manage your account.', 'spiritwp-connect' ) . '</p>'
                    . '<p><a href="' . esc_url( home_url( '/ce-login/' ) ) . '" class="button">' . esc_html__( 'Billing Portal', 'spiritwp-connect' ) . '</a></p>';
            }
        }

        $packages_res = $api->get_packages( $ce_userid, 'all' );
        $all_packages = ( ! is_wp_error( $packages_res ) && isset( $packages_res['data'] ) ) ? $packages_res['data'] : [];

        $hosting_packages = []; $webinar_packages = [];
        foreach ( $all_packages as $pkg ) {
            $pn = strtolower( $pkg['product_name'] ?? $pkg['name'] ?? '' );
            if ( str_contains( $pn, 'webinar' ) || str_contains( $pn, 'spirit.ws' ) ) {
                $webinar_packages[] = $pkg;
            } else {
                $hosting_packages[] = $pkg;
            }
        }

        $sso_portal_url   = home_url( '/ce-login/' );
        $sso_invoices_url = home_url( '/ce-login/?goto=invoices' );
        $sso_support_url  = home_url( '/ce-login/?goto=support' );
        $sso_store_url    = home_url( '/ce-login/?goto=store' );

        $template = locate_template( 'spiritwp-connect/dashboard.php' );
        if ( ! $template ) { $template = SPWP_CONNECT_PLUGIN_DIR . 'templates/dashboard.php'; }
        ob_start(); include $template; return ob_get_clean();
    }
}
