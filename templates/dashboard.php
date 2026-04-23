<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="spwp-dashboard-wrapper">
    <div class="spwp-dashboard-header">
        <div class="spwp-dashboard-greeting">
            <h2 class="spwp-section-title"><?php printf( esc_html__( 'Welcome, %s', 'spiritwp-connect' ), esc_html( $user->display_name ) ); ?></h2>
            <p class="spwp-subtitle"><?php esc_html_e( 'Your active services are shown below.', 'spiritwp-connect' ); ?></p>
        </div>
        <div class="spwp-dashboard-actions">
            <a href="<?php echo esc_url( $sso_portal_url ); ?>" class="spwp-btn spwp-btn-primary" target="_blank"><?php esc_html_e( 'Billing Portal', 'spiritwp-connect' ); ?> &rarr;</a>
            <a href="<?php echo esc_url( $sso_store_url ); ?>" class="spwp-btn spwp-btn-outline" target="_blank"><?php esc_html_e( 'Add Services', 'spiritwp-connect' ); ?></a>
        </div>
    </div>

    <div class="spwp-panel spwp-panel-full">
        <div class="spwp-panel-header"><h3><?php esc_html_e( 'Hosting Services', 'spiritwp-connect' ); ?></h3></div>
        <div class="spwp-panel-body">
            <?php if ( empty( $hosting_packages ) ) : ?>
                <div class="spwp-empty-state spwp-empty-with-cta">
                    <p><?php esc_html_e( 'You have no active hosting services.', 'spiritwp-connect' ); ?></p>
                    <a href="<?php echo esc_url( $sso_store_url ); ?>" class="spwp-btn spwp-btn-sm" target="_blank"><?php esc_html_e( 'Browse Hosting Plans', 'spiritwp-connect' ); ?></a>
                </div>
            <?php else : ?>
                <div class="spwp-packages-grid">
                    <?php foreach ( $hosting_packages as $pkg ) :
                        $pkg_name   = esc_html( $pkg['product_name'] ?? $pkg['name'] ?? __( 'Hosting Plan', 'spiritwp-connect' ) );
                        $pkg_status = strtolower( $pkg['status'] ?? 'active' );
                        $pkg_domain = esc_html( $pkg['domain'] ?? '' );
                        $pkg_due    = esc_html( $pkg['nextduedate'] ?? $pkg['next_due_date'] ?? '' );
                    ?>
                        <div class="spwp-package-card spwp-status-<?php echo esc_attr( $pkg_status ); ?>">
                            <div class="spwp-package-header">
                                <h4 class="spwp-package-name"><?php echo $pkg_name; ?></h4>
                                <span class="spwp-badge spwp-badge-<?php echo esc_attr( $pkg_status ); ?>"><?php echo esc_html( ucfirst( $pkg_status ) ); ?></span>
                            </div>
                            <?php if ( $pkg_domain ) : ?><p class="spwp-package-domain"><?php echo $pkg_domain; ?></p><?php endif; ?>
                            <?php if ( $pkg_due ) : ?><p class="spwp-package-due"><?php printf( esc_html__( 'Next renewal: %s', 'spiritwp-connect' ), $pkg_due ); ?></p><?php endif; ?>
                            <div class="spwp-package-actions"><a href="<?php echo esc_url( $sso_portal_url ); ?>" class="spwp-link" target="_blank"><?php esc_html_e( 'Manage', 'spiritwp-connect' ); ?> &rarr;</a></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="spwp-panel spwp-panel-full">
        <div class="spwp-panel-header">
            <h3><?php esc_html_e( 'Spirit Webinars', 'spiritwp-connect' ); ?></h3>
            <a href="https://spirit.ws" class="spwp-btn spwp-btn-sm" target="_blank"><?php esc_html_e( 'Open Platform', 'spiritwp-connect' ); ?></a>
        </div>
        <div class="spwp-panel-body">
            <?php if ( empty( $webinar_packages ) ) : ?>
                <div class="spwp-empty-state spwp-empty-with-cta">
                    <p><?php esc_html_e( 'No active Spirit Webinars subscription.', 'spiritwp-connect' ); ?></p>
                    <a href="<?php echo esc_url( $sso_store_url ); ?>" class="spwp-btn spwp-btn-sm" target="_blank"><?php esc_html_e( 'Browse Webinar Plans', 'spiritwp-connect' ); ?></a>
                </div>
            <?php else : ?>
                <div class="spwp-packages-grid">
                    <?php foreach ( $webinar_packages as $pkg ) :
                        $pkg_name   = esc_html( $pkg['product_name'] ?? $pkg['name'] ?? __( 'Webinar Plan', 'spiritwp-connect' ) );
                        $pkg_status = strtolower( $pkg['status'] ?? 'active' );
                        $pkg_due    = esc_html( $pkg['nextduedate'] ?? $pkg['next_due_date'] ?? '' );
                    ?>
                        <div class="spwp-package-card spwp-status-<?php echo esc_attr( $pkg_status ); ?>">
                            <div class="spwp-package-header">
                                <h4 class="spwp-package-name"><?php echo $pkg_name; ?></h4>
                                <span class="spwp-badge spwp-badge-<?php echo esc_attr( $pkg_status ); ?>"><?php echo esc_html( ucfirst( $pkg_status ) ); ?></span>
                            </div>
                            <?php if ( $pkg_due ) : ?><p class="spwp-package-due"><?php printf( esc_html__( 'Next renewal: %s', 'spiritwp-connect' ), $pkg_due ); ?></p><?php endif; ?>
                            <div class="spwp-package-actions">
                                <a href="https://spirit.ws" class="spwp-link" target="_blank"><?php esc_html_e( 'Access Platform', 'spiritwp-connect' ); ?> &rarr;</a>
                                <a href="<?php echo esc_url( $sso_portal_url ); ?>" class="spwp-link" target="_blank"><?php esc_html_e( 'Manage Billing', 'spiritwp-connect' ); ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="spwp-quick-links">
        <a href="<?php echo esc_url( $sso_invoices_url ); ?>" class="spwp-quick-link" target="_blank"><span class="spwp-quick-link-icon">&#128190;</span><?php esc_html_e( 'Invoices', 'spiritwp-connect' ); ?></a>
        <a href="<?php echo esc_url( $sso_support_url ); ?>" class="spwp-quick-link" target="_blank"><span class="spwp-quick-link-icon">&#127381;</span><?php esc_html_e( 'Support', 'spiritwp-connect' ); ?></a>
        <a href="<?php echo esc_url( $sso_store_url ); ?>" class="spwp-quick-link" target="_blank"><span class="spwp-quick-link-icon">&#43;</span><?php esc_html_e( 'Add Service', 'spiritwp-connect' ); ?></a>
        <a href="https://spirit.ws" class="spwp-quick-link" target="_blank"><span class="spwp-quick-link-icon">&#127916;</span><?php esc_html_e( 'Spirit Webinars', 'spiritwp-connect' ); ?></a>
    </div>
</div>