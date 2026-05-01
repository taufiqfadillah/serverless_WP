<?php
if (!defined('ABSPATH')) {
    exit;
}

function render_dashboard_footer() {
    ?>
<div class="dashboard-footer app-container">
    <div class="dashboard-footer__content">
        <div class="dashboard-footer__title">
            <h2><?php esc_html_e('Complete your Shopify setup', 'shopify-plugin'); ?></h2>
            <span>
                <img src="<?php echo esc_url(plugins_url('assets/images/shopify-glyf-color.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>" alt="Shopify Logo">
            </span>
        </div>
        <div class="dashboard-footer__caption">
            <p><?php esc_html_e('To start selling on WordPress, complete a few essential tasks in your', 'shopify-plugin'); ?>
            <?php 
            $shop_info = get_option( 'shopify_shop_info' );
            // Set fallback URL if shop_info is not available
            $url = 'https://admin.shopify.com/';
            if ($shop_info && isset($shop_info['primaryDomain']['url'])) {
                $url = $shop_info['primaryDomain']['url'] . '/admin';
            }
            ?>
            <a href="<?php echo esc_url($url); ?>" target="_blank"><?php esc_html_e('Shopify admin', 'shopify-plugin'); ?></a>.</p>
        </div>
    </div>
</div>
<?php
}
?>