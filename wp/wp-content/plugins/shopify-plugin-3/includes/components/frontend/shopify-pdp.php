<?php
/**
 * This is the file that gets injected on the product page template when using rewrites.
 * 
 * @var array $product_data The product data from Shopify
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$store_url = get_option('shopify_store_url');
$api_key = get_option('shopify_api_key');
$product_handle = (!empty($store_url) && !empty($api_key)) ? get_query_var('shopify_product_handle') : 'hoodie';

// Check for theme override for product detail page
$theme_pdp_template_path = get_stylesheet_directory() . '/shopify/pdp.php';
if (file_exists($theme_pdp_template_path)) {
    error_log('Shopify PDP - Using theme PDP template: ' . $theme_pdp_template_path);
    
    // Set up variables that the theme template might need
    $product_data = array(
        'store_url' => $store_url,
        'api_key' => $api_key,
        'product_handle' => $product_handle
    );
    
    // Include the theme PDP template
    include $theme_pdp_template_path;
    return;
}

error_log('Shopify PDP - Using plugin default template');
?>

<div class="shopify-product-details">
  <div class="single-product-layout">
    <div class="single-product">
      <!-- Set product you want to display -->
      <shopify-context type="product" handle="<?php echo esc_attr($product_handle); ?>">
        <template>
          <div class="single-product__container">
            <div class="single-product__media">
              <!-- Image carousel -->
              <div class="single-product__main-image">
                <shopify-media layout="fixed" query="product.selectedOrFirstAvailableVariant.image"></shopify-media>
              </div>
            </div>
            <div class="single-product__details">
              <div class="single-product__info">
                <h1 class="single-product__title">
                  <shopify-data query="product.title"></shopify-data>
                </h1>
                <div class="single-product__price">
                  <span>
                    <shopify-money query="product.selectedOrFirstAvailableVariant.price"></shopify-money>
                    <shopify-money
                      class="single-product__compare-price"
                      query="product.selectedOrFirstAvailableVariant.compareAtPrice"
                    ></shopify-money>
                  </span>
                </div>
              </div>
              <shopify-variant-selector></shopify-variant-selector>

              <div class="single-product__buttons">
                <div class="single-product__add-to-cart">
                  <div class="single-product__incrementor">
                    <button class="decrease" onclick="decreaseValue();">-</button>
                    <span class="single-product__count" id="single-product__count">1</span>
                    <button class="increase" onclick="increaseValue();">+</button>
                  </div>
                  <button
                    class="single-product__add-button"
                    onclick="getElementById('cart').addLine(event); getElementById('cart').showModal();"
                    shopify-attr--disabled="!product.selectedOrFirstAvailableVariant.product.availableForSale"
                  >
                    Add to cart
                  </button>
                </div>
                <button
                  class="single-product__buy-button"
                  onclick="document.querySelector('shopify-store').buyNow(event)"
                  class="product-buy-now__button"
                  shopify-attr--disabled="!product.selectedOrFirstAvailableVariant.product.availableForSale"
                >
                  <?php esc_html_e('Buy now', 'shopify-plugin'); ?>
                </button>
              </div>

              <div class="single-product__accordion">
                <div class="single-product__accordion__item">
                  <div class="single-product__accordion__header" data-toggle="#single-product1" onclick="shopifyToggleAccordion(event);">Description</div>
                  <div class="single-product__accordion__content" id="single-product1">
                    <p>
                      <span class="single-product__description-text">
                        <shopify-data query="product.description"></shopify-data>
                      </span>
                    </p>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </template>
      </shopify-context>
    </div>
  </div>

<?php
// Get card behavior from global plugin settings
$card_behavior = get_option('shopify_card_behavior', 'both');
?>

<!-- Collection / You may also like section -->
<div class="collection-layout product-recommendations">
  <shopify-context type="collection" handle="accessories">
    <template>
      <div class="collection-container">
        <div class="collection-title">
          <h2>You may also like</h2>
          <div class="collection__slider-controls">
            <button class="collection__slider-button prev" onclick="moveSlider(-1)">❮</button>
            <button class="collection__slider-button next" onclick="moveSlider(1)">❯</button>
          </div>
        </div>
        <!-- Collection slider -->
        <div class="collection__slider">
          <div class="collection-grid" id="collection__product-slider">
            <shopify-list-context type="product" query="collection.products" first="8">
              <template>
                <?php if ($card_behavior === 'both' || $card_behavior === 'quick-shop-only') { ?>
                <button
                  shopify-attr--disabled="!product.availableForSale"
                  class="collection-grid__product"
                  onclick="getElementById('product-modal').showModal(); getElementById('product-modal-context').update(event);"
                >
                  <div class="single-product__image-container">
                    <a
                      class="single-product__hover-button"
                      onclick="event.stopPropagation();document.querySelector('shopify-cart').addLine(event); document.querySelector('shopify-cart').showModal()"
                    >
                      <span>Quick add</span>
                    </a>
                    <shopify-media
                      width="242"
                      height="242"
                      max-images="1"
                      query="product.selectedOrFirstAvailableVariant.image"
                    ></shopify-media>
                  </div>
                  <div class="single-product__info">
                    <h3>
                      <shopify-data query="product.title"></shopify-data>
                    </h3>
                    <p class="single-product__price">
                      <shopify-money query="product.selectedOrFirstAvailableVariant.price"></shopify-money>
                    </p>
                  </div>
                </button>
                <?php } else { ?>
                <a
                  shopify-attr--disabled="!product.availableForSale"
                  class="collection-grid__product"
                  shopify-attr--href="'/products/' + product.handle"
                >
                  <div class="single-product__image-container">
                    <shopify-media
                      width="242"
                      height="242"
                      max-images="1"
                      query="product.selectedOrFirstAvailableVariant.image"
                    ></shopify-media>
                  </div>
                  <div class="single-product__info">
                    <h3>
                      <shopify-data query="product.title"></shopify-data>
                    </h3>
                    <p class="single-product__price">
                      <shopify-money query="product.selectedOrFirstAvailableVariant.price"></shopify-money>
                    </p>
                  </div>
                </a>
                <?php } ?>
              </template>
            </shopify-list-context>
          </div>
        </div>
      </div>
    </template>
  </shopify-context>
</div>


<?php 
// Include the shared product modal from the main plugin with theme override support
    shopify_include_template('modal.php', SHOPIFY_PLUGIN_DIR . 'includes/components/frontend/modal.php');
?>

<!-- PDP functionality is now handled by shopify-pdp.js enqueued in frontend-assets.php -->
