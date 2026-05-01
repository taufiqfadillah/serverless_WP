<?php
/**
 * Classic Theme Product Template for Shopify WP Connect
 * 
 * This template is used for classic themes to display Shopify products.
 * It provides a standard WordPress layout with header and footer,
 * while including the Shopify product content.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="shopify-product-content">
            <?php 
            // Include the product template content
            include SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/shopify-pdp.php';
            ?>
        </div>
    </main>
</div>

<?php get_footer(); ?> 