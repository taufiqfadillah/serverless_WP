<dialog id="product-modal" class="product-modal">
  <!-- The handle of this context is automatically set when the dialog is opened -->
  <shopify-context id="product-modal-context" type="product" wait-for-update>
    <template>
      <div class="product-modal__container">
        <div class="product-modal__close-container">
          <button class="product-modal__close" onclick="getElementById('product-modal').close();">&#10005;</button>
        </div>
        <div class="product-modal__content">
          <div class="product-modal__layout">
            <div class="product-modal__media">
              <shopify-media max-images="1" width="400" height="500" query="product.selectedOrFirstAvailableVariant.image"></shopify-media>
            </div>
            <div class="product-modal__details">
              <div class="product-modal__header">
                <h1 class="product-modal__title">
                  <shopify-data query="product.title"></shopify-data>
                </h1>
                <div class="product-modal__price-container">
                  <shopify-money query="product.selectedOrFirstAvailableVariant.price"></shopify-money>
                  <shopify-money
                    class="product-modal__compare-price"
                    query="product.selectedOrFirstAvailableVariant.compareAtPrice"
                  ></shopify-money>
                </div>
                <div class="product-modal__description-text">
                  <shopify-data query="product.descriptionHtml"></shopify-data>
                </div>
              </div>

              <shopify-variant-selector></shopify-variant-selector>

              <div class="product-modal__buttons">
                <button
                  class="product-modal__add-button"
                  onclick="getElementById('cart').addLine(event); getElementById('cart').showModal();getElementById('product-modal').close();"
                  shopify-attr--disabled="!product.selectedOrFirstAvailableVariant.product.availableForSale"
                >
                  Add to cart
                </button>
                <button
                  class="product-modal__buy-button"
                  onclick="document.querySelector('shopify-store').buyNow(event)"
                  class="product-buy-now__button"
                  shopify-attr--disabled="!product.selectedOrFirstAvailableVariant.product.availableForSale"
                >
                  Buy now
                </button>
                <a
                  class="product-modal__details-link"
                  shopify-attr--href="'/products/' + product.handle"
                >
				  <?php esc_html_e('See full product details', 'shopify-plugin'); ?>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </shopify-context>
</dialog>



<!-- Modal functionality is now handled by shopify-modal.js enqueued in frontend-assets.php -->
