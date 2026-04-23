<?php
namespace SpiritWP\Connect\Modules;
use SpiritWP\Connect\Core\Plugin;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Support_Centre {
    public function __construct() {
        add_shortcode( 'spwp_tickets', [ $this, 'render_tickets' ] );
        add_shortcode( 'spwp_kb', [ $this, 'render_kb' ] );
    }

    public function render_tickets( $atts ) {
        if ( ! is_user_logged_in() ) { return '<p>' . esc_html__( 'You must be logged in to view tickets.', 'spiritwp-connect' ) . '</p>'; }
        $user = wp_get_current_user();
        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );
        if ( empty( $ce_userid ) ) { return '<p>' . esc_html__( 'No billing account found.', 'spiritwp-connect' ) . '</p>'; }
        $tickets = [];
        $template = locate_template( 'spiritwp-connect/tickets.php' );
        if ( ! $template ) { $template = SPWP_CONNECT_PLUGIN_DIR . 'templates/tickets.php'; }
        ob_start(); include $template; return ob_get_clean();
    }

    public function render_kb( $atts ) {
        $articles = [];
        $template = locate_template( 'spiritwp-connect/kb-articles.php' );
        if ( ! $template ) { $template = SPWP_CONNECT_PLUGIN_DIR . 'templates/kb-articles.php'; }
        ob_start(); include $template; return ob_get_clean();
    }
}
