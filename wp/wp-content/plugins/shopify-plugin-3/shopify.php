<?php
/*
Plugin Name: Shopify
Plugin URI: https://github.com/shopify/shopify
Description: Connects your WordPress site with Shopify, providing seamless integration for products, collections, and cart functionality.
Version: 1.0.0
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Author: Shopify
Author URI: https://shopify.com
License: GPLv2 or later
Text Domain: shopify-plugin
Domain Path: /languages
Stable tag: 1.0.0
*/

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Define the plugin directory constant
if ( ! defined( 'SHOPIFY_PLUGIN_DIR' ) ) {
	define( 'SHOPIFY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Define the plugin version constant
if ( ! defined( 'SHOPIFY_WP_VERSION' ) ) {
	define( 'SHOPIFY_WP_VERSION', '1.0.0' );
}

// Disable product rewrites - set to true to disable product URL rewrites
define( 'SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES', false );

// Show welcome page in admin - set to false to hide welcome page in the wp nav
define('SHOPIFY_WP_CONNECT_SHOW_WELCOME', false);

// Auto-flush permalinks on Advanced settings update - set to false to disable automatic permalink flushing
define('SHOPIFY_WP_CONNECT_AUTO_FLUSH_PERMALINKS', true);


// Set query vars
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/query-vars.php';

// Handle SEO meta tags for Shopify product and collection pages
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/seo.php';

// Handles URL rewriting for Shopify products and collections (e.g., /products/product-handle, /collections/collection-handle)
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/rewrite-rules.php';

// Manages WordPress block templates for Shopify product and collection pages, handles template loading and 404 errors
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/template-handler.php';

// Enqueues frontend assets (CSS/JS), adds Shopify store wrapper, and handles cart integration in navigation
// Note: Cart injection includes duplication prevention logic to avoid conflicts with manual cart toggle blocks
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/scaffold-frontend.php';

// Handles all frontend component enqueues with proper WordPress standards
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/frontend-assets.php';

// Provides helper functions to check if Shopify products/collections exist via GraphQL API calls
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/utils.php';

// Manages theme template overrides, allowing themes to customize Shopify templates with priority system
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/theme-overrides.php';

// Analytics tracking functionality
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/analytics.php';

// Creates WordPress admin interface, settings pages, menu structure, and handles form processing
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/admin-settings.php';

// Registers shortcodes for injecting PHP content into WordPress block templates for Shopify products/collections
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/shortcodes.php';

// Adds admin toolbar buttons for quick access to Shopify admin when viewing products/collections
require_once SHOPIFY_PLUGIN_DIR . 'includes/core/toolbars.php';

// Instantiate the classes
new Shopify_Block_Templates();
new Shopify_Admin();

// Register the blocks last
require_once SHOPIFY_PLUGIN_DIR . 'blocks/block-registration.php';

// Global variable to track if cart toggle was ever added automatically
$shopify_cart_toggle_added_automatically = false;

// Initialize cart toggle hooks on every page load
add_action( 'init', 'shopify_register_cart_toggle_hooks', 20 );

// Debug mode - prevent caching in development mode
define('SHOPIFY_WP_CONNECT_DEBUG', true);



/**
 * Plugin activation function
 */
function shopify_activate() {
    // Track installation
    track_plugin_installation();
    
    // Store activation time for tracking total usage
    update_option( 'shopify_activation_time', current_time( 'timestamp' ) );
    
    // Track activation
    track_plugin_activation();
    
    // Set a flag to redirect to welcome page
    update_option( 'shopify_show_welcome', true );
}

/**
 * Register cart toggle block hooks to automatically add to navigation blocks
 */
function shopify_register_cart_toggle_hooks() {
    $shop_info = get_option('shopify_shop_info', false);
    
    // Only register hooks if a shop is connected
    if ( ! $shop_info ) {
        return;
    }
    
    // Check if WordPress version supports Block Hooks API (6.4+)
    if ( version_compare( get_bloginfo( 'version' ), '6.4', '<' ) ) {
        return; // Gracefully skip on older WordPress versions
    }
    
    add_filter( 'hooked_block_types', 'shopify_add_cart_toggle_to_navigation', 10, 4 );
    
    // Register shutdown hook to check if fallback cart page is needed
    add_action( 'wp_footer', 'shopify_check_cart_fallback' );
}

/**
 * Check if cart toggle was added automatically, if not create fallback cart page
 */
function shopify_check_cart_fallback() {
    global $shopify_cart_toggle_added_automatically;
    echo '<script>console.log("Creating fallback cart page. Global variable shopify_cart_toggle_added_automatically:", ' . wp_json_encode($shopify_cart_toggle_added_automatically) . ');</script>';


    if (!$shopify_cart_toggle_added_automatically) {
        // Use a transient to prevent multiple page creations during the same request cycle
        $fallback_created = get_transient( 'shopify_cart_fallback_creating' );
        if ( ! $fallback_created ) {
            set_transient( 'shopify_cart_fallback_creating', true, 10 ); // 10 sec
            shopify_create_fallback_cart_page();
        }
    }
}

/**
 * Creates a fallback cart page when cart toggle cannot be added to navigation
 */
function shopify_create_fallback_cart_page() {
    // Don't create the page if the user is currently on the /cart URL
    // This prevents creating the page when users directly visit /cart on block themes
    if ( shopify_wp_connect_is_cart_page() ) {
        return;
    }

    // Check if cart page already exists
    $existing_page = get_page_by_path('cart');
    if ( $existing_page ) {
        return; // Page already exists
    }

    // Create the cart page
    $cart_page = array(
        'post_title'   => 'Cart',
        'post_content' => '<!-- wp:shortcode -->[shopify_cart]<!-- /wp:shortcode -->',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_name'    => 'cart'
    );

    $page_id = wp_insert_post( $cart_page );

    if ( $page_id && ! is_wp_error( $page_id ) ) {
        // Store the created page ID for reference
        update_option( 'shopify_fallback_cart_page_id', $page_id );
        error_log( 'Shopify: Created fallback cart page (ID: ' . $page_id . ') due to theme incompatibility' );
    }
}

/**
 * Add cart toggle block to navigation blocks in header areas
 */
function shopify_add_cart_toggle_to_navigation( $hooked_block_types, $relative_position, $anchor_block_type, $context ) {
    global $shopify_cart_toggle_added_automatically;
    
    if ( $anchor_block_type !== 'core/navigation' ) {
        return $hooked_block_types;
    }
    
    
    // Define our hooked block placements configuration
    $hooked_block_placements = array(
        array(
            'position' => 'after',
            'anchor'   => 'core/navigation',
            'area'     => 'header',
            'version'  => '6.4.0',
        ),
    );
    
    // Check each placement configuration
    foreach ( $hooked_block_placements as $placement ) {
        // Match position and anchor
        if ( $placement['position'] !== $relative_position || $placement['anchor'] !== $anchor_block_type ) {
            continue;
        }
        
        // Check area (header only)
        if ( isset( $placement['area'] ) ) {
            $is_target_area = false;
            
            if ( $context instanceof WP_Block_Template && isset( $context->area ) ) {
                $is_target_area = ( $context->area === $placement['area'] );
            } elseif ( $context instanceof WP_Block_Template && isset( $context->slug ) ) {
                // Fallback: check if template slug contains header
                $is_target_area = ( strpos( $context->slug, $placement['area'] ) !== false );
            }
            
            if ( ! $is_target_area ) {
                continue;
            }
        }
        
        // Add our cart toggle block
        $hooked_block_types[] = 'create-block/shopify-cart-toggle-block';
        $shopify_cart_toggle_added_automatically = true; // Track that we successfully added it
        break; // Only add once
    }
    
    return $hooked_block_types;
}

/**
 * Plugin deactivation function
 */
function shopify_deactivate() {
    // Track deactivation
    track_plugin_deactivation();
    
    // Clean up activation time and hooks
    delete_option( 'shopify_activation_time' );
}

/**
 * Plugin uninstallation function
 */
function shopify_uninstall() {
    // Track uninstallation
    track_plugin_uninstallation();
    
    // Clean up all plugin options
    delete_option( 'shopify_store_url' );
    delete_option( 'shopify_api_key' );
    delete_option( 'shopify_shop_info' );
    delete_option( 'shopify_enable_rewrites' );
    delete_option( 'shopify_ssr_query' );
    delete_option( 'shopify_ssr_fragment' );
    delete_option( 'shopify_activation_time' );
    delete_option( 'shopify_show_welcome' );
    delete_option( 'shopify_enable_analytics' );
}

// Register the hooks with the named functions
register_activation_hook( __FILE__, 'shopify_activate' );
register_deactivation_hook( __FILE__, 'shopify_deactivate' );
register_uninstall_hook( __FILE__, 'shopify_uninstall' );

// Add action to handle redirect after plugin activation
add_action('activated_plugin', 'shopify_activated_redirect');

/**
 * Redirect to welcome page after plugin activation
 */
function shopify_activated_redirect($plugin) {
    
    if ($plugin === plugin_basename(__FILE__)) {
        // Set a flag to redirect on next admin page load
        update_option('shopify_show_welcome', true);
        
        // Redirect to welcome page after a short delay
        wp_redirect(admin_url('admin.php?page=shopify-welcome'));
        exit;
    }
}