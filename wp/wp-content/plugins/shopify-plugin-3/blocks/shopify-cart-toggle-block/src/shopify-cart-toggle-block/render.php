<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$toggleText = isset($attributes['toggleText']) ? $attributes['toggleText'] : 'View Cart';
$enableCartIcon = isset($attributes['enableCartIcon']) ? $attributes['enableCartIcon'] : true;

// Get current store URL from settings
$current_store_url = get_option('shopify_store_url', '');

// Handle no store
$is_no_store = empty($current_store_url);
if ($is_no_store) {
    echo '<div class="shopify-cart-block__error shopify-cart-block__error--no-cart">';
    echo '<p class="shopify-cart-block__error-message">Store is not connected.</p>';
    echo '</div>';
    return;
}

?>



<div <?php echo wp_kses_post(get_block_wrapper_attributes()); ?>>
    <div class="navigation-cart-block">
        <a class="navigation__view-cart-link" onclick="document.querySelector('shopify-cart').showModal()">
                            <div class="navigation__view-cart <?php echo esc_attr($enableCartIcon ? '' : 'no-icon'); ?>">
                <?php if ($enableCartIcon): ?>
                <shopify-context type="cart">
                <template>
                    <shopify-data query="cart.totalQuantity" class="navigation__view-cart__quantity"></shopify-data>
                </template>
                </shopify-context>
                <?php endif; ?>
                <span><?php echo esc_html($toggleText); ?></span>
            </div>
        </a>
    </div>
</div>