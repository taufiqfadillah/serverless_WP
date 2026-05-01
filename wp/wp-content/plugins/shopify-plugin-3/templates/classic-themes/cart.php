<?php
/**
 * Classic Theme Cart Template for Shopify WP Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); ?>

<div class="shopify-cart-content">
    <?php 
    // Include the cart page component
    include SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/shopify-cart-page.php';
    ?>
</div>

<?php get_footer(); ?>