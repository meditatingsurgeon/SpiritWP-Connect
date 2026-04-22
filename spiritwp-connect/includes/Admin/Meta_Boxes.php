<?php
namespace SpiritWP\Connect\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Meta_Boxes {
    public function __construct() {
        add_action( 'show_user_profile', [ $this, 'user_profile_fields' ] );
        add_action( 'edit_user_profile', [ $this, 'user_profile_fields' ] );
        add_action( 'personal_options_update', [ $this, 'save_user_profile_fields' ] );
        add_action( 'edit_user_profile_update', [ $this, 'save_user_profile_fields' ] );

        if ( class_exists( 'WooCommerce' ) ) {
            add_action( 'add_meta_boxes', [ $this, 'add_order_meta_box' ] );
            add_filter( 'manage_edit-shop_order_columns', [ $this, 'custom_order_column' ], 20 );
            add_action( 'manage_shop_order_posts_custom_column', [ $this, 'custom_order_column_content' ], 10, 2 );
        }
    }

    public function user_profile_fields( $user ) {
        if ( ! current_user_can( 'edit_users' ) ) {
            return;
        }

        $ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );
        ?>
        <h3><?php esc_html_e( 'Clientexec Integration', 'spiritwp-connect' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="spwp_ce_userid"><?php esc_html_e( 'Clientexec User ID', 'spiritwp-connect' ); ?></label></th>
                <td>
                    <input type="text" name="spwp_ce_userid" id="spwp_ce_userid" value="<?php echo esc_attr( $ce_userid ); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e( 'The ID of this user in Clientexec. Leave blank to auto-provision.', 'spiritwp-connect' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_user_profile_fields( $user_id ) {
        if ( ! current_user_can( 'edit_users' ) ) {
            return false;
        }
        
        if ( isset( $_POST['spwp_ce_userid'] ) ) {
            $ce_userid = sanitize_text_field( wp_unslash( $_POST['spwp_ce_userid'] ) );
            if ( empty( $ce_userid ) ) {
                delete_user_meta( $user_id, 'spwp_ce_userid' );
            } else {
                update_user_meta( $user_id, 'spwp_ce_userid', $ce_userid );
            }
        }
    }

    public function add_order_meta_box() {
        add_meta_box(
            'spwp_ce_order_data',
            __( 'SpiritWP Connect: CE Provisioning', 'spiritwp-connect' ),
            [ $this, 'render_order_meta_box' ],
            'shop_order',
            'side',
            'default'
        );
    }

    public function render_order_meta_box( $post ) {
        $order = wc_get_order( $post->ID );
        if ( ! $order ) return;

        $provisioned = $order->get_meta( 'spwp_ce_provisioned' );
        $package_id  = $order->get_meta( 'spwp_ce_package_id' );
        $error       = $order->get_meta( 'spwp_ce_error' );
        $ce_userid   = $order->get_meta( 'spwp_ce_userid' );

        echo '<ul>';
        echo '<li><strong>' . esc_html__( 'Status:', 'spiritwp-connect' ) . '</strong> ';
        if ( 'true' === $provisioned ) {
            echo '<span style="color: green;">' . esc_html__( 'Provisioned', 'spiritwp-connect' ) . '</span>';
        } elseif ( 'false' === $provisioned ) {
            echo '<span style="color: red;">' . esc_html__( 'Failed', 'spiritwp-connect' ) . '</span>';
        } else {
            echo '<span style="color: gray;">' . esc_html__( 'Pending/Not Applicable', 'spiritwp-connect' ) . '</span>';
        }
        echo '</li>';

        if ( $ce_userid ) {
            echo '<li><strong>' . esc_html__( 'CE User ID:', 'spiritwp-connect' ) . '</strong> ' . esc_html( $ce_userid ) . '</li>';
        }
        if ( $package_id ) {
            echo '<li><strong>' . esc_html__( 'CE Package ID:', 'spiritwp-connect' ) . '</strong> ' . esc_html( $package_id ) . '</li>';
        }
        if ( $error ) {
            echo '<li><strong>' . esc_html__( 'Error:', 'spiritwp-connect' ) . '</strong> <span style="color:red;">' . esc_html( $error ) . '</span></li>';
        }
        echo '</ul>';
    }

    public function custom_order_column( $columns ) {
        $columns['spwp_ce_status'] = __( 'CE Status', 'spiritwp-connect' );
        return $columns;
    }

    public function custom_order_column_content( $column, $post_id ) {
        if ( 'spwp_ce_status' === $column ) {
            $order = wc_get_order( $post_id );
            if ( $order ) {
                $provisioned = $order->get_meta( 'spwp_ce_provisioned' );
                if ( 'true' === $provisioned ) {
                    echo '<mark class="order-status status-completed tips" data-tip="' . esc_attr__( 'CE Package Provisioned', 'spiritwp-connect' ) . '"><span>' . esc_html__( 'CE OK', 'spiritwp-connect' ) . '</span></mark>';
                } elseif ( 'false' === $provisioned ) {
                    echo '<mark class="order-status status-failed tips" data-tip="' . esc_attr__( 'CE Provisioning Failed', 'spiritwp-connect' ) . '"><span>' . esc_html__( 'CE Fail', 'spiritwp-connect' ) . '</span></mark>';
                } else {
                    echo '-';
                }
            }
        }
    }
}
