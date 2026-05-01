<?php
/**
 * Block registration + style enqueue functionality.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Shopify Product block and its assets.
 */
function shopify_register_blocks() {
    error_log('Shopify WP Connect: Starting block registration');
    
    // Check if the modern WordPress 6.8+ method is available
    if (function_exists('wp_register_block_types_from_metadata_collection')) {
        error_log('Shopify WP Connect: Using modern block registration method');
        
        // Register all blocks using the modern method
        wp_register_block_types_from_metadata_collection(
            SHOPIFY_PLUGIN_DIR . 'blocks/shopify-product-block/build',
            SHOPIFY_PLUGIN_DIR . 'blocks/shopify-product-block/build/blocks-manifest.php'
        );
        
        wp_register_block_types_from_metadata_collection(
            SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build',
            SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/blocks-manifest.php'
        );

        error_log('Shopify WP Connect: Using modern block registration method for shopify-collection-block');

        wp_register_block_types_from_metadata_collection(
            SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build',
            SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build/blocks-manifest.php'
        );


    } else {
        error_log('Shopify WP Connect: Using classic block registration method');
        
        // Fallback to traditional registration for all blocks
        
        // Register Product block script and style
        $editor_script_path = plugins_url( 'blocks/shopify-product-block/build/shopify-product-block/index.js', SHOPIFY_PLUGIN_DIR . 'shopify.php' );
        $version = file_exists($editor_script_path) ? filemtime($editor_script_path) : null;

        // Prevents caching in development mode
        if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
            $version .= '-' . uniqid(); // Unique ID for each load
        }
        wp_register_script(
            'shopify-product-block-editor',
            $editor_script_path,
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'shopify-storefront-elements' ),
            $version,
            true // Load in footer for consistency
        );

        $style_path = plugins_url( 'blocks/shopify-product-block/build/shopify-product-block/style-index.css', SHOPIFY_PLUGIN_DIR . 'shopify.php' );
        $style_version = file_exists(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-product-block/build/shopify-product-block/style-index.css') ? filemtime(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-product-block/build/shopify-product-block/style-index.css') : null;
        wp_register_style(
            'shopify-product-block',
            $style_path,
            array(),
            $style_version
        );

        $view_script_path = plugins_url( 'blocks/shopify-product-block/build/shopify-product-block/view.js', SHOPIFY_PLUGIN_DIR . 'shopify.php' );
        $view_script_version = file_exists(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-product-block/build/shopify-product-block/view.js') ? filemtime(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-product-block/build/shopify-product-block/view.js') : null;
        wp_register_script(
            'shopify-product-block-view',
            $view_script_path,
            array( 'shopify-storefront-elements' ),
            $view_script_version,
            true
        );

        // Register Collection block script and style
        $plugin_dir = SHOPIFY_PLUGIN_DIR;
        $plugin_url = plugins_url('', $plugin_dir . 'shopify.php');

        // Editor script
        $collection_editor_script_path = $plugin_url . '/blocks/shopify-collection-block/build/shopify-collection-block/index.js';
        $collection_editor_script_file = $plugin_dir . 'blocks/shopify-collection-block/build/shopify-collection-block/index.js';
        $version = file_exists($collection_editor_script_file) ? filemtime($collection_editor_script_file) : null;

        // Prevents caching in development mode
        if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
            $version .= '-' . uniqid(); // Unique ID for each load
        }

        wp_register_script(
            'shopify-collection-block-editor',
            $collection_editor_script_path,
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'shopify-storefront-elements' ),
            $version,
            true // Load in footer for consistency
        );

        // Editor style
        $collection_editor_style_path = plugins_url( 'blocks/shopify-collection-block/build/shopify-collection-block/index.css', SHOPIFY_PLUGIN_DIR . 'shopify.php' );
        $collection_editor_style_version = file_exists(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/shopify-collection-block/index.css') ? filemtime(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/shopify-collection-block/index.css') : null;
        wp_register_style(
            'shopify-collection-block-editor',
            $collection_editor_style_path,
            array(),
            $collection_editor_style_version
        );

        $collection_style_path = plugins_url( 'blocks/shopify-collection-block/build/shopify-collection-block/style-index.css', SHOPIFY_PLUGIN_DIR . 'shopify.php' );
        $collection_style_version = file_exists(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/shopify-collection-block/style-index.css') ? filemtime(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/shopify-collection-block/style-index.css') : null;
        wp_register_style(
            'shopify-collection-block',
            $collection_style_path,
            array(),
            $collection_style_version
        );

        $collection_view_script_path = plugins_url( 'blocks/shopify-collection-block/build/shopify-collection-block/view.js', SHOPIFY_PLUGIN_DIR . 'shopify.php' );
        $collection_view_script_version = file_exists(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/shopify-collection-block/view.js') ? filemtime(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/shopify-collection-block/view.js') : null;
        
        wp_register_script(
            'shopify-collection-block-view',
            $collection_view_script_path,
            array( 'shopify-storefront-elements' ),
            $collection_view_script_version,
            true
        );

        // Register Cart Toggle block script and style
        $cart_toggle_editor_script_path = plugins_url('blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/index.js', SHOPIFY_PLUGIN_DIR . 'shopify.php');
        $cart_toggle_editor_script_file = SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/index.js';
        $version = file_exists($cart_toggle_editor_script_file) ? filemtime($cart_toggle_editor_script_file) : null;

        if (defined('SHOPIFY_WP_CONNECT_DEBUG') && SHOPIFY_WP_CONNECT_DEBUG) {
            $version .= '-' . uniqid();
        }

        wp_register_script(
            'shopify-cart-toggle-block-editor',
            $cart_toggle_editor_script_path,
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'shopify-storefront-elements'),
            $version,
            true
        );

        $cart_toggle_style_path = plugins_url('blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/style-index.css', SHOPIFY_PLUGIN_DIR . 'shopify.php');
        $cart_toggle_style_version = file_exists(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/style-index.css') ? filemtime(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/style-index.css') : null;
        wp_register_style(
            'shopify-cart-toggle-block',
            $cart_toggle_style_path,
            array(),
            $cart_toggle_style_version
        );

        $cart_toggle_view_script_path = plugins_url('blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/view.js', SHOPIFY_PLUGIN_DIR . 'shopify.php');
        $cart_toggle_view_script_version = file_exists(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/view.js') ? filemtime(SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/view.js') : null;
        wp_register_script(
            'shopify-cart-toggle-block-view',
            $cart_toggle_view_script_path,
            array('shopify-storefront-elements'),
            $cart_toggle_view_script_version,
            true
        );

        // Register the Product block
        register_block_type('shopify-product/shopify-product-block', array(
            'editor_script' => 'shopify-product-block-editor',
            'editor_style' => 'shopify-product-block-editor',
            'style' => 'shopify-product-block',
            'view_script' => 'shopify-product-block-view',
            'render_callback' => 'shopify_render_product_block',
            'category' => 'shopify',
            'icon' => 'smiley',
            'title' => __('Add a product', 'shopify-plugin'),
            'description' => __('Display a Shopify product', 'shopify-plugin'),
            'supports' => array(
                'html' => false,
            ),
            'attributes' => array(
                'selectedProduct' => array(
                    'type' => 'object',
                    'default' => null
                ),
                'selectedStoreUrl' => array(
                    'type' => 'string',
                    'default' => ''
                )
            )
        ));

        // Register the Collection block
        register_block_type('shopify-collection/shopify-collection-block', array(
            'editor_script' => 'shopify-collection-block-editor',
            'editor_style' => 'shopify-collection-block-editor',
            'style' => 'shopify-collection-block',
            'view_script' => 'shopify-collection-block-view',
            'render_callback' => 'shopify_render_collection_block',
            'category' => 'shopify',
            'icon' => 'grid-view',
            'title' => __('Add a Shopify collection', 'shopify-plugin'),
            'description' => __('Display a Shopify collection', 'shopify-plugin'),
            'supports' => array(
                'html' => false,
            ),
            'attributes' => array(
                'selectedCollection' => array(
                    'type' => 'object',
                    'default' => null
                ),
                'maxProductsPerRow' => array(
                    'type' => 'number',
                    'default' => 3
                ),
                'maxProductsPerPage' => array(
                    'type' => 'number',
                    'default' => 12
                ),
                'showPagination' => array(
                    'type' => 'boolean',
                    'default' => true
                )
            )
        ));

        // Register the Cart Toggle block
        register_block_type('create-block/shopify-cart-toggle-block', array(
            'editor_script' => 'shopify-cart-toggle-block-editor',
            'editor_style' => 'shopify-cart-toggle-block-editor',
            'style' => 'shopify-cart-toggle-block',
            'view_script' => 'shopify-cart-toggle-block-view',
            'category' => 'shopify',
            'icon' => 'cart',
            'title' => __('Cart Toggle', 'shopify-plugin'),
            'description' => __('Display a Shopify cart toggle button', 'shopify-plugin'),
            'supports' => array(
                'html' => false,
                'spacing' => array(
                    'margin' => true,
                    'padding' => true,
                    'blockGap' => true,
                ),
            ),
            'render_callback' => 'shopify_render_cart_toggle_block',
            'attributes' => array(
                'toggleText' => array(
                    'type' => 'string',
                    'default' => 'View Cart'
                ),
                'enableCartIcon' => array(
                    'type' => 'boolean',
                    'default' => true
                )
            )
        ));
    }

    error_log('Shopify WP Connect: Block registration completed');
}
add_action( 'init', 'shopify_register_blocks' );

