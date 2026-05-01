<?php
if (!defined('ABSPATH')) {
    exit;
}

function render_dashboard_header_banner() {
    $store_url = get_option('shopify_store_url');
    $api_key = get_option('shopify_api_key');
    $shop_info = get_option('shopify_shop_info');
    
    $has_credentials = !empty($store_url) && !empty($api_key);
    $has_shop_info = !empty($shop_info);
    $show_credentials_error = $has_credentials && !$has_shop_info;
    
    ?>
    
    <?php if ($show_credentials_error): ?>
        <div class="app-settings-form-container__credentials-error app-container">
            <p>
                <?php esc_html_e('Unable to connect to Shopify. Please check your access code.', 'shopify-plugin'); ?>
            </p>
        </div>
    <?php elseif (!$has_shop_info): ?>
        <div class="shop-info-notice app-container">
            <div class="shop-info-notice__content">
                <div class="shop-info-notice__text">
                    <p>Connect your WordPress site to Shopify to get started.</p>
                </div>
                <a class="wp-connect-button sm" href="<?php echo esc_url(admin_url('admin.php?page=shopify-settings')); ?>">Connect</a>
            </div>
        </div>
    <?php endif; ?>


<?php
}
?>