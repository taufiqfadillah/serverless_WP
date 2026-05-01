<?php
if (!defined('ABSPATH')) {
    exit;
}

function render_dashboard_card_section() {
    ?>
<div class="dashboard-card-section app-container">
    <div class="dashboard-card-section__title-container">
        <h2 class="dashboard-card-section__title"><?php esc_html_e('Choose where to display your product', 'shopify-plugin'); ?></h2>
        <p class="dashboard-card-section__caption"><?php esc_html_e("Once you've added products to Shopify, there are two ways to display them using WordPress blocks.", 'shopify-plugin'); ?></p>
    </div>
    <div class="dashboard-card-section__cards">
        <div class="dashboard-card-section__card">
            <div class="dashboard-card-section__card__content__image">
                <img src="<?php echo esc_url(plugins_url('assets/images/example-1.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>" alt="Example 1">
            </div>
            <div class="dashboard-card-section__card__content">
                <h3><?php esc_html_e('Create a dedicated product page', 'shopify-plugin'); ?></h3>
                <p><?php esc_html_e('Showcase all your products and collections in one central place, and make it easy for your customers to browse everything you have to offer.', 'shopify-plugin'); ?></p>
                <a class="wp-connect-button" href="/wp-admin/post-new.php?post_type=page">Create a page</a>
            </div>
        </div>
        <div class="dashboard-card-section__card">
            <div class="dashboard-card-section__card__content__image">
                <img src="<?php echo esc_url(plugins_url('assets/images/example-2.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>" alt="Example 2">
            </div>
            <div class="dashboard-card-section__card__content">
                <h3><?php esc_html_e('Add products to a blog post', 'shopify-plugin'); ?></h3>
                <p><?php esc_html_e('Weave products and collections into any blog post with the Site Editor to create engaging, shoppable content that connects with your customers.', 'shopify-plugin'); ?></p>
                <a class="wp-connect-button" href="/wp-admin/edit.php"><?php esc_html_e('Choose blog post', 'shopify-plugin'); ?></a>
            </div>
        </div>
    </div>
</div>
<?php
}
?>