/**
 * Render callback for the Shopify Product block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered block HTML.
 */
function shopify_render_product_block($attributes) {
    $render_template_path = SHOPIFY_PLUGIN_DIR . 'blocks/shopify-product-block/build/shopify-product-block/render.php';
    
    if (!file_exists($render_template_path)) {
        return '<!-- Shopify Product Block: Render template not found -->';
    }
    
    ob_start();
    include $render_template_path;
    return ob_get_clean();
}

/**
 * Render callback for the Shopify Collection block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered block HTML.
 */
function shopify_render_collection_block($attributes) {
    $render_template_path = SHOPIFY_PLUGIN_DIR . 'blocks/shopify-collection-block/build/shopify-collection-block/render.php';
    
    if (!file_exists($render_template_path)) {
        return '<!-- Shopify Collection Block: Render template not found -->';
    }
    
    ob_start();
    include $render_template_path;
    return ob_get_clean();
}

/**
 * Render callback for the Shopify Cart Toggle block.
 *
 * @param array $attributes Block attributes.
 * @return string Rendered block HTML.
 */
function shopify_render_cart_toggle_block($attributes, $content) {
    $render_template_path = SHOPIFY_PLUGIN_DIR . 'blocks/shopify-cart-toggle-block/build/shopify-cart-toggle-block/render.php';
    
    if (!file_exists($render_template_path)) {
        return '<!-- Shopify Cart Toggle Block: Render template not found -->';
    }
    
    ob_start();
    include $render_template_path;
    return ob_get_clean();
}

