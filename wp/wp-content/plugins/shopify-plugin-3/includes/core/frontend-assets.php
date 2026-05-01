<?php
/**
 * Frontend Assets Management
 * 
 * Handles all frontend script and style enqueues for the Shopify for WordPress plugin.
 * This ensures proper WordPress enqueue standards are followed.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue frontend component assets
 */
function shopify_enqueue_frontend_component_assets() {
    // Enqueue product card styles
    wp_enqueue_style(
        'shopify-product-card',
        plugins_url('includes/components/frontend/styles/product-card.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
    );

    // Enqueue modal styles
    wp_enqueue_style(
        'shopify-modal',
        plugins_url('includes/components/frontend/styles/modal.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
    );

    // Enqueue collection styles
    wp_enqueue_style(
        'shopify-collection',
        plugins_url('includes/components/frontend/styles/collection.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
    );

    // Enqueue cart styles
    wp_enqueue_style(
        'shopify-cart',
        plugins_url('includes/components/frontend/styles/cart.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
    );

    // Enqueue cart page styles (only on cart page)
    if (shopify_wp_connect_is_cart_page()) {
        wp_enqueue_style(
            'shopify-cart-page',
            plugins_url('includes/components/frontend/styles/cart-page.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('shopify-cart'),
            defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
        );
    }

    // Enqueue single product styles
    wp_enqueue_style(
        'shopify-single-product',
        plugins_url('includes/components/frontend/styles/single-product.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
    );

    // Enqueue variant selector styles
    wp_enqueue_style(
        'shopify-variant-select',
        plugins_url('includes/components/frontend/styles/variant-select.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
    );

    // Enqueue PDP JavaScript
    wp_enqueue_script(
        'shopify-pdp',
        plugins_url('assets/js/shopify-pdp.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(), // No dependencies
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0',
        true // Load in footer
    );

    // Enqueue Pagination JavaScript
    wp_enqueue_script(
        'shopify-pagination',
        plugins_url('assets/js/shopify-pagination.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(), // No dependencies
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0',
        true // Load in footer
    );

    // Enqueue Modal JavaScript
    wp_enqueue_script(
        'shopify-modal',
        plugins_url('assets/js/shopify-modal.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(), // No dependencies
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0',
        true // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'shopify_enqueue_frontend_component_assets');
