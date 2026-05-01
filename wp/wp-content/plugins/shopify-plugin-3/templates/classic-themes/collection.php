<?php
/**
 * Classic Theme Collection Template for Shopify
 * 
 * This template is used for classic themes to display Shopify collections.
 * It provides a standard WordPress layout with header and footer,
 * while including the Shopify collection content.
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="shopify-collection-content">
            <?php 
            // Include the collection template content
            include SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/shopify-collection.php';
            ?>
        </div>
    </main>
</div>

<?php get_footer(); ?> 