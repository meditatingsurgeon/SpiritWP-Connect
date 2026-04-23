<?php
namespace SpiritWP\Connect\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings_Page {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function add_menu_page() {
        add_menu_page(
            __( 'SpiritWP Connect', 'spiritwp-connect' ),
            __( 'SpiritWP', 'spiritwp-connect' ),
            'manage_options',
            'spwp-connect',
            [ $this, 'render_page' ],
            'dashicons-cloud',
            56
        );
    }

    public function register_settings() {
        register_setting( 'spwp_ce_settings', 'spwp_ce_base_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        register_setting( 'spwp_ce_settings', 'spwp_ce_app_key', [ 'sanitize_callback' => 'sanitize_text_field' ] );
        
        // BUG-015 Fix: Modules array sanitization
        register_setting( 'spwp_ce_settings', 'spwp_ce_modules', [
            'type' => 'array',
            'sanitize_callback' => function( $input ) {
                return (array) $input;
            }
        ] );

        register_setting( 'spwp_ce_settings', 'spwp_ce_sso_default', [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'spwp_ce_settings', 'spwp_ce_cache_ttl', [ 'sanitize_callback' => 'absint' ] );
        register_setting( 'spwp_ce_settings', 'spwp_ce_cron_freq', [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'spwp_ce_settings', 'spwp_ce_debug_log', [ 'sanitize_callback' => 'absint' ] );
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_spwp-connect' !== $hook ) {
            return;
        }
        wp_enqueue_style( 'spwp-admin-css', plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/css/admin.css', [], '1.1.0' );
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // BUG-015 Fix: Missing options
        $modules = [
            'user_provisioning' => __( 'User Provisioning (auto-create CE accounts on WP registration)', 'spiritwp-connect' ),
            'sso'               => __( 'Single Sign-On (/ce-login/ endpoint)', 'spiritwp-connect' ),
            'dashboard'         => __( 'Customer Dashboard ([spwp_dashboard] shortcode)', 'spiritwp-connect' ),
            'support_centre'    => __( 'Support Centre ([spwp_tickets] and [spwp_kb] shortcodes)', 'spiritwp-connect' ),
            'plan_sync'         => __( 'Plan Sync Engine (WC product → CE package mapping)', 'spiritwp-connect' ),
            'purchase_handler'  => __( 'WooCommerce Purchase Handler (auto-provision on order complete)', 'spiritwp-connect' ),
        ];
        
        $active_modules = get_option( 'spwp_ce_modules', [] );
        if ( ! is_array( $active_modules ) ) $active_modules = [];
        ?>
        <div class="wrap spwp-admin-wrap spwp-wrap">
            <h1><?php esc_html_e( 'SpiritWP Connect', 'spiritwp-connect' ); ?> <span class="spwp-badge spwp-badge-active">v1.1.0</span></h1>
            <p class="description"><?php esc_html_e( 'Bidirectional Integration with Clientexec', 'spiritwp-connect' ); ?></p>
            
            <form action="options.php" method="post">
                <?php settings_fields( 'spwp_ce_settings' ); ?>
                
                <div class="spwp-card">
                    <h2><?php esc_html_e( 'API Configuration', 'spiritwp-connect' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="spwp_ce_base_url"><?php esc_html_e( 'Clientexec Base URL', 'spiritwp-connect' ); ?></label></th>
                            <td>
                                <input name="spwp_ce_base_url" type="url" id="spwp_ce_base_url" value="<?php echo esc_attr( get_option('spwp_ce_base_url') ); ?>" class="regular-text" placeholder="https://my.spiritvm.net">
                                <p class="description"><?php esc_html_e( 'The full URL to your Clientexec installation.', 'spiritwp-connect' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="spwp_ce_app_key"><?php esc_html_e( 'Application Key', 'spiritwp-connect' ); ?></label></th>
                            <td>
                                <input name="spwp_ce_app_key" type="password" id="spwp_ce_app_key" value="<?php echo esc_attr( get_option('spwp_ce_app_key') ); ?>" class="regular-text">
                                <p class="description"><?php esc_html_e( 'Generated in Clientexec > Settings > Plugins > API.', 'spiritwp-connect' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="spwp_ce_cache_ttl"><?php esc_html_e( 'Cache TTL (seconds)', 'spiritwp-connect' ); ?></label></th>
                            <td>
                                <input name="spwp_ce_cache_ttl" type="number" id="spwp_ce_cache_ttl" value="<?php echo esc_attr( get_option('spwp_ce_cache_ttl', 300) ); ?>" class="small-text">
                                <p class="description"><?php esc_html_e( 'Time to cache CE GET requests (default: 300).', 'spiritwp-connect' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="spwp-card">
                    <h2><?php esc_html_e( 'Modules', 'spiritwp-connect' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Active Modules', 'spiritwp-connect' ); ?></th>
                            <td>
                                <fieldset>
                                    <?php foreach ( $modules as $key => $label ) : ?>
                                        <label>
                                            <input type="checkbox" name="spwp_ce_modules[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $active_modules, true ) ); ?>>
                                            <?php echo esc_html( $label ); ?>
                                        </label><br>
                                    <?php endforeach; ?>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="spwp_ce_sso_default"><?php esc_html_e( 'SSO Default Route', 'spiritwp-connect' ); ?></label></th>
                            <td>
                                <select name="spwp_ce_sso_default" id="spwp_ce_sso_default">
                                    <option value="dashboard" <?php selected( get_option( 'spwp_ce_sso_default' ), 'dashboard' ); ?>>Dashboard</option>
                                    <option value="invoices" <?php selected( get_option( 'spwp_ce_sso_default' ), 'invoices' ); ?>>Invoices</option>
                                    <option value="tickets" <?php selected( get_option( 'spwp_ce_sso_default' ), 'tickets' ); ?>>Tickets</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="spwp-card">
                    <h2><?php esc_html_e( 'Advanced', 'spiritwp-connect' ); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Debug Logging', 'spiritwp-connect' ); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="spwp_ce_debug_log" value="1" <?php checked( get_option( 'spwp_ce_debug_log' ), 1 ); ?>>
                                    <?php esc_html_e( 'Log all API requests to database (wp_spwp_api_log)', 'spiritwp-connect' ); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button( __( 'Save Changes', 'spiritwp-connect' ), 'primary spwp-btn spwp-btn-primary' ); ?>
            </form>
        </div>
        <?php
    }
}
