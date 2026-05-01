<?php
if (!defined('ABSPATH')) {
    exit;
}

function render_setup_guide($admin_instance, $disabled = false) {
    $container_class = 'app-setup__container app-container';
    if ($disabled) {
        $container_class .= ' app-setup__container--disabled';
    }
    ?>
    <div class="<?php echo esc_attr($container_class); ?>">
        <div class="app-setup__content">
            <div class="app-setup__content__left">
                <?php
                require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/accordion.php';

                $setup_items = array(
                    array(
                        'icon' => wp_kses_post($admin_instance->get_svg_icon('assets/icons/mono-inverted-logo.svg')),
                        'title' => 'Add products on Shopify',
                        'description' => 'Write a description, add photos, and set pricing for the products you plan to sell on Shopify admin',
                        'cta' => array(
                            'url' => 'https://help.shopify.com/en/manual/custom-storefronts/storefront-api/getting-started',
                            'text' => __('Add products', 'shopify-plugin'),
                            'icon' => wp_kses_post($admin_instance->get_svg_icon('assets/icons/open.svg'))
                        )
                    ),
                    array(
                        'title' => __('Create a product page on WordPress', 'shopify-plugin'),
                        'description' => __('Your Shopify products will automatically sync with WordPress. Use the Shopify Product block to display products anywhere on your site.', 'shopify-plugin'),
                        'cta' => array(
                            'url' => 'https://wordpress.org/plugins/shopify/#description',
                            'text' => __('Create a store page', 'shopify-plugin')
                        )
                    ),
                    array(
                        'title' => __('Add products to your existing posts', 'shopify-plugin'),
                        'description' => __('Insert products you want to sell on existing pages and posts', 'shopify-plugin'),
                        'cta' => array(
                            'url' => '#',
                            'text' => __('Add products to posts', 'shopify-plugin')
                        )
                    )
                );

                $accordion = new Shopify_Accordion('Set up', $setup_items, 'setup-guide', true);
                $accordion->render();
                ?>
            </div>
            <div class="app-setup__content__right">
                <div class="app-setup__linklist">
                    <h3 class="app-setup__linklist__title">Resources</h3>
                    <ul>
                        <li><a href="#"><span class="app-setup__linklist__icon">
                            <?php echo wp_kses_post($admin_instance->get_svg_icon('assets/icons/clipboard.svg')); ?>
                        </span> <?php esc_html_e('Developer Guide', 'shopify-plugin'); ?></a></li>
                        <li><a href="#"><span class="app-setup__linklist__icon">
                            <?php echo wp_kses_post($admin_instance->get_svg_icon('assets/icons/help-circle.svg')); ?>
                        </span> <?php esc_html_e('Shopify Help Center', 'shopify-plugin'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
} 