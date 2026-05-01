<?php
/**
 * Rewrite rules for Shopify WP Connect
 * 
 * This file handles URL rewriting for Shopify products and collections within WordPress.
 * It creates custom URL structures that map to Shopify product and collection pages,
 * allowing for clean, SEO-friendly URLs like:
 * - /products/product-handle
 * - /collections/collection-handle
 * 
 * The rewrite rules are registered on WordPress initialization and are flushed
 * when the plugin is activated or deactivated to ensure proper URL handling.
 * 
 * @package Shopify_WP_Connect
 */


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all rewrite rules for the plugin
 */
function shopify_connect_get_rewrite_rules() {
    $rules = array();
    
    // Check if rewrites are enabled globally (backwards compatibility)
    $global_rewrites_enabled = get_option('shopify_enable_rewrites', true);
    
    // Check individual rewrite settings
    $product_rewrites_enabled = get_option('shopify_enable_product_rewrites', true);
    $collection_rewrites_enabled = get_option('shopify_enable_collection_rewrites', true);
    
    // Add product rewrites if both global and product-specific rewrites are enabled
    if ($global_rewrites_enabled && $product_rewrites_enabled) {
        // Only add product rewrites if they're not disabled by constant
        if (!defined('SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES') || !SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES) {
            $rules['^products/([^/]+)/?$'] = 'index.php?shopify_product_handle=$matches[1]&is_shopify_product_template=true';
        } else {
            // When product rewrites are disabled, explicitly return 404 for product URLs
            $rules['^products/([^/]+)/?$'] = 'index.php?error=404';
        }
    }
    
    // Add collection rewrites if both global and collection-specific rewrites are enabled
    if ($global_rewrites_enabled && $collection_rewrites_enabled) {
        $rules['^collections/([^/]+)/?$'] = 'index.php?shopify_collection_handle=$matches[1]&is_shopify_collection_template=true';
    }
    
    // Add cart rewrite - always enabled regardless of other rewrite settings
    $rules['^cart/?$'] = 'index.php?shopify_cart_page=true&is_shopify_cart_template=true';
    
    return $rules;
}

/**
 * Add our rewrite rules to WordPress
 */
function shopify_connect_add_rewrite_rules($rules) {
    return shopify_connect_get_rewrite_rules() + $rules;
}
add_filter('rewrite_rules_array', 'shopify_connect_add_rewrite_rules');

/**
 * Flush rewrite rules when the enable_rewrites option changes
 */
function shopify_connect_flush_rewrites_on_option_change($old_value, $new_value) {
    if ($old_value !== $new_value) {
        // Force a complete rewrite rules flush
        global $wp_rewrite;
        $wp_rewrite->flush_rules(true);
    }
}
add_action('update_option_shopify_enable_rewrites', 'shopify_connect_flush_rewrites_on_option_change', 10, 2);

/**
 * Flush rewrite rules when the product rewrites option changes
 */
function shopify_connect_flush_rewrites_on_product_option_change($old_value, $new_value) {
    if ($old_value !== $new_value) {
        // Force a complete rewrite rules flush
        global $wp_rewrite;
        $wp_rewrite->flush_rules(true);
        error_log('Shopify Rewrite Rules - Product rewrites option changed, flushing rules');
    }
}
add_action('update_option_shopify_enable_product_rewrites', 'shopify_connect_flush_rewrites_on_product_option_change', 10, 2);

/**
 * Flush rewrite rules when the collection rewrites option changes
 */
function shopify_connect_flush_rewrites_on_collection_option_change($old_value, $new_value) {
    if ($old_value !== $new_value) {
        // Force a complete rewrite rules flush
        global $wp_rewrite;
        $wp_rewrite->flush_rules(true);
        error_log('Shopify Rewrite Rules - Collection rewrites option changed, flushing rules');
    }
}
add_action('update_option_shopify_enable_collection_rewrites', 'shopify_connect_flush_rewrites_on_collection_option_change', 10, 2);

/**
 * Flush rewrite rules on plugin activation
 */
function shopify_connect_activation() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules(true);
}
register_activation_hook(SHOPIFY_PLUGIN_DIR . 'shopify.php', 'shopify_connect_activation');

/**
 * Flush rewrite rules on plugin deactivation
 */
function shopify_connect_deactivation() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules(true);
}
register_deactivation_hook(SHOPIFY_PLUGIN_DIR . 'shopify.php', 'shopify_connect_deactivation');

/**
 * Flush rewrite rules when the disableProductRewrites constant changes
 * This should be called manually when the constant is changed
 */
function shopify_connect_flush_rewrites_for_product_disabled() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules(true);
}