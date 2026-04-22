<?php
namespace SpiritWP\Connect\Modules;

use SpiritWP\Connect\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Dashboard {
    public function __construct() {
        add_shortcode( 'spwp_dashboard', [ $this, 'render_shortcode' ] );
    }

    public function render_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in to view your dashboard.', 'spiritwp-connect' ) . '</p>';
        }

        $user = wp_get_current_user();
        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );

        if ( empty( $ce_userid ) ) {
            // Lazy load / attempt fetch
            $ce_user = Plugin::get_instance()->api->get_user_by_email( $user->user_email );
            if ( ! is_wp_error( $ce_user ) && isset( $ce_user['userid'] ) ) {
                $ce_userid = $ce_user['userid'];
                update_user_meta( $user->ID, 'spwp_ce_userid', $ce_userid );
            } else {
                return '<p>' . esc_html__( 'No billing account found. Please contact support.', 'spiritwp-connect' ) . '</p>';
            }
        }

        $api = Plugin::get_instance()->api;

        // Note: Clientexec API lacks a direct `getpackages` endpoint documented publicly.
        // If it exists but undocumented, we would call it here. For now, we simulate the array structure
        // we'd expect or leave an empty array if not possible.
        // We will fetch Invoices and Tickets as they are documented.

        $invoices_res = $api->get_invoices( $ce_userid );
        $tickets_res  = $api->get_tickets( $ce_userid, 'open' );

        $invoices = ( ! is_wp_error( $invoices_res ) && isset( $invoices_res['invoices'] ) ) ? $invoices_res['invoices'] : [];
        $tickets  = ( ! is_wp_error( $tickets_res ) && isset( $tickets_res['tickets'] ) ) ? $tickets_res['tickets'] : [];

        // Determine template path
        $template = locate_template( 'spiritwp-connect/dashboard.php' );
        if ( ! $template ) {
            $template = SPWP_CONNECT_PLUGIN_DIR . 'templates/dashboard.php';
        }

        ob_start();
        include $template;
        return ob_get_clean();
    }
}
