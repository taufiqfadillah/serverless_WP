<?php
/**
 * Query Variables Handler for Shopify WP Connect
 * 
 * This file handles the registration and processing of custom query variables
 * for Shopify products and collections. It provides functionality to:
 * - Register custom query variables for Shopify products and collections
 * - Process Shopify requests and set appropriate query variables
 * - Handle the logic for determining if we're on a Shopify product or collection page
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom query variables for Shopify
 */
function shopify_wp_connect_register_query_vars($vars) {
    // Only add product-related vars if product rewrites are not disabled
    if (!defined('SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES') || !SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES) {
        $vars[] = 'shopify_product_handle';
        $vars[] = 'is_shopify_product_template';
    }
    $vars[] = 'shopify_collection_handle';
    $vars[] = 'is_shopify_collection_template';
    $vars[] = 'shopify_cart_page';
    $vars[] = 'is_shopify_cart_template';
    $vars[] = 'error'; // For handling 404 errors
    return $vars;
}

/**
 * Process Shopify requests and set appropriate query variables
 */
function shopify_wp_connect_handle_request($wp) {
    if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
        error_log('Query vars: handle_shopify_request called');
        error_log('Query vars: query_vars: ' . print_r($wp->query_vars, true));
    }
    
    // Only handle product requests if product rewrites are not disabled
    if (!defined('SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES') || !SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES) {
        if (isset($wp->query_vars['shopify_product_handle'])) {
            $wp->query_vars['is_shopify_product_template'] = true;
            if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
                error_log('Query vars: Set is_shopify_product_template to true');
            }
        }
    }
    
    if (isset($wp->query_vars['shopify_collection_handle'])) {
        $wp->query_vars['is_shopify_collection_template'] = true;
        if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
            error_log('Query vars: Set is_shopify_collection_template to true');
        }
    }
    
    if (isset($wp->query_vars['shopify_cart_page'])) {
        $wp->query_vars['is_shopify_cart_template'] = true;
        if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
            error_log('Query vars: Set is_shopify_cart_template to true');
        }
    }
    
    if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
        error_log('Query vars: Final query_vars: ' . print_r($wp->query_vars, true));
    }
}

/**
 * Check if we're currently on a Shopify product page
 */
function shopify_wp_connect_is_product_page() {
    return get_query_var('is_shopify_product_template') && !empty(get_query_var('shopify_product_handle'));
}

/**
 * Check if we're currently on a Shopify collection page
 */
function shopify_wp_connect_is_collection_page() {
    return get_query_var('is_shopify_collection_template') && !empty(get_query_var('shopify_collection_handle'));
}

/**
 * Get the current Shopify product handle
 */
function shopify_wp_connect_get_product_handle() {
    return get_query_var('shopify_product_handle');
}

/**
 * Get the current Shopify collection handle
 */
function shopify_wp_connect_get_collection_handle() {
    return get_query_var('shopify_collection_handle');
}

/**
 * Check if we're currently on a Shopify cart page
 */
function shopify_wp_connect_is_cart_page() {
    return get_query_var('is_shopify_cart_template') && get_query_var('shopify_cart_page');
}

// Hook into WordPress
add_filter('query_vars', 'shopify_wp_connect_register_query_vars');
add_action('parse_request', 'shopify_wp_connect_handle_request');
