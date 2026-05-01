<?php
/**
 * This is the file that gets injected on the collection page template when using rewrites.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

 
$store_url = get_option('shopify_store_url');
$api_key = get_option('shopify_api_key');
$collection_handle = (!empty($store_url) && !empty($api_key)) ? get_query_var('shopify_collection_handle') : 'accessories';

// Generate a unique ID for this collection instance
$collection_unique_id = 'product-list-' . uniqid();

// Get collection data from the template handler
$collection_data = shopify_wp_connect_check_collection_exists($collection_handle);

// Get card behavior from global plugin settings
$card_behavior = get_option('shopify_card_behavior', 'both');

// Note: SEO title and description are now handled in includes/core/seo.php
?>

<!-- Set collection you want to display -->
<shopify-context type="collection" handle="<?php echo esc_attr($collection_handle); ?>">
  <template>
    <div class="collection-grid__container">
      <div class="collection-grid__grid">
        <!-- Define a new context for the products within the collection -->
        <shopify-list-context id="<?php echo esc_attr($collection_unique_id); ?>" type="product" query="collection.products" first="15">
          <!-- This template is repeated for each product in the collection -->
          <template>
            <?php 
            include SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/product-cards/storefront-product-card.php';
            render_product_card_client('', true, $card_behavior); // The handle will be provided by the Shopify context
            ?>
          </template>
        </shopify-list-context>
      </div>
        <?php 
        // Include and render the reusable pagination snippet
        include SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/pagination.php';
        // Render the pagination using the unique list id
        render_shopify_pagination($collection_unique_id);
        ?>
    </div>

  </template>
</shopify-context>

<?php 
// Include the shared product modal from the main plugin with theme override support
    shopify_include_template('modal.php', SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/modal.php');
?>