<?php
/**
 * SEO Handler for Shopify WP Connect
 * 
 * This file handles comprehensive SEO functionality for Shopify product and collection pages.
 * It provides functionality to:
 * - Override WordPress document title and wp_title for Shopify pages
 * - Inject custom meta description tags with unique identifiers
 * - Add Open Graph meta tags for social media sharing
 * - Add Twitter Card meta tags for Twitter sharing
 * - Fetch and extract SEO data from Shopify products and collections
 * - Handle both modern (document_title) and legacy (wp_title) title systems
 * - Provide debug logging when SHOPIFY_WP_CONNECT_DEBUG is enabled
 * 
 * The SEO system integrates with WordPress's title and meta tag systems
 * to ensure Shopify content is properly indexed and shared on social platforms.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Set up SEO filters early in the WordPress lifecycle
 */

function shopify_wp_connect_setup_seo_filters() {
    // Check if we're on a Shopify product page
    if (shopify_wp_connect_is_product_page()) {
        $product_handle = shopify_wp_connect_get_product_handle();
        $product_data = shopify_wp_connect_get_product_seo_data($product_handle);
    }
    
    // Check if we're on a Shopify collection page
    if (shopify_wp_connect_is_collection_page()) {
        $collection_handle = shopify_wp_connect_get_collection_handle();
        $collection_data = shopify_wp_connect_get_collection_seo_data($collection_handle);
    }
}

/**
 * Override WordPress document title for Shopify pages
 */
function shopify_wp_connect_override_document_title($title) {
    // Check if we're on a Shopify product page
    if (shopify_wp_connect_is_product_page()) {
        $product_handle = shopify_wp_connect_get_product_handle();
        $product_data = shopify_wp_connect_get_product_seo_data($product_handle);
        
        if ($product_data && !is_wp_error($product_data) && !empty($product_data['title'])) {
            $custom_title = esc_attr($product_data['title']) . ' - ' . esc_html(get_bloginfo('name'));
            if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
                error_log('Shopify WP Connect: Overriding title for product "' . $product_data['title'] . '" to: ' . $custom_title);
            }
            return $custom_title;
        }
    }
    
    // Check if we're on a Shopify collection page
    if (shopify_wp_connect_is_collection_page()) {
        $collection_handle = shopify_wp_connect_get_collection_handle();
        $collection_data = shopify_wp_connect_get_collection_seo_data($collection_handle);
        
        if ($collection_data && !is_wp_error($collection_data) && !empty($collection_data['title'])) {
            $custom_title = esc_attr($collection_data['title']) . ' - ' . esc_html(get_bloginfo('name'));
            if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
                error_log('Shopify WP Connect: Overriding title for collection "' . $collection_data['title'] . '" to: ' . $custom_title);
            }
            return $custom_title;
        }
    }
    
    return $title;
}

/**
 * Override WordPress wp_title for themes that still use the deprecated function
 */
function shopify_wp_connect_override_wp_title($title, $sep, $seplocation) {
    // Check if we're on a Shopify product page
    if (shopify_wp_connect_is_product_page()) {
        $product_handle = shopify_wp_connect_get_product_handle();
        $product_data = shopify_wp_connect_get_product_seo_data($product_handle);
        
        if ($product_data && !is_wp_error($product_data) && !empty($product_data['title'])) {
            $custom_title = esc_attr($product_data['title']);
            if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
                error_log('Shopify WP Connect: Overriding wp_title for product "' . $product_data['title'] . '" to: ' . $custom_title);
            }
            return $custom_title;
        }
    }
    
    // Check if we're on a Shopify collection page
    if (shopify_wp_connect_is_collection_page()) {
        $collection_handle = shopify_wp_connect_get_collection_handle();
        $collection_data = shopify_wp_connect_get_collection_seo_data($collection_handle);
        
        if ($collection_data && !is_wp_error($collection_data) && !empty($collection_data['title'])) {
            $custom_title = esc_attr($collection_data['title']);
            if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
                error_log('Shopify WP Connect: Overriding wp_title for collection "' . $collection_data['title'] . '" to: ' . $custom_title);
            }
            return $custom_title;
        }
    }
    
    return $title;
}

/**
 * Inject SEO meta tags into WordPress head
 */
