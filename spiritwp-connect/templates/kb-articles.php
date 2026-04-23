<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Extract arguments wrapper
$kb_tag = isset( $atts['tag'] ) ? sanitize_text_field( $atts['tag'] ) : null;

$api = \SpiritWP\Connect\Core\Plugin::get_instance()->api;
$kb_res = $api->get_kb_articles( $kb_tag );
$articles = ( ! is_wp_error( $kb_res ) && isset( $kb_res['data'] ) ) ? $kb_res['data'] : [];
?>

<div class="spwp-wrap spwp-kb-wrapper">
    <div class="spwp-kb-header">
        <h2 class="spwp-section-title"><?php esc_html_e( 'Knowledge Base', 'spiritwp-connect' ); ?></h2>
        <?php if ( $kb_tag ) : ?>
            <span class="spwp-badge spwp-badge-active"><?php echo esc_html( 'Tag: ' . $kb_tag ); ?></span>
        <?php endif; ?>
    </div>

    <?php if ( empty( $articles ) ) : ?>
        <div class="spwp-panel">
            <div class="spwp-empty-state"><?php esc_html_e( 'No knowledge base articles found.', 'spiritwp-connect' ); ?></div>
        </div>
    <?php else : ?>
        <div class="spwp-kb-grid">
            <?php foreach ( $articles as $article ) : ?>
                <div class="spwp-kb-card">
                    <h3 class="spwp-kb-title"><?php echo esc_html( $article['title'] ?? '' ); ?></h3>
                    <div class="spwp-kb-excerpt">
                        <?php 
                        $excerpt = strip_tags( $article['content'] ?? '' );
                        echo esc_html( wp_trim_words( $excerpt, 20, '...' ) ); 
                        ?>
                    </div>
                    <div style="margin-top: var(--vf-space-md);">
                        <a href="/ce-login/?goto=knowledgebase" class="spwp-link"><?php esc_html_e( 'Read Article', 'spiritwp-connect' ); ?> &rarr;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
