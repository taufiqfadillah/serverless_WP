<?php
/**
 * Frontend functionality for Shopify WP Connect
 * 
 * This file contains the frontend functionality for the Shopify WP Connect plugin.
 * It handles the enqueueing of plugin styles and scripts, as well as the addition
 * of a body wrapper for Shopify storefront elements.
 * 
 * @package Shopify_WP_Connect
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue frontend scripts and styles
 */
function shopify_connect_enqueue_frontend_assets() {
    // Add inline styles for navigation block
    $custom_css = '.wp-block-navigation__container { display: flex; }';
    wp_add_inline_style('shopify-product-card', $custom_css);
}
add_action('wp_enqueue_scripts', 'shopify_connect_enqueue_frontend_assets');

/**
 * Enqueue Shopify Storefront Elements script
 */
function shopify_connect_enqueue_storefront_elements() {
    wp_enqueue_script(
        'shopify-storefront-elements',
        'https://cdn.shopify.com/storefront/web-components.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'shopify_connect_enqueue_storefront_elements');


/**
 * Get user's country and language using WordPress native functions
 * 
 * @return array Array with 'country' and 'language' keys
 */
function shopify_connect_get_user_locale_info() {
    // Get the current locale (WordPress handles all the complexity)
    $locale = get_user_locale();
    
    // Parse locale to extract language and country
    $language = 'en'; // Default
    $country = 'US';  // Default
    
    if (strpos($locale, '_') !== false) {
        $parts = explode('_', $locale);
        if (isset($parts[0]) && strlen($parts[0]) === 2) {
            $language = strtolower($parts[0]);
        }
        if (isset($parts[1]) && strlen($parts[1]) === 2) {
            $country = strtoupper($parts[1]);
        }
    } else {
        // If no underscore, assume it's just a language code
        if (strlen($locale) === 2) {
            $language = strtolower($locale);
        }
    }
    
    // Allow override from plugin settings
            $user_country = get_option('shopify_country');
    if (!empty($user_country)) {
        $country = strtoupper(trim($user_country));
    }
    
            $user_language = get_option('shopify_language');
    if (!empty($user_language)) {
        $language = strtolower(trim($user_language));
    }
    
    return array(
        'country' => $country,
        'language' => $language
    );
}

/**
 * Add Shopify store wrapper - Works with both block and classic themes
 */
function add_body_wrapper() {
    // Global flag to ensure wrapper is only added once
    global $shopify_wrapper_added;
    
    // Check if wrapper has already been added
    if ($shopify_wrapper_added) {
        return;
    }
    
            $store_url = get_option('shopify_store_url');
        $api_key = get_option('shopify_api_key');
    
    // Don't output store wrapper if credentials are missing
    if (empty(trim($store_url)) || empty(trim($api_key))) {
        return;
    }

    // Get user's country and language using WordPress native functions
    $locale_info = shopify_connect_get_user_locale_info();
    $country = $locale_info['country'];
    $language = $locale_info['language'];
    
    // echo '<div class="shopify-wrapper">';
    echo '<shopify-store store-domain="' . esc_attr($store_url) . '" public-access-token="' . esc_attr($api_key) . '" country="' . esc_attr($country) . '" language="' . esc_attr($language) . '"></shopify-store>';
    
    // Mark as added
    $shopify_wrapper_added = true;
}

// Use wp_body_open for modern themes
add_action('wp_body_open', 'add_body_wrapper');

/**
 * Add Cart to closing body tag
 */
function close_body_wrapper() {
    echo '<shopify-cart id="cart"></shopify-cart>';
            shopify_include_template('modal.php', SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/modal.php');
    echo '<div id="product-modal-container"></div>';
}
add_action('wp_footer', 'close_body_wrapper', 999);