function shopify_wp_connect_inject_seo_meta() {
    // Check if we're on a Shopify product page
    if (shopify_wp_connect_is_product_page()) {
        $product_handle = shopify_wp_connect_get_product_handle();
        $product_data = shopify_wp_connect_get_product_seo_data($product_handle);
        
        if ($product_data && !is_wp_error($product_data)) {
            // Add meta description with unique identifier
            if (!empty($product_data['description'])) { 
                echo '<meta name="description" content="' . esc_attr($product_data['description']) . '" data-shopify="true" />' . "\n";
            }
            
            // Add Open Graph tags
            if (!empty($product_data['title'])) {
                echo '<meta property="og:title" content="' . esc_attr($product_data['title']) . '" />' . "\n";
            }
            if (!empty($product_data['description'])) {
                echo '<meta property="og:description" content="' . esc_attr($product_data['description']) . '" />' . "\n";
            }
            
            echo '<meta property="og:type" content="product" />' . "\n";
            
            // Add Twitter Card tags
            echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
            if (!empty($product_data['title'])) {
                echo '<meta name="twitter:title" content="' . esc_attr($product_data['title']) . '" />' . "\n";
            }
            if (!empty($product_data['description'])) {
                echo '<meta name="twitter:description" content="' . esc_attr($product_data['description']) . '" />' . "\n";
            }
        }
    }
    
    // Check if we're on a Shopify collection page
    if (shopify_wp_connect_is_collection_page()) {
        $collection_handle = shopify_wp_connect_get_collection_handle();
        $collection_data = shopify_wp_connect_get_collection_seo_data($collection_handle);
        
        if ($collection_data && !is_wp_error($collection_data)) {
            // Add meta description with unique identifier
            if (!empty($collection_data['description'])) {
                echo '<meta name="description" content="' . esc_attr($collection_data['description']) . '" data-shopify="true" />' . "\n";
            }
            
            // Add Open Graph tags
            if (!empty($collection_data['title'])) {
                echo '<meta property="og:title" content="' . esc_attr($collection_data['title']) . ' - ' . esc_html(get_bloginfo('name')) . '" />' . "\n";
            }
            if (!empty($collection_data['description'])) {
                echo '<meta property="og:description" content="' . esc_attr($collection_data['description']) . '" />' . "\n";
            }
            
            echo '<meta property="og:type" content="website" />' . "\n";
            
            // Add Twitter Card tags
            echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
            if (!empty($collection_data['title'])) {
                echo '<meta name="twitter:title" content="' . esc_attr($collection_data['title']) . '" />' . "\n";
            }
            if (!empty($collection_data['description'])) {
                echo '<meta name="twitter:description" content="' . esc_attr($collection_data['description']) . '" />' . "\n";
            }
        }
    }
}

/**
 * Get SEO data for a Shopify product
 */
function shopify_wp_connect_get_product_seo_data($product_handle) {
    // This function should fetch product data from Shopify
    // For now, we'll use the existing helper function and extract SEO data
    $product_check = shopify_wp_connect_check_product_exists($product_handle);
    
    if (is_wp_error($product_check)) {
        return $product_check;
    }
    
    // Extract SEO data from the product
    $seo_data = array(
        'title' => $product_check['title'] ?? '',
        'description' => $product_check['description'] ?? '',
        'image' => $product_check['featured_image'] ?? ''
    );
    
    return $seo_data;
}

/**
 * Get SEO data for a Shopify collection
 */
function shopify_wp_connect_get_collection_seo_data($collection_handle) {
    // This function should fetch collection data from Shopify
    // For now, we'll use the existing helper function and extract SEO data
    $collection_check = shopify_wp_connect_check_collection_exists($collection_handle);
    
    if (is_wp_error($collection_check)) {
        return $collection_check;
    }
    
    // Extract SEO data from the collection
    $seo_data = array(
        'title' => $collection_check['title'] ?? '',
        'description' => $collection_check['description'] ?? '',
        'image' => $collection_check['image'] ?? ''
    );
    
    return $seo_data;
}

// Hook into WordPress early for title filters
add_action('wp', 'shopify_wp_connect_setup_seo_filters');

// Hook into WordPress document title to override for Shopify pages with highest priority
add_filter('pre_get_document_title', 'shopify_wp_connect_override_document_title', 1, 1);

// Hook into WordPress wp_title for themes that still use the deprecated function
add_filter('wp_title', 'shopify_wp_connect_override_wp_title', 10, 3);

// Hook into WordPress head for meta tags with highest priority to ensure our description appears first
add_action('wp_head', 'shopify_wp_connect_inject_seo_meta', 0); 