<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="spwp-kb-wrapper">
    <div class="spwp-kb-header">
        <h2><?php esc_html_e( 'Knowledge Base', 'spiritwp-connect' ); ?></h2>
    </div>

    <?php if ( empty( $articles ) ) : ?>
        <p class="spwp-empty-state"><?php esc_html_e( 'No articles found.', 'spiritwp-connect' ); ?></p>
    <?php else : ?>
        <div class="spwp-kb-grid">
            <?php foreach ( $articles as $article ) : ?>
                <div class="spwp-kb-card">
                    <h3 class="spwp-kb-title">
                        <a href="<?php echo esc_url( home_url( '/ce-login/?goto=knowledgebase' ) ); ?>" target="_blank">
                            <?php echo esc_html( $article['title'] ?? 'Untitled' ); ?>
                        </a>
                    </h3>
                    <div class="spwp-kb-excerpt">
                        <?php 
                        // Some articles might have excerpts, if not show read more
                        if ( !empty($article['excerpt']) ) {
                            echo wp_kses_post( $article['excerpt'] ); 
                        }
                        ?>
                    </div>
                    <a href="<?php echo esc_url( home_url( '/ce-login/?goto=knowledgebase' ) ); ?>" class="spwp-kb-read-more" target="_blank">
                        <?php esc_html_e( 'Read Article &rarr;', 'spiritwp-connect' ); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
