<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="spwp-tickets-wrapper">
    <div class="spwp-tickets-header">
        <h2><?php esc_html_e( 'Support Tickets', 'spiritwp-connect' ); ?></h2>
        <a href="<?php echo esc_url( home_url( '/ce-login/?goto=tickets' ) ); ?>" class="spwp-btn spwp-btn-primary" target="_blank">
            <?php esc_html_e( 'Open New Ticket', 'spiritwp-connect' ); ?>
        </a>
    </div>

    <?php if ( empty( $tickets ) ) : ?>
        <p class="spwp-empty-state spwp-p-6"><?php esc_html_e( 'You have no support tickets.', 'spiritwp-connect' ); ?></p>
    <?php else : ?>
        <div class="spwp-table-responsive">
            <table class="spwp-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'ID', 'spiritwp-connect' ); ?></th>
                        <th><?php esc_html_e( 'Subject', 'spiritwp-connect' ); ?></th>
                        <th><?php esc_html_e( 'Status', 'spiritwp-connect' ); ?></th>
                        <th><?php esc_html_e( 'Last Updated', 'spiritwp-connect' ); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $tickets as $ticket ) : ?>
                        <tr>
                            <td>#<?php echo esc_html( $ticket['id'] ?? '' ); ?></td>
                            <td><strong><?php echo esc_html( $ticket['subject'] ?? 'Untitled' ); ?></strong></td>
                            <td>
                                <span class="spwp-badge spwp-badge-<?php echo sanitize_title( $ticket['status'] ?? 'open' ); ?>">
                                    <?php echo esc_html( $ticket['status'] ?? 'Open' ); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html( $ticket['last_reply'] ?? '' ); ?></td>
                            <td class="spwp-text-right">
                                <a href="<?php echo esc_url( home_url( '/ce-login/?goto=tickets' ) ); ?>" class="spwp-btn spwp-btn-sm spwp-btn-outline" target="_blank">
                                    <?php esc_html_e( 'View', 'spiritwp-connect' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
