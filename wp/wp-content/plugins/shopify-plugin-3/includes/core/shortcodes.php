<?php
/**
 * Shortcodes for Shopify WP Connect
 * 
 * This file contains shortcode functions that enable PHP injection into WordPress block templates.
 * These shortcodes are used in the block templates (single-shopify-product.html and single-shopify-collection.html)
 * to inject dynamic PHP content for displaying Shopify products and collections within the WordPress block system.
 * 
 * @package Shopify_WP_Connect
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the Shopify PDP template shortcode
 */
function shopify_connect_include_pdp_template_shortcode($atts) {
    $php_file_path = SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/shopify-pdp.php';

    if (file_exists($php_file_path)) {
        ob_start();
        include($php_file_path);
        return ob_get_clean();
    }
    return '';
}
add_shortcode('shopify_pdp_template', 'shopify_connect_include_pdp_template_shortcode');

/**
 * Register the Shopify Collection template shortcode
 */
function shopify_connect_include_collection_template_shortcode($atts) {
    $php_file_path = SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/shopify-collection.php';

    if (file_exists($php_file_path)) {
        ob_start();
        include($php_file_path);
        return ob_get_clean();
    }
    return '';
}
add_shortcode('shopify_collection_template', 'shopify_connect_include_collection_template_shortcode');

/**
 * Register the Shopify Cart Page template shortcode
 */
function shopify_connect_include_cart_page_template_shortcode($atts) {
    $php_file_path = SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/shopify-cart-page.php';

    if (file_exists($php_file_path)) {
        ob_start();
        include($php_file_path);
        return ob_get_clean();
    }
    return '';
}
add_shortcode('shopify_cart_page_template', 'shopify_connect_include_cart_page_template_shortcode');