/**
 * Enqueue the main plugin stylesheet.
 */
function shopify_enqueue_styles() {
    wp_enqueue_style(
        'shopify-styles',
        plugins_url('style.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
        array(),
        filemtime(SHOPIFY_PLUGIN_DIR . 'style.css')
    );
}
add_action('wp_enqueue_scripts', 'shopify_enqueue_styles');

/**
 * Enqueue assets for the block editor.
 */
function shopify_enqueue_block_editor_assets() {
    $settings = [
        'storeUrl' => get_option('shopify_store_url', ''),
        'apiKey'   => get_option('shopify_api_key', ''),
    ];

    // Define all possible script handles for our blocks.
    $handles = [
        // Handles from the "classic" registration method.
        'shopify-product-block-editor',
        'shopify-collection-block-editor',
        'shopify-cart-toggle-block-editor',
        // Handles automatically generated by the "modern" registration method from block.json.
        'shopify-product-shopify-product-block-editor-script',
        'shopify-collection-shopify-collection-block-editor-script',
        'create-block-shopify-cart-toggle-block-editor-script',
    ];

    foreach ( $handles as $handle ) {
        if ( wp_script_is( $handle, 'registered' ) ) {
            wp_localize_script( $handle, 'shopifyPluginSettings', $settings );
            wp_set_script_translations( $handle, 'shopify-plugin', SHOPIFY_PLUGIN_DIR . 'languages' );
            error_log('Shopify: Translations set for handle ' . $handle);
        }
    }
}
add_action('enqueue_block_editor_assets', 'shopify_enqueue_block_editor_assets'); 


// Register custom block category
function shopify_register_block_category( $categories ) {
    return array_merge(
        $categories,
        [
            [
                'slug'  => 'shopify',
                'title' => __( 'Shopify', 'shopify-plugin' ),
            ],
        ]
    );
}

function shopify_init_block_category() {
    add_filter( 'block_categories_all', 'shopify_register_block_category', -10, 1 );
}
add_action( 'init', 'shopify_init_block_category', 1 ); 

add_filter('load_script_translation_file', function($file, $handle, $domain) {
    if ($domain !== 'shopify') return $file;
    $locale = get_locale();
    $dir = SHOPIFY_PLUGIN_DIR . 'languages';
    if (!file_exists($file) && $handle === 'shopify-collection-shopify-collection-block-editor-script') {
        // Use src path hash from .po
        $src_path = 'blocks/shopify-collection-block/src/shopify-collection-block/edit.js';
        $hash = md5($src_path); // This should be 64dcbbe6d3927946d236e7413971e299 based on your .pot
        $new_file = $dir . '/' . $domain . '-' . $locale . '-' . $hash . '.json';
        if (file_exists($new_file)) {
            error_log('Shopify: Overriding collection translation to ' . $new_file);
            return $new_file;
        }
        // Try component path if main fails
        $src_path = 'blocks/shopify-collection-block/src/shopify-collection-block/components/CollectionSearch.js';
        $hash = md5($src_path); // 4a6170dedc4062b7004b2b095c1ba049
        $new_file = $dir . '/' . $domain . '-' . $locale . '-' . $hash . '.json';
        if (file_exists($new_file)) {
            error_log('Shopify: Overriding collection translation to ' . $new_file);
            return $new_file;
        }
    }
    return $file;
}, 10, 3);

