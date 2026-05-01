<?php
/**
 * Cart Page Component for Shopify WP Connect
 * 
 * This file displays the full cart page at /cart using Shopify web components.
 * It provides an inline cart experience that complements the existing cart modal.
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$store_url = get_option('shopify_store_url');
$api_key = get_option('shopify_api_key');

/**
 * Enqueue cart page scripts
 */
function shopify_cart_page_enqueue_scripts() {
    // Enqueue Shopify web components
    wp_enqueue_script(
        'shopify-web-components',
        'https://cdn.shopify.com/storefront/web-components.js',
        array(),
        '1.0.0',
        true
    );
    
    // Enqueue cart page initialization script
    wp_enqueue_script(
        'shopify-cart-page',
        plugins_url('assets/js/shopify-cart-page.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array('shopify-web-components'),
        defined('shopify_VERSION') ? shopify_VERSION : '1.0.0',
        true
    );
}

// Enqueue scripts immediately for cart page
shopify_cart_page_enqueue_scripts();

// Check for theme override for cart page
$theme_cart_template_path = get_stylesheet_directory() . '/shopify/cart-page.php';
if (file_exists($theme_cart_template_path)) {
    // Set up variables that the theme template might need
    $cart_data = array(
        'store_url' => $store_url,
        'api_key' => $api_key
    );
    
    // Include the theme cart template
    include $theme_cart_template_path;
    return;
}
?>

<div class="shopify-cart-page">
    <div class="cart-page-content">
        <!-- Shopify cart component displayed inline -->
        <shopify-cart id="cart-page-display" class="cart-page-display"></shopify-cart>
    </div>

</div>