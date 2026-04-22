<?php
namespace SpiritWP\Connect\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Settings_Page {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_menu_page() {
        add_menu_page(
            __( 'SpiritWP Connect', 'spiritwp-connect' ),
            __( 'SpiritWP', 'spiritwp-connect' ),
            'manage_options',
            'spiritwp-connect',
            [ $this, 'render_settings_page' ],
            'dashicons-cloud',
            80
        );

        add_submenu_page(
            'spiritwp-connect',
            __( 'Settings', 'spiritwp-connect' ),
            __( 'Settings', 'spiritwp-connect' ),
            'manage_options',
            'spiritwp-connect',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'spwp_connect_settings', 'spwp_ce_base_url', [ $this, 'sanitize_url' ] );
        register_setting( 'spwp_connect_settings', 'spwp_ce_app_key', 'sanitize_text_field' );
        register_setting( 'spwp_connect_settings', 'spwp_ce_modules' ); // Array sanitizer
        register_setting( 'spwp_connect_settings', 'spwp_ce_sso_default', 'sanitize_text_field' );
        register_setting( 'spwp_connect_settings', 'spwp_ce_cache_ttl', 'absint' );
        register_setting( 'spwp_connect_settings', 'spwp_ce_cron_freq', 'sanitize_text_field' );
        register_setting( 'spwp_connect_settings', 'spwp_ce_debug_log', 'rest_sanitize_boolean' );
    }

    public function sanitize_url( $url ) {
        return esc_url_raw( rtrim( $url, '/' ) );
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $modules = get_option( 'spwp_ce_modules', [] );
        if ( ! is_array( $modules ) ) {
            $modules = [];
        }
        ?>
        <div class="wrap spwp-admin-wrap">
            <h1><?php esc_html_e( 'SpiritWP Connect Settings', 'spiritwp-connect' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'spwp_connect_settings' ); ?>
                
                <h2 class="title"><?php esc_html_e( 'API Connection', 'spiritwp-connect' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="spwp_ce_base_url"><?php esc_html_e( 'Clientexec Base URL', 'spiritwp-connect' ); ?></label></th>
                        <td>
                            <input type="url" id="spwp_ce_base_url" name="spwp_ce_base_url" value="<?php echo esc_attr( get_option( 'spwp_ce_base_url' ) ); ?>" class="regular-text" placeholder="https://my.spiritvm.net">
                            <p class="description"><?php esc_html_e( 'The full URL to your Clientexec installation.', 'spiritwp-connect' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="spwp_ce_app_key"><?php esc_html_e( 'Application Key', 'spiritwp-connect' ); ?></label></th>
                        <td>
                            <input type="password" id="spwp_ce_app_key" name="spwp_ce_app_key" value="<?php echo esc_attr( get_option( 'spwp_ce_app_key' ) ); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e( 'Found in Clientexec under Settings > Security > Application Key.', 'spiritwp-connect' ); ?></p>
                        </td>
                    </tr>
                </table>

                <h2 class="title"><?php esc_html_e( 'Active Modules', 'spiritwp-connect' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Modules', 'spiritwp-connect' ); ?></th>
                        <td>
                            <fieldset>
                                <label><input type="checkbox" name="spwp_ce_modules[]" value="user_provisioning" <?php checked( in_array( 'user_provisioning', $modules ) ); ?>> <?php esc_html_e( 'User Provisioning (Auto-create CE users)', 'spiritwp-connect' ); ?></label><br>
                                <label><input type="checkbox" name="spwp_ce_modules[]" value="plan_sync" <?php checked( in_array( 'plan_sync', $modules ) ); ?>> <?php esc_html_e( 'Plan Sync Engine', 'spiritwp-connect' ); ?></label><br>
                                <label><input type="checkbox" name="spwp_ce_modules[]" value="sso" <?php checked( in_array( 'sso', $modules ) ); ?>> <?php esc_html_e( 'AutoLogin SSO Bridge', 'spiritwp-connect' ); ?></label><br>
                                <label><input type="checkbox" name="spwp_ce_modules[]" value="dashboard" <?php checked( in_array( 'dashboard', $modules ) ); ?>> <?php esc_html_e( 'Customer Dashboard (Shortcode)', 'spiritwp-connect' ); ?></label><br>
                                <label><input type="checkbox" name="spwp_ce_modules[]" value="purchase_handler" <?php checked( in_array( 'purchase_handler', $modules ) ); ?>> <?php esc_html_e( 'WooCommerce Purchase Handler', 'spiritwp-connect' ); ?></label><br>
                                <label><input type="checkbox" name="spwp_ce_modules[]" value="support_centre" <?php checked( in_array( 'support_centre', $modules ) ); ?>> <?php esc_html_e( 'Support Centre', 'spiritwp-connect' ); ?></label>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <h2 class="title"><?php esc_html_e( 'Advanced Configuration', 'spiritwp-connect' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="spwp_ce_sso_default"><?php esc_html_e( 'SSO Default Destination', 'spiritwp-connect' ); ?></label></th>
                        <td>
                            <select id="spwp_ce_sso_default" name="spwp_ce_sso_default">
                                <option value="dashboard" <?php selected( get_option( 'spwp_ce_sso_default', 'dashboard' ), 'dashboard' ); ?>><?php esc_html_e( 'Dashboard', 'spiritwp-connect' ); ?></option>
                                <option value="packages" <?php selected( get_option( 'spwp_ce_sso_default' ), 'packages' ); ?>><?php esc_html_e( 'Packages', 'spiritwp-connect' ); ?></option>
                                <option value="invoices" <?php selected( get_option( 'spwp_ce_sso_default' ), 'invoices' ); ?>><?php esc_html_e( 'Invoices', 'spiritwp-connect' ); ?></option>
                                <option value="tickets" <?php selected( get_option( 'spwp_ce_sso_default' ), 'tickets' ); ?>><?php esc_html_e( 'Tickets', 'spiritwp-connect' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="spwp_ce_cache_ttl"><?php esc_html_e( 'Cache TTL (seconds)', 'spiritwp-connect' ); ?></label></th>
                        <td>
                            <input type="number" id="spwp_ce_cache_ttl" name="spwp_ce_cache_ttl" value="<?php echo esc_attr( get_option( 'spwp_ce_cache_ttl', 300 ) ); ?>" class="small-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="spwp_ce_cron_freq"><?php esc_html_e( 'Sync Cron Frequency', 'spiritwp-connect' ); ?></label></th>
                        <td>
                            <select id="spwp_ce_cron_freq" name="spwp_ce_cron_freq">
                                <option value="hourly" <?php selected( get_option( 'spwp_ce_cron_freq', 'hourly' ), 'hourly' ); ?>><?php esc_html_e( 'Hourly', 'spiritwp-connect' ); ?></option>
                                <option value="twicedaily" <?php selected( get_option( 'spwp_ce_cron_freq' ), 'twicedaily' ); ?>><?php esc_html_e( 'Twice Daily', 'spiritwp-connect' ); ?></option>
                                <option value="daily" <?php selected( get_option( 'spwp_ce_cron_freq' ), 'daily' ); ?>><?php esc_html_e( 'Daily', 'spiritwp-connect' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Debug Logging', 'spiritwp-connect' ); ?></th>
                        <td>
                            <label><input type="checkbox" name="spwp_ce_debug_log" value="1" <?php checked( get_option( 'spwp_ce_debug_log', false ) ); ?>> <?php esc_html_e( 'Log all API requests to database', 'spiritwp-connect' ); ?></label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
