<?php
namespace SpiritWP\Connect\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Plugin {
    private static $instance = null;
    public API_Client $api;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->api = new API_Client();
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    public function init() {
        if ( is_admin() ) {
            new \SpiritWP\Connect\Admin\Settings_Page();
            new \SpiritWP\Connect\Admin\Sync_Page();
            if ( class_exists( 'WooCommerce' ) ) {
                new \SpiritWP\Connect\Admin\Meta_Boxes();
            }
        }

        $this->load_modules();
    }

    private function load_modules() {
        $options = get_option( 'spwp_ce_modules', [] );
        
        if ( in_array( 'user_provisioning', $options, true ) ) {
            new \SpiritWP\Connect\Modules\User_Provisioning();
        }
        if ( in_array( 'sso', $options, true ) ) {
            new \SpiritWP\Connect\Modules\SSO_Bridge();
        }
        if ( in_array( 'dashboard', $options, true ) ) {
            new \SpiritWP\Connect\Modules\Dashboard();
        }
        if ( in_array( 'support_centre', $options, true ) ) {
            new \SpiritWP\Connect\Modules\Support_Centre();
        }
        if ( in_array( 'plan_sync', $options, true ) ) {
            new \SpiritWP\Connect\Modules\Plan_Sync();
        }
        // BUG-006 Fix: WooCommerce strictly optional gating
        if ( in_array( 'purchase_handler', $options, true ) && class_exists( 'WooCommerce' ) ) {
            new \SpiritWP\Connect\Modules\Purchase_Handler();
        }
    }
}
