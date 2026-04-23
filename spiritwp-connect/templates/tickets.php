<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Extract variables: $tickets

$api = \SpiritWP\Connect\Core\Plugin::get_instance()->api;
$user = wp_get_current_user();
$ce_userid = get_user_meta( $user->ID, 'spwp_ce_userid', true );

if ( empty( $ce_userid ) ) {
    echo '<div class="spwp-wrap spwp-tickets-wrapper"><div class="spwp-panel"><div class="spwp-empty-state">' . esc_html__( 'Please log in to view support tickets.', 'spiritwp-connect' ) . '</div></div></div>';
    return;
}

$tickets_res = $api->get_tickets( $ce_userid, 'all' );
$tickets = ( ! is_wp_error( $tickets_res ) && isset( $tickets_res['data'] ) ) ? $tickets_res['data'] : [];
?>

<div class="spwp-wrap spwp-tickets-wrapper">
    <div class="spwp-tickets-header">
        <h2 class="spwp-section-title"><?php esc_html_e( 'Support Centre', 'spiritwp-connect' ); ?></h2>
        <div class="spwp-quick-links">
            <a href="/ce-login/?goto=support" class="spwp-btn spwp-btn-primary spwp-btn-sm"><?php esc_html_e( 'Submit Ticket', 'spiritwp-connect' ); ?></a>
            <a href="/ce-login/?goto=knowledgebase" class="spwp-btn spwp-btn-outline spwp-btn-sm"><?php esc_html_e( 'Knowledge Base', 'spiritwp-connect' ); ?></a>
        </div>
    </div>

    <div class="spwp-panel">
        <?php if ( empty( $tickets ) ) : ?>
            <div class="spwp-empty-state">
                <p><?php esc_html_e( 'No tickets found.', 'spiritwp-connect' ); ?></p>
            </div>
        <?php else : ?>
            <div class="spwp-table-responsive">
                <table class="spwp-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Subject', 'spiritwp-connect' ); ?></th>
                            <th><?php esc_html_e( 'Status', 'spiritwp-connect' ); ?></th>
                            <th><?php esc_html_e( 'Last Updated', 'spiritwp-connect' ); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $tickets as $ticket ) : ?>
                            <tr>
                                <td>
                                    <span class="spwp-ticket-subject"><?php echo esc_html( $ticket['subject'] ); ?></span><br>
                                    <small class="spwp-ticket-meta">Ticket #<?php echo esc_html( $ticket['id'] ); ?></small>
                                </td>
                                <td>
                                    <span class="spwp-badge spwp-badge-<?php echo esc_attr( strtolower( $ticket['status'] ) ); ?>">
                                        <?php echo esc_html( $ticket['status'] ); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html( $ticket['last_update'] ?? '' ); ?></td>
                                <td style="text-align: right;">
                                    <a href="/ce-login/?goto=tickets" class="spwp-btn spwp-btn-outline spwp-btn-sm"><?php esc_html_e( 'View', 'spiritwp-connect' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
