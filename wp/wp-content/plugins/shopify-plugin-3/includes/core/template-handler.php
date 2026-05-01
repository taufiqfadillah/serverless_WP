<?php
/**
 * Template Handler for Shopify WP Connect
 * 
 * This class manages WordPress block templates for Shopify product and collection pages.
 * It provides functionality to:
 * - Handle template loading for Shopify product and collection pages
 * - Create and manage custom block templates from plugin templates
 * 
 * The class integrates with WordPress's block template system to provide
 * custom templates for Shopify content while maintaining compatibility
 * with the block editor and theme system.
 * 
 * 
 * Also manages 404 templates
 */

if (!defined('ABSPATH')) {
    exit;
}


class Shopify_Block_Templates {


    public function __construct() {
        add_filter('get_block_templates', array($this, 'manage_shopify_block_template'), 10, 3);
        // add_action('template_redirect', array($this, 'handle_404_redirects'));
        
        // Add template_include filter for classic themes
        add_filter('template_include', array($this, 'handle_classic_theme_templates'), 99);
        
        // Handle 404 errors for disabled product URLs
        add_action('template_redirect', array($this, 'handle_404_errors'));
    }

    /**
     * Handle classic theme templates for Shopify pages
     */
    public function handle_classic_theme_templates($template) {
        // Only handle if this is a classic theme
        if ($this->is_block_theme()) {
            return $template;
        }

        // Handle Shopify collection pages
        if (get_query_var('is_shopify_collection_template') && !empty(get_query_var('shopify_collection_handle'))) {
            $collection_handle = get_query_var('shopify_collection_handle');
            $collection_check = shopify_wp_connect_check_collection_exists($collection_handle);
            
            if (is_wp_error($collection_check)) {
                error_log('Shopify Collection Error: ' . $collection_check->get_error_message());
                status_header(404);
                return get_404_template();
            }
            
            return $this->get_classic_collection_template();
        }

        // Handle Shopify product pages only if product rewrites are not disabled
        if (!defined('SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES') || !SHOPIFY_WP_CONNECT_DISABLE_PRODUCT_REWRITES) {
            if (get_query_var('is_shopify_product_template') && !empty(get_query_var('shopify_product_handle'))) {
                $product_handle = get_query_var('shopify_product_handle');
                $product_check = shopify_wp_connect_check_product_exists($product_handle);
                
                if (is_wp_error($product_check)) {
                    error_log('Shopify Product Error: ' . $product_check->get_error_message());
                    status_header(404);
                    return get_404_template();
                }
                
                return $this->get_classic_product_template();
            }
        }

        // Handle Shopify cart page
        if (get_query_var('is_shopify_cart_template') && get_query_var('shopify_cart_page')) {
            $shop_info = get_option('shopify_shop_info');
            if (!$shop_info || empty($shop_info)) {
                status_header(404);
                return get_404_template();
            }
            return $this->get_classic_cart_template();
        }

        return $template;
    }

    /**
     * Check if the current theme is a block theme
     */
    private function is_block_theme() {
        return wp_is_block_theme();
    }

    /**
     * Get the classic theme collection template
     */
    private function get_classic_collection_template() {
        $template_path = SHOPIFY_PLUGIN_DIR . 'templates/classic-themes/collection.php';
        
        if (file_exists($template_path)) {
            return $template_path;
        }
        
        // Fallback to a basic template
        return $this->create_fallback_collection_template();
    }

    /**
     * Get the classic theme product template
     */
    private function get_classic_product_template() {
        $template_path = SHOPIFY_PLUGIN_DIR . 'templates/classic-themes/product.php';
        
        if (file_exists($template_path)) {
            return $template_path;
        }
        
        // Fallback to a basic template
        return $this->create_fallback_product_template();
    }

    /**
     * Get the classic theme cart template
     */
    private function get_classic_cart_template() {
        $template_path = SHOPIFY_PLUGIN_DIR . 'templates/classic-themes/cart.php';
        
        if (file_exists($template_path)) {
            return $template_path;
        }
        
        // Fallback to a basic template
        return $this->create_fallback_cart_template();
    }

    /**
     * Create a fallback collection template for classic themes
     */
    private function create_fallback_collection_template() {
        $template_content = '<?php
        /**
         * Classic Theme Collection Template for Shopify WP Connect
         * 
         * This is a fallback template for classic themes that don\'t have
         * custom collection templates.
         */

        get_header(); ?>

        <div id="primary" class="content-area">
            <main id="main" class="site-main">
                <div class="shopify-collection-content">
                    <?php 
                    // Include the collection template content
                    include SHOPIFY_PLUGIN_DIR . \'includes/components/frontend/shopify-collection.php\';
                    ?>
                </div>
            </main>
        </div>

        <?php get_footer(); ?>';

        $temp_file = wp_tempnam('shopify-collection-');
        file_put_contents($temp_file, $template_content);
        
        return $temp_file;
    }

