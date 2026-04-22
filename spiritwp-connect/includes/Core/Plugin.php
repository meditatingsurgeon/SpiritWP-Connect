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

    private function __construct() {}

    public function init() {
        $this->api = new API_Client();
        
        $this->load_admin();
        $this->load_modules();
    }

    private function load_admin() {
        if ( is_admin() ) {
            new \SpiritWP\Connect\Admin\Settings_Page();
            new \SpiritWP\Connect\Admin\Sync_Page();
            new \SpiritWP\Connect\Admin\Meta_Boxes();
            
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        }
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    public function enqueue_admin_assets() {
        wp_enqueue_style( 'spwp-connect-admin', SPWP_CONNECT_PLUGIN_URL . 'assets/css/admin.css', [], SPWP_CONNECT_VERSION );
        wp_enqueue_script( 'spwp-connect-admin', SPWP_CONNECT_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], SPWP_CONNECT_VERSION, true );
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style( 'spwp-connect-front', SPWP_CONNECT_PLUGIN_URL . 'assets/css/frontend.css', [], SPWP_CONNECT_VERSION );
    }

    private function load_modules() {
        $options = get_option( 'spwp_ce_modules', [] );
        
        if ( in_array( 'user_provisioning', $options, true ) ) {
            new \SpiritWP\Connect\Modules\User_Provisioning();
        }
        if ( in_array( 'plan_sync', $options, true ) ) {
            new \SpiritWP\Connect\Modules\Plan_Sync();
        }
        if ( in_array( 'sso', $options, true ) ) {
            new \SpiritWP\Connect\Modules\SSO_Bridge();
        }
        if ( in_array( 'dashboard', $options, true ) ) {
            new \SpiritWP\Connect\Modules\Dashboard();
        }
        if ( in_array( 'purchase_handler', $options, true ) && class_exists( 'WooCommerce' ) ) {
            new \SpiritWP\Connect\Modules\Purchase_Handler();
        }
        if ( in_array( 'support_centre', $options, true ) ) {
            new \SpiritWP\Connect\Modules\Support_Centre();
        }
    }
}
