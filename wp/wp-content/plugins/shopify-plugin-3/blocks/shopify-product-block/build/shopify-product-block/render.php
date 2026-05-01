<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$product = isset($attributes['selectedProduct']) ? $attributes['selectedProduct'] : [];
$selected_store_url = isset($attributes['selectedStoreUrl']) ? $attributes['selectedStoreUrl'] : '';
$product_handle = is_array($product) && isset($product['handle']) ? $product['handle'] : '';



// Get current store URL from settings
$current_store_url = get_option('shopify_store_url', '');

// Check for store URL mismatch
// Show mismatch error if:
// 1. We have a product
// 2. Either:
//    a) We have a stored selectedStoreUrl that doesn't match current store URL, OR
//    b) We have a product but no current store URL (no credentials)
$is_store_url_mismatch = !empty($product) && 
    (empty($current_store_url) || 
     (!empty($selected_store_url) && $selected_store_url !== $current_store_url));



// Get card behavior from global plugin settings instead of block attributes
$card_behavior = get_option('shopify_card_behavior', 'both');

// Handle no store
$is_no_store = empty($current_store_url);
if ($is_no_store) {
    return;
}

// Handle store URL mismatch first
if ($is_store_url_mismatch) {
    echo '<div class="shopify-product-block__error shopify-product-block__error--no-product">';
    echo '<p class="shopify-product-block__error-message">Product is not connected. Please edit this block to select a new product.</p>';
    echo '</div>';
    return;
}

if (empty($product_handle)) {
    echo '<div class="shopify-product-block__error shopify-product-block__error--no-product">';
    echo '<p class="shopify-product-block__error-message">You must add a product.</p>';
    echo '</div>'; // Close the block wrapper
    return;
}

?>


<div <?php echo wp_kses_post(get_block_wrapper_attributes()); ?>>
    <?php
        // Try theme override first
        $theme_template_path = get_stylesheet_directory() . '/shopify/product-card.php';
        if (file_exists($theme_template_path)) {
            error_log('Shopify Product Block - Using theme template: ' . $theme_template_path);
            require_once $theme_template_path;
        } else {
            error_log('Shopify Product Block - Using plugin client template');
            require_once SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/product-cards/storefront-product-card.php';
        }
        render_product_card_client($product_handle, false, $card_behavior);
        ?>
</div>

<?php 
// Include the shared product modal from the main plugin with theme override support
shopify_include_template('modal.php', SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/modal.php');
?>



