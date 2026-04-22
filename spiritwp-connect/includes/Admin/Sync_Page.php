<?php
namespace SpiritWP\Connect\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Sync_Page {
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_submenu_page' ] );
        add_action( 'admin_post_spwp_save_mapping', [ $this, 'handle_save_mapping' ] );
        add_action( 'admin_post_spwp_delete_mapping', [ $this, 'handle_delete_mapping' ] );
    }

    public function add_submenu_page() {
        add_submenu_page(
            'spiritwp-connect',
            __( 'Plan Sync', 'spiritwp-connect' ),
            __( 'Plan Sync', 'spiritwp-connect' ),
            'manage_options',
            'spiritwp-connect-sync',
            [ $this, 'render_sync_page' ]
        );
    }

    public function render_sync_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'spwp_plan_map';
        $mappings   = $wpdb->get_results( "SELECT * FROM {$table_name} ORDER BY id DESC" );

        $wc_products = function_exists('wc_get_products') ? wc_get_products(['limit' => -1, 'status' => 'publish']) : [];

        ?>
        <div class="wrap spwp-admin-wrap spwp-sync-wrap">
            <h1><?php esc_html_e( 'Product Mapping & Sync', 'spiritwp-connect' ); ?></h1>
            
            <div class="card spwp-card">
                <h2><?php esc_html_e( 'Add New Mapping', 'spiritwp-connect' ); ?></h2>
                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
                    <input type="hidden" name="action" value="spwp_save_mapping">
                    <?php wp_nonce_field( 'spwp_mapping_nonce', 'spwp_nonce' ); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="ce_product_id"><?php esc_html_e( 'Clientexec Product ID', 'spiritwp-connect' ); ?></label></th>
                            <td><input type="number" id="ce_product_id" name="ce_product_id" required class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="ce_product_name"><?php esc_html_e( 'CE Product Name (for reference)', 'spiritwp-connect' ); ?></label></th>
                            <td><input type="text" id="ce_product_name" name="ce_product_name" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="wc_product_id"><?php esc_html_e( 'WooCommerce Product', 'spiritwp-connect' ); ?></label></th>
                            <td>
                                <select id="wc_product_id" name="wc_product_id" required>
                                    <option value=""><?php esc_html_e( '-- Select Product --', 'spiritwp-connect' ); ?></option>
                                    <?php foreach ( $wc_products as $product ) : ?>
                                        <option value="<?php echo esc_attr( $product->get_id() ); ?>"><?php echo esc_html( $product->get_name() ); ?> (#<?php echo esc_html( $product->get_id() ); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button( __( 'Save Mapping', 'spiritwp-connect' ) ); ?>
                </form>
            </div>

            <h2 class="title"><?php esc_html_e( 'Current Mappings', 'spiritwp-connect' ); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'CE Product', 'spiritwp-connect' ); ?></th>
                        <th><?php esc_html_e( 'WP/WC Product', 'spiritwp-connect' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'spiritwp-connect' ); ?></th>
                        <th><?php esc_html_e( 'Last Synced', 'spiritwp-connect' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'spiritwp-connect' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $mappings ) ) : ?>
                        <tr><td colspan="5"><?php esc_html_e( 'No mappings found.', 'spiritwp-connect' ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $mappings as $map ) : ?>
                            <tr>
                                <td><?php echo esc_html( $map->ce_product_name ); ?> (#<?php echo esc_html( $map->ce_product_id ); ?>)</td>
                                <td><?php echo esc_html( $map->wc_product_name ); ?> (#<?php echo esc_html( $map->wc_product_id ); ?>)</td>
                                <td><span class="spwp-badge spwp-badge-<?php echo esc_attr( $map->status ); ?>"><?php echo esc_html( ucfirst( $map->status ) ); ?></span></td>
                                <td><?php echo $map->last_synced ? esc_html( $map->last_synced ) : 'Never'; ?></td>
                                <td>
                                    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" style="display:inline;">
                                        <input type="hidden" name="action" value="spwp_delete_mapping">
                                        <input type="hidden" name="map_id" value="<?php echo esc_attr( $map->id ); ?>">
                                        <?php wp_nonce_field( 'spwp_delete_mapping_' . $map->id, 'spwp_nonce' ); ?>
                                        <button type="submit" class="button button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'spiritwp-connect' ); ?>')"><?php esc_html_e( 'Delete', 'spiritwp-connect' ); ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function handle_save_mapping() {
        if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['spwp_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['spwp_nonce'] ) ), 'spwp_mapping_nonce' ) ) {
            wp_die( esc_html__( 'Security check failed', 'spiritwp-connect' ) );
        }

        global $wpdb;
        $table_name    = $wpdb->prefix . 'spwp_plan_map';
        
        $ce_product_id = absint( $_POST['ce_product_id'] );
        $ce_name       = sanitize_text_field( wp_unslash( $_POST['ce_product_name'] ) );
        $wc_product_id = absint( $_POST['wc_product_id'] );
        
        $wc_name = '';
        if ( function_exists('wc_get_product') && $wc_product_id > 0 ) {
            $product = wc_get_product( $wc_product_id );
            if ( $product ) {
                $wc_name = $product->get_name();
            }
        }

        $wpdb->insert(
            $table_name,
            [
                'ce_product_id'   => $ce_product_id,
                'ce_product_name' => $ce_name,
                'wc_product_id'   => $wc_product_id,
                'wc_product_name' => $wc_name,
                'created_at'      => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%d', '%s', '%s' ]
        );

        wp_redirect( admin_url( 'admin.php?page=spiritwp-connect-sync&message=saved' ) );
        exit;
    }

    public function handle_delete_mapping() {
        $map_id = isset( $_POST['map_id'] ) ? absint( $_POST['map_id'] ) : 0;
        
        if ( ! current_user_can( 'manage_options' ) || ! isset( $_POST['spwp_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['spwp_nonce'] ) ), 'spwp_delete_mapping_' . $map_id ) ) {
            wp_die( esc_html__( 'Security check failed', 'spiritwp-connect' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'spwp_plan_map';
        $wpdb->delete( $table_name, [ 'id' => $map_id ], [ '%d' ] );

        wp_redirect( admin_url( 'admin.php?page=spiritwp-connect-sync&message=deleted' ) );
        exit;
    }
}
