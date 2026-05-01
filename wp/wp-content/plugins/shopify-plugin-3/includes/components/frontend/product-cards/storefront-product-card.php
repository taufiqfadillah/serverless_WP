<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('render_product_card_client')) {
    function render_product_card_client($product_handle, $has_context = true, $card_behavior = 'both') {
        // Check if product handle is provided
        if (empty($product_handle) && $has_context === false) {
            echo '<div class="product-card product-card--error">';
            echo '<p class="product-card__error-message">You must add a product.</p>';
            echo '</div>';
            return;
        }
        
        if ($has_context === false) { ?>
            <shopify-context type="product" handle="<?php echo esc_attr($product_handle); ?>">
                <template>
        <?php } ?>



        <div shopify-attr--disabled="!product.availableForSale" class="product-card">
            <div class="product-card__image-container">
            <?php if ($card_behavior === 'both' || $card_behavior === 'quick-shop-only') { ?>
            <button
                shopify-attr--disabled="!product.availableForSale"
                shopify-attr--test="!product.availableForSale"
                class="product-card__hover-button"
                onclick="getElementById('product-modal').showModal(); getElementById('product-modal-context').update(event);"
            >
                <span class="product-card__hover-button__button__text">Quick Shop</span>
            </button>
            <?php } ?>
            
            <?php if ($card_behavior === 'quick-shop-only') { ?>
            <a
                shopify-attr--disabled="!product.availableForSale"
                class="product__link"
                onclick="getElementById('product-modal').showModal(); getElementById('product-modal-context').update(event);"
                ><shopify-media
                max-images="1"
                query="product.selectedOrFirstAvailableVariant.image"
                ></shopify-media
            ></a>
            <?php } else { ?>
            <a
                shopify-attr--disabled="!product.availableForSale"
                class="product__link"
                shopify-attr--href="'/products/' + product.handle "
                ><shopify-media
                max-images="1"
                query="product.selectedOrFirstAvailableVariant.image"
                ></shopify-media
            ></a>
            <?php } ?>
            </div>
            
            <?php if ($card_behavior === 'quick-shop-only') { ?>
            <a
            shopify-attr--disabled="!product.availableForSale"
            class="product__link"
            onclick="getElementById('product-modal').showModal(); getElementById('product-modal-context').update(event);"
            >
            <?php } else { ?>
            <a
            shopify-attr--disabled="!product.availableForSale"
            class="product__link"
            shopify-attr--href="'/products/' + product.handle "
            >
            <?php } ?>
            <!-- Product information on the card -->
            <div class="product-card__details">
                <div class="product-card__info">
                <h3 class="product-card__title">
                    <span>
                    <shopify-data query="product.title"></shopify-data>
                    </span>
                </h3>
                </div>
                <p class="product-card__price">
                <shopify-money query="product.selectedOrFirstAvailableVariant.price"></shopify-money>
                </p>
            </div>
            </a>
        </div>
        <?php if (!$has_context) { ?>
            </template>
            </shopify-context>
        <?php } ?>


<?php
    }} ?>