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
            return '<div class="spwp-wrap spwp-dashboard-wrapper"><div class="spwp-panel"><div class="spwp-empty-state">Please log in to view your dashboard.</div></div></div>';
        }

        $user = wp_get_current_user();
        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );

        if ( empty( $ce_userid ) ) {
            return '<div class="spwp-wrap spwp-dashboard-wrapper"><div class="spwp-panel"><div class="spwp-empty-state">Your account is not linked to the billing system.</div></div></div>';
        }

        $api = Plugin::get_instance()->api;

        // BUG-007 Fix: Split packages out
        $packages_res = $api->get_packages( $ce_userid, 'all' );
        
        $hosting_packages = [];
        $webinar_packages = [];

        // BUG-008 Fix: is_wp_error guard
        if ( ! is_wp_error( $packages_res ) && isset( $packages_res['data'] ) ) {
            foreach ( $packages_res['data'] as $pkg ) {
                $pn = strtolower( $pkg['product_name'] ?? $pkg['name'] ?? '' );
                if ( str_contains( $pn, 'webinar' ) || str_contains( $pn, 'spirit.ws' ) ) {
                    $webinar_packages[] = $pkg;
                } else {
                    $hosting_packages[] = $pkg;
                }
            }
        }

        // Fetch Recent Invoices
        $invoices_res = $api->get_invoices( $ce_userid );
        $invoices = ! is_wp_error( $invoices_res ) && isset( $invoices_res['data'] ) ? array_slice( $invoices_res['data'], 0, 5 ) : [];
        if ( is_wp_error( $invoices_res ) ) {
            $invoices = [];
        }

        // Fetch Open Tickets
        $tickets_res = $api->get_tickets( $ce_userid, 'open' );
        $tickets = ! is_wp_error( $tickets_res ) && isset( $tickets_res['data'] ) ? array_slice( $tickets_res['data'], 0, 5 ) : [];
        if ( is_wp_error( $tickets_res ) ) {
            $tickets = [];
        }

        ob_start();
        include dirname( dirname( __DIR__ ) ) . '/templates/dashboard.php';
        return ob_get_clean();
    }
}
