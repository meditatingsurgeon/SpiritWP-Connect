<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$api = \SpiritWP\Connect\Core\Plugin::get_instance()->api;
$user = wp_get_current_user();
?>
<div class="spwp-dashboard-wrapper">
    <div class="spwp-dashboard-header">
        <h2 class="spwp-section-title"><?php esc_html_e( 'My Services & Billing', 'spiritwp-connect' ); ?></h2>
        <a href="<?php echo esc_url( home_url( '/ce-login/' ) ); ?>" class="spwp-btn spwp-btn-primary" target="_blank">
            <?php esc_html_e( 'Manage Account', 'spiritwp-connect' ); ?>
        </a>
    </div>

    <div class="spwp-dashboard-grid">
        <!-- Invoices Panel -->
        <div class="spwp-panel">
            <div class="spwp-panel-header">
                <h3><?php esc_html_e( 'Recent Invoices', 'spiritwp-connect' ); ?></h3>
            </div>
            <div class="spwp-panel-body">
                <?php if ( empty( $invoices ) ) : ?>
                    <p class="spwp-empty-state"><?php esc_html_e( 'No recent invoices found.', 'spiritwp-connect' ); ?></p>
                <?php else : ?>
                    <ul class="spwp-list">
                        <?php foreach ( array_slice( $invoices, 0, 5 ) as $invoice ) : ?>
                            <li class="spwp-list-item">
                                <div class="spwp-list-info">
                                    <span class="spwp-invoice-id">#<?php echo esc_html( $invoice['id'] ?? '' ); ?></span>
                                    <span class="spwp-invoice-date"><?php echo esc_html( $invoice['date'] ?? '' ); ?></span>
                                </div>
                                <div class="spwp-list-actions">
                                    <span class="spwp-badge spwp-badge-<?php echo esc_attr( strtolower( $invoice['status'] ?? '' ) ); ?>">
                                        <?php echo esc_html( $invoice['status'] ?? 'Unknown' ); ?>
                                    </span>
                                    <a href="<?php echo esc_url( home_url( '/ce-login/?goto=invoices' ) ); ?>" class="spwp-link" target="_blank">
                                        <?php echo esc_html( $invoice['total'] ?? '' ); ?> &rarr;
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tickets Panel -->
        <div class="spwp-panel">
            <div class="spwp-panel-header">
                <h3><?php esc_html_e( 'Open Support Tickets', 'spiritwp-connect' ); ?></h3>
                <a href="<?php echo esc_url( home_url( '/ce-login/?goto=tickets' ) ); ?>" class="spwp-btn spwp-btn-sm" target="_blank"><?php esc_html_e( 'New Ticket', 'spiritwp-connect' ); ?></a>
            </div>
            <div class="spwp-panel-body">
                <?php if ( empty( $tickets ) ) : ?>
                    <p class="spwp-empty-state"><?php esc_html_e( 'No open tickets.', 'spiritwp-connect' ); ?></p>
                <?php else : ?>
                    <ul class="spwp-list">
                        <?php foreach ( array_slice( $tickets, 0, 5 ) as $ticket ) : ?>
                            <li class="spwp-list-item">
                                <div class="spwp-list-info">
                                    <strong class="spwp-ticket-subject"><?php echo esc_html( $ticket['subject'] ?? 'Untitled' ); ?></strong>
                                    <span class="spwp-ticket-meta"><?php esc_html_e('Last activity:', 'spiritwp-connect'); ?> <?php echo esc_html( $ticket['last_reply'] ?? '' ); ?></span>
                                </div>
                                <div class="spwp-list-actions">
                                    <span class="spwp-badge spwp-badge-ticket spwp-badge-<?php echo sanitize_title( $ticket['status'] ?? '' ); ?>">
                                        <?php echo esc_html( $ticket['status'] ?? 'Open' ); ?>
                                    </span>
                                    <a href="<?php echo esc_url( home_url( '/ce-login/?goto=tickets' ) ); ?>" class="spwp-link" target="_blank">
                                        <?php esc_html_e( 'View', 'spiritwp-connect' ); ?>
                                    </a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
