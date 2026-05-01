<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Admin Toolbar Integration
 * 
 * Adds Shopify-related buttons to the WordPress admin toolbar for quick access
 * to common Shopify admin functions.
 */

/**
 * Enqueue toolbar assets
 */
function shopify_enqueue_toolbar_assets() {
    // Enqueue toolbar styles
    wp_enqueue_style(
        'shopify-toolbar',
        plugins_url('assets/css/toolbar.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        defined('SHOPIFY_WP_VERSION') ? SHOPIFY_WP_VERSION : '1.0.0'
    );

    // Add inline styles for toolbar icons
    $custom_css = "
        #wp-admin-bar-collections_button .ab-icon,
        #wp-admin-bar-product_button .ab-icon,
        #wp-admin-bar-visit_shopify_button .ab-icon {   
            background: url('" . esc_url(plugins_url('assets/icons/menu-icon.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')) . "') no-repeat center !important;
            background-size: 18px !important;
            width: 20px !important;
            height: 17px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            margin-right: 5px !important;
            font: normal 20px/1 dashicons !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }
        
        #wp-admin-bar-collections_button .ab-icon::before,
        #wp-admin-bar-product_button .ab-icon::before,
        #wp-admin-bar-visit_shopify_button .ab-icon::before {
            content: ' ' !important;
            background: url('" . esc_url(plugins_url('assets/icons/menu-icon.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')) . "') no-repeat center !important;
            background-size: contain !important;
            width: 20px !important;
            height: 17px !important;
            display: inline-block !important;
            vertical-align: middle !important;
            position: relative !important;
            top: -2px !important;
            font: normal 20px/1 dashicons !important;
            -webkit-font-smoothing: antialiased !important;
            -moz-osx-font-smoothing: grayscale !important;
        }";
    
    wp_add_inline_style('shopify-toolbar', $custom_css);
}
add_action('admin_enqueue_scripts', 'shopify_enqueue_toolbar_assets');
add_action('wp_enqueue_scripts', 'shopify_enqueue_toolbar_assets');

add_action('admin_bar_menu', 'add_collections_toolbar_button', 100);
add_action('admin_bar_menu', 'add_product_toolbar_button', 100);
add_action('admin_bar_menu', 'add_visit_shopify_toolbar_button', 100);

function add_product_toolbar_button($wp_admin_bar) {
    // Get the current URL
    $current_url = home_url(add_query_arg([]));

    // Check if the URL contains '/products/'
    if (strpos($current_url, '/products/') !== false) {
        // Get the product handle from the URL
        $product_handle = get_query_var('shopify_product_handle');
        
        if (!empty($product_handle)) {
            // Get product data to get the ID
            $product_data = shopify_wp_connect_check_product_exists($product_handle);
            
            if (!is_wp_error($product_data) && !empty($product_data['id'])) {
                // Extract the numeric ID from the Shopify ID (format: gid://shopify/Product/123456789)
                $product_id = basename($product_data['id']);
                
                // Get the store URL from options
                $store_url = get_option('shopify_store_url');
                $store_name = wp_parse_url($store_url, PHP_URL_HOST);
                $store_handle = str_replace('.myshopify.com', '', $store_url);

                // Define the button arguments
                $args = array(
                    'id'    => 'product_button',
                    'title' => '<span class="ab-icon"></span>View Product',
                    'href'  => "https://admin.shopify.com/store/{$store_handle}/products/{$product_id}",
                    'meta'  => array(
                        'class' => 'product-toolbar-button',
                        'title' => 'View this product in Shopify admin',
                        'target' => '_blank'
                    )
                );

                // Add the button to the toolbar
                $wp_admin_bar->add_node($args);
            }
        }
    }
}



function add_collections_toolbar_button($wp_admin_bar) {
    // Get the current URL
    $current_url = home_url(add_query_arg([]));

    // Check if the URL contains '/collections/'
    if (strpos($current_url, '/collections/') !== false) {
        // Get the collection handle from the URL
        $collection_handle = get_query_var('shopify_collection_handle');
        
        if (!empty($collection_handle)) {
            // Get collection data to get the ID
            $collection_data = shopify_wp_connect_check_collection_exists($collection_handle);
            
            if (!is_wp_error($collection_data) && !empty($collection_data['id'])) {
                // Extract the numeric ID from the Shopify ID (format: gid://shopify/Collection/123456789)
                $collection_id = basename($collection_data['id']);
                
                // Get the store URL from options
                $store_url = get_option('shopify_store_url');
                $store_name = wp_parse_url($store_url, PHP_URL_HOST);
                $store_handle = str_replace('.myshopify.com', '', $store_url);

                error_log('Store URL: ' . $store_url);
                error_log('Store name: ' . $store_name);
                error_log('Collection ID: ' . $collection_id);
                error_log('store_handle ID: ' . $store_handle);

                // Define the button arguments
                $args = array(
                    'id'    => 'collections_button',
                    'title' => '<span class="ab-icon"></span>View Collection',
                    'href'  => "https://admin.shopify.com/store/{$store_handle}/collections/{$collection_id}",
                    'meta'  => array(
                        'class' => 'collections-toolbar-button',
                        'title' => 'View this collection in Shopify admin',
                        'target' => '_blank'
                    )
                );

                // Add the button to the toolbar
                $wp_admin_bar->add_node($args);
            }
        }
    }
}

function add_visit_shopify_toolbar_button($wp_admin_bar) {
    // Only show in WordPress admin area
    if (!is_admin()) {
        return;
    }
    
    // Get the store URL from options
    $store_url = get_option('shopify_store_url');
    
    // Validate that we have a store URL and it's not empty
    if (empty($store_url) || trim($store_url) === '') {
        return;
    }
    
    // Clean up the store URL to get the admin URL
    $store_handle = str_replace('.myshopify.com', '', $store_url);
    $store_handle = str_replace('https://', '', $store_handle);
    $store_handle = str_replace('http://', '', $store_handle);
    
    // Validate that we have a store handle after cleaning
    if (empty($store_handle) || trim($store_handle) === '') {
        return;
    }
    
    // Debug logging
    error_log('Visit Shopify button - Store URL: ' . $store_url);
    error_log('Visit Shopify button - Store handle: ' . $store_handle);
    error_log('Visit Shopify button - Admin URL: https://admin.shopify.com/store/' . $store_handle);
    
    // Define the button arguments
    $args = array(
        'id'    => 'visit_shopify_button',
        'title' => '<span class="ab-icon"></span>Visit Shopify',
        'href'  => "https://admin.shopify.com/store/{$store_handle}",
        'meta'  => array(
            'class' => 'visit-shopify-toolbar-button',
            'title' => 'Visit your Shopify store admin',
            'target' => '_blank'
        )
    );

    // Add the button to the toolbar
    $wp_admin_bar->add_node($args);
    error_log('Visit Shopify button added to toolbar');
}

// Hook the functions to the admin bar
add_action('admin_bar_menu', 'add_product_toolbar_button', 999);
add_action('admin_bar_menu', 'add_collections_toolbar_button', 999);
add_action('admin_bar_menu', 'add_visit_shopify_toolbar_button', 999);