<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$collection = isset($attributes['selectedCollection']) ? $attributes['selectedCollection'] : [];
$selected_store_url = isset($attributes['selectedStoreUrl']) ? $attributes['selectedStoreUrl'] : '';
$collection_handle = is_array($collection) && isset($collection['handle']) ? $collection['handle'] : '';
$max_products_per_row = isset($attributes['maxProductsPerRow']) ? $attributes['maxProductsPerRow'] : 3;
$max_products_per_page = isset($attributes['maxProductsPerPage']) ? $attributes['maxProductsPerPage'] : 12;
$show_pagination = isset($attributes['showPagination']) ? $attributes['showPagination'] : true;

// Get current store URL from settings
$current_store_url = get_option('shopify_store_url', '');

// Check for store URL mismatch
// Show mismatch error if:
// 1. We have a collection
// 2. Either:
//    a) We have a stored selectedStoreUrl that doesn't match current store URL, OR
//    b) We have a collection but no current store URL (no credentials)
$is_store_url_mismatch = !empty($collection) && (
    (empty($current_store_url)) || 
    (!empty($selected_store_url) && $selected_store_url !== $current_store_url)
);




// Get card behavior from global plugin settings instead of block attributes
$card_behavior = get_option('shopify_card_behavior', 'both');
// Use the selected collection handle if available, otherwise fall back to the query var
$collection_handle = !empty($collection_handle) ? $collection_handle : ((!empty($store_url) && !empty($api_key)) ? get_query_var('shopify_collection_handle') : '');

// Generate a unique ID for this block instance to avoid conflicts when multiple blocks are on the same page
$block_unique_id = 'product-list-' . uniqid();

// Check for theme override for collection block
$theme_collection_template_path = get_stylesheet_directory() . '/shopify/collection.php';
if (file_exists($theme_collection_template_path)) {
    error_log('Shopify Collection Block - Using theme collection template: ' . $theme_collection_template_path);
    
    // Set up variables that the theme template might need
    $attributes['maxProductsPerRow'] = $max_products_per_row;
    $attributes['maxProductsPerPage'] = $max_products_per_page;
    $attributes['blockUniqueId'] = $block_unique_id;
    $attributes['collectionHandle'] = $collection_handle;
    $attributes['cardBehavior'] = $card_behavior;
    
    // Also set as direct variables for backward compatibility
    $max_products_per_row = $attributes['maxProductsPerRow'];
    $max_products_per_page = $attributes['maxProductsPerPage'];
    $block_unique_id = $attributes['blockUniqueId'];
    $card_behavior = $attributes['cardBehavior'];
    
    // Include the product card functions that the collection template needs
    $theme_product_card_path = get_stylesheet_directory() . '/shopify/product-card.php';
    if (file_exists($theme_product_card_path)) {
        require_once $theme_product_card_path;
    } else {
        // Fallback to plugin product card functions
        require_once SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/product-cards/storefront-product-card.php';
    }
    
    // Include the theme collection template
    include $theme_collection_template_path;
    return;
}

// Include the product card function with theme override support
// Try theme override first
$theme_template_path = get_stylesheet_directory() . '/shopify/product-card.php';
if (file_exists($theme_template_path)) {
    error_log('Shopify Collection Block - Using theme template: ' . $theme_template_path);
    require_once $theme_template_path;
} else {
    error_log('Shopify Collection Block - Using plugin client template');
    require_once SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/product-cards/storefront-product-card.php';
}


// Handle no store
$is_no_store = empty($current_store_url);
if ($is_no_store) {
    return;
}

// Handle store URL mismatch first
if ($is_store_url_mismatch) {
    echo '<div class="shopify-collection-block__error shopify-collection-block__error--no-collection">';
    echo '<p class="shopify-collection-block__error-message">Collection is not connected. Please edit this block to select a new collection.</p>';
    echo '</div>';
    return;
}

if (empty($collection_handle)) {
    echo '<div class="shopify-collection-block__error shopify-collection-block__error--no-collection">';
    echo '<p class="shopify-collection-block__error-message">You must add a collection.</p>';
    echo '</div>';
    return;
}

?>


<div <?php echo wp_kses_post(get_block_wrapper_attributes(['class' => "max-products-per-row-{$max_products_per_row} max-products-per-page-{$max_products_per_page}"])); ?>>
<shopify-context type="collection" handle="<?php echo esc_attr($collection_handle); ?>">
  <template>
    <div class="collection-layout">
      <div class="collection-grid__grid max-products-per-row-<?php echo esc_attr($max_products_per_row); ?> max-products-per-page-<?php echo esc_attr($max_products_per_page); ?>">
        <?php
        // When pagination is disabled, show 250 results instead of the configured max products per page
        $products_to_show = $show_pagination ? $max_products_per_page : 250;
        ?>
        <shopify-list-context id="<?php echo esc_attr($block_unique_id); ?>" type="product" query="collection.products" first="<?php echo esc_attr($products_to_show); ?>">
          <!-- This template is repeated for each product in the collection -->
          <template>
            <?php 
            render_product_card_client('', true, $card_behavior); // The handle will be provided by the Shopify context
            ?>
          </template>
        </shopify-list-context>
      </div>


      <?php 
      if ($show_pagination) {
        // Include and render the reusable pagination snippet
        require_once SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/pagination.php';
        // Render the pagination using the unique list id
        render_shopify_pagination($block_unique_id);
      }
      ?>
    </div>
  </template>
</shopify-context>
</div>

<?php 
// Include the shared product modal from the main plugin with theme override support
    shopify_include_template('modal.php', SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/modal.php');
?>

