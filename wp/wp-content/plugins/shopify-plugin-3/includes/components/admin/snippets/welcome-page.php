<?php
/**
 * Welcome Page Component
 *
 * Renders the welcome page for new users with setup instructions and quick actions.
 */

if (!defined('ABSPATH')) {
    exit;
}



function render_welcome_page() {
    ?>
    <div class="shopify-form app-container app-welcome-page-container">
        <div class="app-welcome-page-container__content blue-bg">
            <div class="welcome-layout">
                <div class="welcome-content">
                    <h1><?php esc_html_e('Connect Shopify to WordPress', 'shopify-plugin'); ?></h1>
                    <p class="welcome-caption"><?php esc_html_e('Start selling by connecting Shopify to your WordPress site. Not on Shopify yet? Start your free trial today.', 'shopify-plugin'); ?></p>

                    <div class="welcome-actions">
                        <a href="https://admin.shopify.com/signup?signup_types[]=shopify_for_wordpress_plugin&utm_source=wordpress&utm_medium=plugin&utm_campaign=wordpress_connector_q225lkpehj" class="wp-connect-button primary" target="_blank" data-shopify-redirect-action="signup">
                            <?php esc_html_e('Start free trial', 'shopify-plugin'); ?>
                        </a>
                        <a href="https://admin.shopify.com/?utm_source=wordpress&utm_medium=plugin&utm_campaign=wordpress_connector_q225lkpehj&no_redirect=true&redirect=%2Foauth%2Finstall%3Fclient_id%3D0d20f725fd4f9d9d95215e5d4fcf426d" class="wp-connect-button secondary" target="_blank" data-shopify-redirect-action="connect">
                            <?php esc_html_e('Connect your store', 'shopify-plugin'); ?>
                        </a>
                    </div>

                    <p class="welcome-footer">
                        <?php esc_html_e('Shopify Partner? ', 'shopify-plugin'); ?> <a href="/wp-admin/admin.php?page=shopify-documentation#accordion-shopify-partners"><?php esc_html_e('Learn how to get started', 'shopify-plugin'); ?></a>
                    </p>
                </div>

                <div class="welcome-image">
                    <?php
                    $locale = get_locale();
                    $cdn_base_url = 'https://cdn.shopify.com/static/wordpress/';
                    $image_filename = (strpos($locale, 'en_US') === 0) ? 'welcome.gif' : 'welcome-alt.gif';
                    $image_url = $cdn_base_url . $image_filename;
                    ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="Shopify Connect">
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome page styles are now properly enqueued via wp_enqueue_style() in admin-settings.php -->
    <!-- See: assets/css/welcome-page.css -->

    <?php
    $welcome_script = '
        (function() {
            document.addEventListener("DOMContentLoaded", function() {
                // Handle welcome page button redirects
                const shopifyRedirectButtons = document.querySelectorAll("[data-shopify-redirect-action]");

                shopifyRedirectButtons.forEach(function(button) {
                    button.addEventListener("click", function(e) {
                        e.preventDefault();

                        // Open Shopify link in new tab
                        window.open(this.href, "_blank");

                        // Redirect current tab to settings page
                        window.location.href = "' . esc_url(admin_url('admin.php?page=shopify-settings')) . '";
                    });
                });
            });
        })();
    ';
    wp_add_inline_script('shopify-admin', $welcome_script);
}
