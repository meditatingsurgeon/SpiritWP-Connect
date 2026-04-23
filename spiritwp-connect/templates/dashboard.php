<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Extract variables
// $hosting_packages, $webinar_packages, $invoices, $tickets
?>

<div class="spwp-wrap spwp-dashboard-wrapper">
    <div class="spwp-dashboard-header">
        <h2 class="spwp-section-title"><?php esc_html_e( 'Client Dashboard', 'spiritwp-connect' ); ?></h2>
        <div class="spwp-quick-links">
            <a href="/ce-login/?goto=store" class="spwp-btn spwp-btn-outline spwp-btn-sm"><?php esc_html_e( 'Add Service', 'spiritwp-connect' ); ?></a>
            <a href="/ce-login/?goto=support" class="spwp-btn spwp-btn-primary spwp-btn-sm"><?php esc_html_e( 'Get Support', 'spiritwp-connect' ); ?></a>
        </div>
    </div>

    <!-- BUG-007 Fix: Hosting Services Panel -->
    <div class="spwp-panel" style="margin-bottom: var(--vf-space-xl);">
        <div class="spwp-panel-header">
            <h3><?php esc_html_e( 'Hosting Services', 'spiritwp-connect' ); ?></h3>
            <a href="/ce-login/?goto=packages" class="spwp-link"><?php esc_html_e( 'Manage All', 'spiritwp-connect' ); ?> &rarr;</a>
        </div>
        <?php if ( empty( $hosting_packages ) ) : ?>
            <div class="spwp-empty-state">
                <p><?php esc_html_e( 'You have no active hosting services.', 'spiritwp-connect' ); ?></p>
                <a href="/ce-login/?goto=store" class="spwp-btn spwp-btn-primary spwp-btn-sm" style="margin-top: var(--vf-space-sm);"><?php esc_html_e( 'Browse Hosting Plans', 'spiritwp-connect' ); ?></a>
            </div>
        <?php else : ?>
            <ul class="spwp-list">
                <?php foreach ( $hosting_packages as $package ) : ?>
                    <li class="spwp-list-item spwp-package-card">
                        <div class="spwp-list-info">
                            <span class="spwp-invoice-id"><?php echo esc_html( $package['product_name'] ?? $package['name'] ); ?></span>
                            <span class="spwp-invoice-date"><?php echo esc_html( $package['domain'] ?? '' ); ?></span>
                        </div>
                        <div class="spwp-list-actions">
                            <span class="spwp-badge spwp-badge-<?php echo esc_attr( strtolower( $package['status'] ) ); ?>"><?php echo esc_html( $package['status'] ); ?></span>
                            <a href="/ce-login/?goto=packages" class="spwp-btn spwp-btn-outline spwp-btn-sm"><?php esc_html_e( 'Manage', 'spiritwp-connect' ); ?></a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- BUG-007 Fix: Spirit Webinars Panel -->
    <div class="spwp-panel" style="margin-bottom: var(--vf-space-xl);">
        <div class="spwp-panel-header">
            <h3><?php esc_html_e( 'Spirit Webinars', 'spiritwp-connect' ); ?></h3>
            <a href="https://spirit.ws" target="_blank" class="spwp-link"><?php esc_html_e( 'Access Platform', 'spiritwp-connect' ); ?> &rarr;</a>
        </div>
        <?php if ( empty( $webinar_packages ) ) : ?>
            <div class="spwp-empty-state">
                <p><?php esc_html_e( 'No active Spirit Webinars subscription.', 'spiritwp-connect' ); ?></p>
                <a href="/ce-login/?goto=store" class="spwp-btn spwp-btn-primary spwp-btn-sm" style="margin-top: var(--vf-space-sm);"><?php esc_html_e( 'Browse Webinar Plans', 'spiritwp-connect' ); ?></a>
            </div>
        <?php else : ?>
            <ul class="spwp-list">
                <?php foreach ( $webinar_packages as $package ) : ?>
                    <li class="spwp-list-item spwp-package-card">
                        <div class="spwp-list-info">
                            <span class="spwp-invoice-id"><?php echo esc_html( $package['product_name'] ?? $package['name'] ); ?></span>
                            <span class="spwp-invoice-date"><?php esc_html_e( 'Subscription Active', 'spiritwp-connect' ); ?></span>
                        </div>
                        <div class="spwp-list-actions">
                            <span class="spwp-badge spwp-badge-<?php echo esc_attr( strtolower( $package['status'] ) ); ?>"><?php echo esc_html( $package['status'] ); ?></span>
                            <a href="/ce-login/?goto=packages" class="spwp-btn spwp-btn-outline spwp-btn-sm"><?php esc_html_e( 'Manage Billing', 'spiritwp-connect' ); ?></a>
                            <a href="https://spirit.ws" target="_blank" class="spwp-btn spwp-btn-primary spwp-btn-sm"><?php esc_html_e( 'Launch', 'spiritwp-connect' ); ?></a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="spwp-dashboard-grid">
        <div class="spwp-panel">
            <div class="spwp-panel-header">
                <h3><?php esc_html_e( 'Recent Invoices', 'spiritwp-connect' ); ?></h3>
                <a href="/ce-login/?goto=invoices" class="spwp-link"><?php esc_html_e( 'View All', 'spiritwp-connect' ); ?></a>
            </div>
            
            <?php if ( empty( $invoices ) ) : ?>
                <div class="spwp-empty-state"><?php esc_html_e( 'No recent invoices found.', 'spiritwp-connect' ); ?></div>
            <?php else : ?>
                <ul class="spwp-list">
                    <?php foreach ( $invoices as $invoice ) : ?>
                        <li class="spwp-list-item">
                            <div class="spwp-list-info">
                                <span class="spwp-invoice-id">#<?php echo esc_html( $invoice['id'] ); ?></span>
                                <span class="spwp-invoice-date"><?php echo esc_html( $invoice['date'] ?? '' ); ?></span>
                            </div>
                            <div class="spwp-list-actions">
                                <span class="spwp-badge spwp-badge-<?php echo esc_attr( $invoice['status'] == 1 ? 'paid' : 'unpaid' ); ?>">
                                    <?php echo $invoice['status'] == 1 ? esc_html__( 'Paid', 'spiritwp-connect' ) : esc_html__( 'Unpaid', 'spiritwp-connect' ); ?>
                                </span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="spwp-panel">
            <div class="spwp-panel-header">
                <h3><?php esc_html_e( 'Open Support Tickets', 'spiritwp-connect' ); ?></h3>
                <a href="/ce-login/?goto=tickets" class="spwp-link"><?php esc_html_e( 'View All', 'spiritwp-connect' ); ?></a>
            </div>
            
            <?php if ( empty( $tickets ) ) : ?>
                <div class="spwp-empty-state"><?php esc_html_e( 'No open tickets.', 'spiritwp-connect' ); ?></div>
            <?php else : ?>
                <ul class="spwp-list">
                    <?php foreach ( $tickets as $ticket ) : ?>
                        <li class="spwp-list-item">
                            <div class="spwp-list-info">
                                <span class="spwp-ticket-subject"><?php echo esc_html( $ticket['subject'] ); ?></span>
                                <span class="spwp-ticket-meta">Ticket #<?php echo esc_html( $ticket['id'] ); ?></span>
                            </div>
                            <div class="spwp-list-actions">
                                <a href="/ce-login/?goto=tickets" class="spwp-btn spwp-btn-outline spwp-btn-sm"><?php esc_html_e( 'View', 'spiritwp-connect' ); ?></a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