    /**
     * Create a fallback product template for classic themes
     */
    private function create_fallback_product_template() {
        $template_content = '<?php
/**
 * Classic Theme Product Template for Shopify WP Connect
 * 
 * This is a fallback template for classic themes that don\'t have
 * custom product templates.
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="shopify-product-content">
            <?php 
            // Include the product template content
            include SHOPIFY_PLUGIN_DIR . \'includes/components/frontend/shopify-pdp.php\';
            ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>';

        $temp_file = wp_tempnam('shopify-product-');
        file_put_contents($temp_file, $template_content);
        
        return $temp_file;
    }

    /**
     * Create a fallback cart template for classic themes
     */
    private function create_fallback_cart_template() {
        $template_content = '<?php
/**
 * Classic Theme Cart Template for Shopify WP Connect
 * 
 * This is a fallback template for classic themes that don\'t have
 * custom cart templates.
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="shopify-cart-content">
            <?php shopify_include_template(\'shopify-cart-page.php\', SHOPIFY_PLUGIN_DIR . \'includes/components/frontend/shopify-cart-page.php\'); ?>
        </div>
    </main>
</div>

<?php get_footer(); ?>';

        $temp_file = wp_tempnam('shopify-cart-');
        file_put_contents($temp_file, $template_content);
        
        return $temp_file;
    }

    public function manage_shopify_block_template($query_result, $query, $template_type) {
        if ('wp_template' !== $template_type) {
            return $query_result;
        }

        $theme = wp_get_theme();
        $block_source = 'plugin';

        // Handle single collection template
        if (get_query_var('is_shopify_collection_template') && !empty(get_query_var('shopify_collection_handle'))) {
            $collection_handle = get_query_var('shopify_collection_handle');
            $collection_check = shopify_wp_connect_check_collection_exists($collection_handle);
            
            if (is_wp_error($collection_check)) {
                error_log('Shopify Collection Error: ' . $collection_check->get_error_message());
                return $this->get_404_template('404', '404 Template');
            }
            
            return $this->get_template('single-shopify-collection', 'Single Shopify Collection', 'Template for displaying a single Shopify collection.');
        }

        // Handle single product template
        if (get_query_var('is_shopify_product_template') && !empty(get_query_var('shopify_product_handle'))) {
            $product_handle = get_query_var('shopify_product_handle');
            $product_check = shopify_wp_connect_check_product_exists($product_handle);
            
            if (is_wp_error($product_check)) {
                error_log('Shopify Product Error: ' . $product_check->get_error_message());
                return $this->get_404_template('404', '404 Template');
            }
            
            return $this->get_template('single-shopify-product', 'Single Shopify Product', 'Template for displaying a single Shopify product.');
        }

        // Handle cart page template
        if (get_query_var('is_shopify_cart_template') && get_query_var('shopify_cart_page')) {
            $shop_info = get_option('shopify_shop_info');
            if (!$shop_info || empty($shop_info)) {
                return $this->get_404_template('404', '404 Template');
            }
            
            return $this->get_template('single-shopify-cart', 'Shopify Cart', 'Template for Shopify cart page.');
        }

        return $query_result;
    }

   

    /**
     * Set a transient to mark a collection as non-existent
     */
    public function set_collection_not_found($collection_handle) {
        $transient_key = 'shopify_collection_not_found_' . sanitize_key($collection_handle);
        set_transient($transient_key, true, 0); // 0 means it won't expire automatically
    }

    private function get_template($template_slug, $title, $description) {
        $theme = wp_get_theme();
        $template_id = $theme->get_stylesheet() . '//' . $template_slug;
        
        // Use block-themes directory for these templates
        if ($template_slug === 'single-shopify-collection' || $template_slug === 'single-shopify-product' || $template_slug === 'single-shopify-cart') {
            $template_file = SHOPIFY_PLUGIN_DIR . 'templates/block-themes/' . $template_slug . '.html';
        } else {
            $template_file = SHOPIFY_PLUGIN_DIR . 'templates/' . $template_slug . '.html';
        }

        if (file_exists($template_file)) {
            $template_contents = file_get_contents($template_file);
            $new_block = new \WP_Block_Template();
            $new_block->type = 'wp_template';
            $new_block->theme = $theme->get_stylesheet();
            $new_block->slug = $template_slug;
            $new_block->id = $template_id;
            $new_block->title = $title;
            $new_block->description = $description;
            $new_block->source = 'plugin';
            $new_block->status = 'publish';
            $new_block->has_theme_file = false;
            $new_block->is_custom = true;
            $new_block->content = $template_contents;
            return array($new_block);
        }

        return array();
    }
    
    
    private function get_404_template($title, $description) {
        $theme = wp_get_theme();
        $template_id = $theme->get_stylesheet() . '//' . '404';
        $template_file = get_template_directory() . '/templates/404.html';

        // Set 404 status
        status_header(404);
        
        // Set the page title to "Page not found"
        add_filter('pre_get_document_title', function() {
            return 'Page not found - ' . get_bloginfo('name');
        });

        if (file_exists($template_file)) {
            $template_contents = file_get_contents($template_file);
            $new_block = new \WP_Block_Template();
            $new_block->type = 'wp_template';
            $new_block->theme = $theme->get_stylesheet();
            $new_block->slug = '404';  // Fixed undefined variable
            $new_block->id = $template_id;
            $new_block->title = $title;
            $new_block->description = $description;
            $new_block->source = 'plugin';
            $new_block->status = 'publish';
            $new_block->has_theme_file = false;
            $new_block->is_custom = true;
            $new_block->content = $template_contents;
            return array($new_block);
        }

        return array();
    }

    /**
     * Handle 404 errors for disabled product URLs
     */
    public function handle_404_errors() {
        if (get_query_var('error') === '404') {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
        }
    }
}