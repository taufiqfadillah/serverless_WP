<?php
/**
 * Settings form snippet for Shopify WP Connect
 */

if (!defined('ABSPATH')) {
    exit;
}

function render_settings_form() {
    // CDN base path for assets
    $cdn_base_url = 'https://cdn.shopify.com/static/wordpress';

    // Add debug logging only if request is from an authorized user
    if (current_user_can('manage_options')) {
        error_log('=== SHOPIFY SETTINGS FORM RENDER START ===');
    }

    // Process Advanced Settings form submission
    // Check for either the submit button OR the presence of advanced settings fields
    $has_advanced_submission = (isset($_POST['submit_advanced_settings']) || isset($_POST['shopify_card_behavior']) || isset($_POST['shopify_enable_product_rewrites']) || isset($_POST['shopify_enable_collection_rewrites']) || isset($_POST['shopify_enable_analytics'])) && isset($_POST['shopify_access_code_nonce']);
    if ($has_advanced_submission) {
        error_log('Shopify Settings Form - Advanced settings form submission detected');
        if (wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['shopify_access_code_nonce'])), 'shopify_access_code_nonce')) {
            error_log('Shopify Settings Form - Advanced settings nonce verification passed');
            if (current_user_can('manage_options')) {
                error_log('Shopify Settings Form - Advanced settings user permissions verified');

                // Save collection rewrites setting
                $enable_collection_rewrites = isset($_POST['shopify_enable_collection_rewrites']) ? true : false;
                update_option('shopify_enable_collection_rewrites', $enable_collection_rewrites);
                error_log('Shopify Settings Form - Collection rewrites setting saved: ' . ($enable_collection_rewrites ? 'enabled' : 'disabled'));

                // Save product rewrites setting
                $enable_product_rewrites = isset($_POST['shopify_enable_product_rewrites']) ? true : false;
                update_option('shopify_enable_product_rewrites', $enable_product_rewrites);
                error_log('Shopify Settings Form - Product rewrites setting saved: ' . ($enable_product_rewrites ? 'enabled' : 'disabled'));


                // Save card behavior
                $card_behavior = isset($_POST['shopify_card_behavior']) ? sanitize_text_field($_POST['shopify_card_behavior']) : 'both';
                update_option('shopify_card_behavior', $card_behavior);
                error_log('Shopify Settings Form - Card behavior saved: ' . $card_behavior);

                // Save analytics consent setting
                $enable_analytics = isset($_POST['shopify_enable_analytics']) ? true : false;
                update_option('shopify_enable_analytics', $enable_analytics);
                error_log('Shopify Settings Form - Analytics consent saved: ' . ($enable_analytics ? 'enabled' : 'disabled'));

                // Set success message flag for display
                $advanced_settings_success = true;

                // Flush rewrite rules to apply any permalink changes (if enabled by flag)
                if (defined('SHOPIFY_WP_CONNECT_AUTO_FLUSH_PERMALINKS') && SHOPIFY_WP_CONNECT_AUTO_FLUSH_PERMALINKS) {
                    error_log('Shopify Settings Form - About to flush rewrite rules');
                    error_log('Shopify Settings Form - Product rewrites enabled: ' . (get_option('shopify_enable_product_rewrites', true) ? 'YES' : 'NO'));
                    error_log('Shopify Settings Form - Collection rewrites enabled: ' . (get_option('shopify_enable_collection_rewrites', true) ? 'YES' : 'NO'));
                    error_log('Shopify Settings Form - Global rewrites enabled: ' . (get_option('shopify_enable_rewrites', true) ? 'YES' : 'NO'));

                    // Use both methods to ensure rewrite rules are flushed
                    flush_rewrite_rules();
                    global $wp_rewrite;
                    $wp_rewrite->flush_rules(true);
                    error_log('Shopify Settings Form - Permalink rewrite rules flushed using both methods');

                    // Verify the rules were actually flushed by checking current rewrite rules
                    global $wp_rewrite;
                    $current_rules = $wp_rewrite->wp_rewrite_rules();
                    $has_product_rules = false;
                    $has_collection_rules = false;

                    foreach ($current_rules as $pattern => $replacement) {
                        if (strpos($pattern, '^products/') === 0) {
                            $has_product_rules = true;
                        }
                        if (strpos($pattern, '^collections/') === 0) {
                            $has_collection_rules = true;
                        }
                    }

                    error_log('Shopify Settings Form - After flush - Product rules present: ' . ($has_product_rules ? 'YES' : 'NO'));
                    error_log('Shopify Settings Form - After flush - Collection rules present: ' . ($has_collection_rules ? 'YES' : 'NO'));
                } else {
                    error_log('Shopify Settings Form - Automatic permalink flushing disabled by flag');
                }

                error_log('Shopify Settings Form - Advanced settings saved successfully');
            } else {
                error_log('Shopify Settings Form - Advanced settings user permissions failed');
            }
        } else {
            error_log('Shopify Settings Form - Advanced settings nonce verification failed');
        }
    }

    // Check for connection error from transient (set by admin_init handler after failed submission)
    $connection_error = get_transient('shopify_connection_error');
    if ($connection_error) {
        $connection_failed = true;
        $connection_error_message = $connection_error;
        delete_transient('shopify_connection_error');
        error_log('Shopify Settings Form - Displaying connection error from transient: ' . $connection_error_message);
    }

    // Check if we have credentials but no shop info (indicating invalid credentials)
    $store_url = get_option('shopify_store_url');
    $api_key = get_option('shopify_api_key');
    $shop_info = get_option('shopify_shop_info');
    $access_code = get_option('shopify_for_wordpress_access_code');

    // Debug logging to verify settings are being retrieved
    error_log('Shopify Settings Form - Retrieved settings:');
    error_log('Shopify Settings Form - Store URL: ' . ($store_url ? $store_url : 'NOT SET'));
    error_log('Shopify Settings Form - API Key: ' . ($api_key ? substr($api_key, 0, 10) . '...' : 'NOT SET'));
    error_log('Shopify Settings Form - Access Code: ' . ($access_code ? substr($access_code, 0, 10) . '...' : 'NOT SET'));
    error_log('Shopify Settings Form - Shop Info: ' . ($shop_info ? print_r($shop_info, true) : 'NOT SET'));

    $has_credentials = !empty($store_url) && !empty($api_key);
    $has_shop_info = !empty($shop_info);
    $has_access_code = !empty($access_code);

    // Show error only if connection just failed in this request
    $show_credentials_error = isset($connection_failed) && $connection_failed;

    // Initialize success message flags if not set
    $advanced_settings_success = isset($advanced_settings_success) ? $advanced_settings_success : false;
    $disconnect_success = isset($disconnect_success) ? $disconnect_success : false;

    // Check for connection success transient
    $connection_success = get_transient('shopify_connection_success');
    if ($connection_success) {
        delete_transient('shopify_connection_success'); // Clear it after reading
    }

    ?>


    <div class="shopify-form app-container app-settings-form-container">
        <!-- Tab Navigation -->
        <div class="shopify-tabs">
            <button type="button" class="shopify-tab-button active" data-tab="general"><?php esc_html_e('General', 'shopify-plugin'); ?></button>
            <button type="button" class="shopify-tab-button" data-tab="advanced"><?php esc_html_e('Advanced', 'shopify-plugin'); ?> </button>
        </div>

        <?php if ($show_credentials_error): ?>
            <div class="app-settings-form-container__credentials-error">
                <p>
                    <?php esc_html_e("Unable to connect. Please confirm your Shopify access token is correct.", "shopify-plugin"); ?>
                </p>

            </div>
        <?php endif; ?>

        <?php if ($advanced_settings_success): ?>
            <div class="app-settings-form-container__success-message">
                <p>
                    <?php esc_html_e('Advanced settings saved successfully!', 'shopify-plugin'); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($disconnect_success): ?>
            <div class="app-settings-form-container__success-message">
                <p>
                    <?php esc_html_e('Store disconnected successfully!', 'shopify-plugin'); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($connection_success): ?>
            <div class="app-settings-form-container__success-message">
                <p>
                    <?php esc_html_e('Successfully connected.', 'shopify-plugin'); ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- Tab Content -->
        <div id="general-tab" class="shopify-tab-content active">
            <div class="app-settings-form-container__content blue-bg">
        <form method="post" action="">
                <?php wp_nonce_field('shopify_access_code_nonce', 'shopify_access_code_nonce'); ?>
                <h2><?php esc_html_e("Connect Shopify to your WordPress site", "shopify"); ?></h2>
                <p class="app-settings-form-container__content__description lg"><?php esc_html_e("To find your access token, go to the Sell on WordPress sales channel in Shopify.", "shopify-plugin"); ?></p>

                <div class="form-fields">
                    <div class="form-field">
                        <label class="small" for="shopify_for_wordpress_access_code"><?php esc_html_e("Shopify access token", "shopify-plugin"); ?></label>
                        <input type="text"
                               id="shopify_for_wordpress_access_code"
                               name="shopify_for_wordpress_access_code"
                               value="<?php echo esc_attr(get_option('shopify_for_wordpress_access_code')); ?>"
                               placeholder="Paste your access token here"
                               class="regular-text"
                               <?php if ($has_shop_info) { echo 'readonly'; } ?> />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="submit-container" <?php if ($has_shop_info) { echo 'style="display: none;"'; } ?>>
                    <input type="submit" name="submit_access_code" id="submit-access-code" class="wp-connect-button inverse" value="<?php echo esc_html__('Connect', 'shopify-plugin'); ?>">
                </div>

                <?php if ($has_shop_info): ?>
                <!-- Disconnect Button -->
                <div class="submit-container" style="margin-top: 10px;">
                    <button type="button" id="disconnect-store" class="wp-connect-button">
                        <?php esc_html_e('Disconnect', 'shopify-plugin'); ?>
                    </button>
                </div>
                <?php endif; ?>
            </form>

            <div class="art-container">
                <img src="<?php echo esc_url(plugins_url('assets/images/illustration-updated.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>" alt="Shopify Connect">
            </div>
        </div>

        <!-- Disconnect Confirmation Modal -->
        <?php if ($has_shop_info): ?>
        <div id="disconnect-modal" class="shopify-disconnect-modal" style="display: none;">
            <div class="shopify-disconnect-modal__backdrop"></div>
            <div class="shopify-disconnect-modal__content">
                <div class="shopify-disconnect-modal__header">
                    <h2 class="shopify-disconnect-modal__title"><?php esc_html_e('Disconnect from Shopify?', 'shopify-plugin'); ?></h2>
                    <button type="button" class="shopify-disconnect-modal__close" id="disconnect-modal-close">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M9.05973 7.99997L15.5297 1.52997L14.4697 0.469971L7.99973 6.93997L1.52973 0.469971L0.469727 1.52997L6.93973 7.99997L0.469727 14.47L1.52973 15.53L7.99973 9.05997L14.4697 15.53L15.5297 14.47L9.05973 7.99997Z" fill="#1E1E1E"/>
                        </svg>
                    </button>
                </div>

                <div class="shopify-disconnect-modal__body">
                    <p class="shopify-disconnect-modal__caption">
                        <?php esc_html_e('All products published to your WordPress site through Shopify will be removed and Shopify checkout will no longer be available.', 'shopify-plugin'); ?>
                    </p>
                </div>

                <div class="shopify-disconnect-modal__footer">
                    <button type="button" class="wp-connect-button" id="disconnect-modal-cancel">
                        <?php esc_html_e('Cancel', 'shopify-plugin'); ?>
                    </button>
                    <button type="button" class="wp-connect-button inverse" id="disconnect-modal-confirm">
                        <?php esc_html_e('Disconnect from Shopify', 'shopify-plugin'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Advanced Tab Content -->
     <div class="shopify-tab-content" id="advanced-tab">

        <!-- Products and Collections Display Customization Section (Informational Only) -->
        <div class="advanced-tab-content">
            <div class="form-group">
                <h2 class="form-group-title"><?php esc_html_e("Products and collections display customization", "shopify"); ?></h2>
                <p class="app-settings-form-container__content__description" style="margin-bottom: 0;"><?php esc_html_e("You can also customize how your product card, collection, quick view popup, detailed product page looks by creating a file in your theme.", "shopify"); ?> <a href="admin.php?page=shopify-documentation#accordion-shopify-products-and-collections-display-customization"><?php esc_html_e("Learn more", "shopify"); ?></a> <?php esc_html_e("on how to do this.", "shopify"); ?></p>
            </div>
        </div>

        <!-- Main Settings Form -->
        <div class="advanced-tab-content" style="margin-top: 20px;">
            <form method="post" action="">
                <?php wp_nonce_field('shopify_access_code_nonce', 'shopify_access_code_nonce'); ?>

        <div class="form-group">
            <h2><?php esc_html_e("Shopify links rewrites ", "shopify-plugin"); ?></h2>
            <p class="app-settings-form-container__content__description"><?php esc_html_e("Automatically generate a WordPress page for every published Shopify product or collection pages. Navigate to ", "shopify-plugin"); ?> <a href="/wp-admin/options-permalink.php"><?php esc_html_e("Permalinks", "shopify-plugin"); ?></a> <?php esc_html_e("to apply new rules.", "shopify-plugin"); ?> <a href="admin.php?page=shopify-documentation#accordion-shopify-products-and-collections-rewrites"><?php esc_html_e("Learn more", "shopify-plugin"); ?></a></p>


            <div class="form-fields">
                <div class="form-field">
                    <div class="checkbox-wrapper">
                        <input type="checkbox"
                            id="shopify_enable_product_rewrites"
                            name="shopify_enable_product_rewrites"
                            value="1"
                            <?php checked(get_option('shopify_enable_product_rewrites', true)); ?> />
                        <label for="shopify_enable_product_rewrites"><?php esc_html_e("Shopify to WordPress product sync", "shopify-plugin"); ?></label>
                    </div>
                    <p class="description with-margin">
                        <?php esc_html_e('Automatically create a product detailed page on WordPress for every active product on Shopify. ', "shopify-plugin"); ?>
                    </p>
                </div>
                <div class="form-field">
                    <div class="checkbox-wrapper">
                        <input type="checkbox"
                            id="shopify_enable_collection_rewrites"
                            name="shopify_enable_collection_rewrites"
                            value="1"
                            <?php checked(get_option('shopify_enable_collection_rewrites', true)); ?> />
                        <label for="shopify_enable_collection_rewrites"><?php esc_html_e("Shopify to WordPress collection sync", "shopify-plugin"); ?></label>
                    </div>
                    <p class="description with-margin"><?php esc_html_e("Automatically create a collection page on WordPress for every active collection on Shopify. ", "shopify-plugin"); ?>
                </div>
                </div>



                <div class="form-group">
                    <h2 class="form-group-title"><?php esc_html_e("Product view", "shopify"); ?></h2>
                    <p class="app-settings-form-container__content__description"><?php esc_html_e("Choose how you want customers to view your products. This setting applies to all product cards across your site.", "shopify"); ?> <a href="admin.php?page=shopify-documentation#accordion-customize-your-product-card-behaviour"><?php esc_html_e("Learn more", "shopify"); ?></a></p>

                    <div class="form-fields radio-group">
                        <div class="form-field radio-field">
                            <div class="radio-wrapper">
                                <input type="radio"
                                       id="shopify_card_behavior_both"
                                       name="shopify_card_behavior"
                                       value="both"
                                       <?php checked(get_option('shopify_card_behavior', 'both'), 'both'); ?> />
                                <label for="shopify_card_behavior_both"><?php esc_html_e("Show both views (default)", "shopify"); ?></label>
                            </div>
                            <p class="description with-margin">
                                <?php esc_html_e("Customers can use quick view for a summary, or open a full product page.", "shopify"); ?>
                            </p>
                        </div>

                        <div class="form-field radio-field">
                            <div class="radio-wrapper">
                                <input type="radio"
                                       id="shopify_card_behavior_quick_only"
                                       name="shopify_card_behavior"
                                       value="quick-shop-only"
                                       <?php checked(get_option('shopify_card_behavior', 'both'), 'quick-shop-only'); ?> />
                                <label for="shopify_card_behavior_quick_only"><?php esc_html_e("Quick view only", "shopify"); ?></label>
                            </div>
                            <p class="description with-margin">
                                <?php esc_html_e("Customers see a summary of each product in a popup.", "shopify"); ?> <button type="button" class="quick-view-example-btn" style="background: none; border: none; color: #3858e9; text-decoration: underline; cursor: pointer; padding: 0; font: inherit;"><?php esc_html_e("Example", "shopify"); ?></button>
                            </p>
                        </div>

                        <div class="form-field radio-field">
                            <div class="radio-wrapper">
                                <input type="radio"
                                       id="shopify_card_behavior_pdp_only"
                                       name="shopify_card_behavior"
                                       value="pdp-links-only"
                                       <?php checked(get_option('shopify_card_behavior', 'both'), 'pdp-links-only'); ?> />
                                <label for="shopify_card_behavior_pdp_only"><?php esc_html_e("Detailed product page only", "shopify"); ?></label>
                            </div>
                            <p class="description with-margin">
                                <?php esc_html_e("Each product has its own detailed product page with a unique URL.", "shopify"); ?> <button type="button" class="pdp-example-btn" style="background: none; border: none; color: #3858e9; text-decoration: underline; cursor: pointer; padding: 0; font: inherit;"><?php esc_html_e("Example", "shopify"); ?></button>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <h2 class="form-group-title"><?php esc_html_e("Usage tracking", "shopify-plugin"); ?></h2>
                    <div class="form-fields">
                        <div class="form-field">
                            <div class="checkbox-wrapper">
                                <input type="checkbox"
                                    id="shopify_enable_analytics"
                                    name="shopify_enable_analytics"
                                    value="1"
                                    <?php checked(get_option('shopify_enable_analytics', false)); ?> />
                                <label for="shopify_enable_analytics"><?php esc_html_e("Allow usage of Shopify for WordPress Plugin to be tracked", "shopify-plugin"); ?></label>
                            </div>
                            <p class="description with-margin">
                                <?php esc_html_e("To opt out, leave this box unchecked. Your usage will remain untracked, and no data will be collected. For more information on what data is being tracked, please review the", "shopify-plugin"); ?> <a href="https://www.shopify.com/ca/legal/privacy" target="_blank" rel="noopener noreferrer"><?php esc_html_e("Shopify Privacy Policy", "shopify-plugin"); ?></a>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button for Advanced Tab -->
                <div class="submit-container pt-md">
                    <input type="submit" name="submit_advanced_settings" id="submit-advanced-settings" class="wp-connect-button inverse" value="<?php esc_attr_e('Save', 'shopify'); ?>" disabled>
                </div>
            </form>
        </div>

    </div>

            </div>

    <!-- Quick View Example Modal -->
    <div id="quick-view-example-modal" class="quick-view-image-modal" style="display: none;">
        <div class="quick-view-image-modal__backdrop"></div>
        <div class="quick-view-image-modal__content">
            <button type="button" class="quick-view-image-modal__close" id="quick-view-example-modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M13.06 12L19.53 5.52997L18.47 4.46997L12 10.94L5.52997 4.46997L4.46997 5.52997L10.94 12L4.46997 18.47L5.52997 19.53L12 13.06L18.47 19.53L19.53 18.47L13.06 12Z" fill="#1E1E1E"/>
                </svg>
            </button>
            <div class="quick-view-image-modal__header">
                <h2 class="quick-view-image-modal__title"><?php esc_html_e('Quick view example', 'shopify-plugin'); ?></h2>
            </div>
            <div class="quick-view-image-modal__image">
                <img src="<?php echo esc_url($cdn_base_url . '/quick-view-example.png'); ?>" alt="Quick view example">
            </div>
        </div>
    </div>

    <!-- PDP Example Modal -->
    <div id="pdp-example-modal" class="quick-view-image-modal" style="display: none;">
        <div class="quick-view-image-modal__backdrop"></div>
        <div class="quick-view-image-modal__content">
            <button type="button" class="quick-view-image-modal__close" id="pdp-example-modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M13.06 12L19.53 5.52997L18.47 4.46997L12 10.94L5.52997 4.46997L4.46997 5.52997L10.94 12L4.46997 18.47L5.52997 19.53L12 13.06L18.47 19.53L19.53 18.47L13.06 12Z" fill="#1E1E1E"/>
                </svg>
            </button>
            <div class="quick-view-image-modal__header">
                <h2 class="quick-view-image-modal__title"><?php esc_html_e('Product detail page example', 'shopify-plugin'); ?></h2>
            </div>
            <div class="quick-view-image-modal__image">
                <img src="<?php echo esc_url($cdn_base_url . '/detail-product-page-example.png'); ?>" alt="Product detail page example">
            </div>
        </div>
    </div>

    <?php
    wp_add_inline_script(
        'shopify-settings-form',
        "window.ajaxurl = '" . esc_url(admin_url('admin-ajax.php')) . "';"
    );

    wp_add_inline_script(
        'shopify-settings-form',
        "window.shopifyPageviewNonce = '" . esc_js(wp_create_nonce('shopify_pageview_nonce')) . "';"
    );

    wp_add_inline_script(
        'shopify-settings-form',
        "window.shopifyAccessCodeNonce = '" . esc_js(wp_create_nonce('shopify_access_code_nonce')) . "';"
    );

    wp_add_inline_script(
        'shopify-settings-form',
        "window.shopifyConnectText = '" . esc_js(__('Connect', 'shopify-plugin')) . "';"
    );
    ?>
    <?php
}

/**
 * Function to handle Shopify access code submission
 */
function handle_shopify_access_code_submission($access_code) {
    error_log('=== SHOPIFY ACCESS CODE SUBMISSION START ===');
    error_log('Shopify Access Code Submission - Access code: ' . substr($access_code, 0, 10) . '...');

    // Check database connection
    global $wpdb;
    if ($wpdb->last_error) {
        error_log('Shopify Access Code Submission - Database error: ' . $wpdb->last_error);
    } else {
        error_log('Shopify Access Code Submission - Database connection OK');
    }

    // Test database write capability
    error_log('Shopify Access Code Submission - Testing database write capability...');
    $test_option_name = 'shopify_test_write_' . time();
    $test_write_result = update_option($test_option_name, 'test_value');
    error_log('Shopify Access Code Submission - Test write result: ' . ($test_write_result ? 'SUCCESS' : 'FAILED'));

    // Test direct database write
    global $wpdb;
    $direct_test_result = $wpdb->insert(
        $wpdb->options,
        array(
            'option_name' => 'shopify_direct_test_' . time(),
            'option_value' => 'direct_test_value',
            'autoload' => 'no'
        ),
        array('%s', '%s', '%s')
    );
    error_log('Shopify Access Code Submission - Direct database write result: ' . ($direct_test_result ? 'SUCCESS' : 'FAILED'));
    if (!$direct_test_result && $wpdb->last_error) {
        error_log('Shopify Access Code Submission - Direct database error: ' . $wpdb->last_error);
    }

    // Clean up test options
    delete_option($test_option_name);
    $wpdb->delete($wpdb->options, array('option_name' => 'shopify_direct_test_' . time()));

    // Handle empty access code (clearing the value)
    if (empty($access_code)) {
        error_log('Shopify Access Code Submission - Empty access code, clearing data');
        // Clear the access code and related data
        delete_option('shopify_for_wordpress_access_code');
        delete_option('shopify_store_url');
        delete_option('shopify_api_key');
        delete_option('shopify_shop_info');

        return array(
            'success' => true,
            'message' => __('Access code cleared successfully.', 'shopify-plugin')
        );
    }

    // Sanitize the access code
    $sanitized_access_code = sanitize_text_field($access_code);

    // Always save the access code first
    error_log('Shopify Access Code Submission - Saving access code to database...');
    error_log('Shopify Access Code Submission - Access code length: ' . strlen($sanitized_access_code));
    error_log('Shopify Access Code Submission - Access code value: ' . substr($sanitized_access_code, 0, 50) . '...');

    // Try add_option first, then update_option if it exists
    $existing_access_code = get_option('shopify_for_wordpress_access_code');
    if ($existing_access_code === false) {
        $access_code_saved = add_option('shopify_for_wordpress_access_code', $sanitized_access_code);
        error_log('Shopify Access Code Submission - Using add_option for access code');
    } else {
        $access_code_saved = update_option('shopify_for_wordpress_access_code', $sanitized_access_code);
        error_log('Shopify Access Code Submission - Using update_option for access code');
        // update_option returns false if value hasn't changed, but this is not a failure
        if ($access_code_saved === false && $existing_access_code === $sanitized_access_code) {
            $access_code_saved = true; // Consider it successful if value is the same
            error_log('Shopify Access Code Submission - Access code unchanged, treating as success');
        }
    }
    error_log('Shopify Access Code Submission - Access code save result: ' . ($access_code_saved ? 'SUCCESS' : 'FAILED'));
    if (!$access_code_saved) {
        error_log('Shopify Access Code Submission - Access code save failed');
        if ($wpdb->last_error) {
            error_log('Shopify Access Code Submission - Database error after access code save: ' . $wpdb->last_error);
        }
    }

    // Make server-side fetch request to your external API
    $api_response = submit_access_code_to_external_api($sanitized_access_code);

    if ($api_response['success']) {
        error_log('Shopify Access Code Submission - API call successful, saving credentials...');
        // Save the store URL and API key
        error_log('Shopify Access Code Submission - Saving store URL: ' . $api_response['myshopify_url']);
        $existing_store_url = get_option('shopify_store_url');
        if ($existing_store_url === false) {
            $saved_store_url = add_option('shopify_store_url', $api_response['myshopify_url']);
            error_log('Shopify Access Code Submission - Using add_option for store URL');
        } else {
            $saved_store_url = update_option('shopify_store_url', $api_response['myshopify_url']);
            error_log('Shopify Access Code Submission - Using update_option for store URL');
            // update_option returns false if value hasn't changed, but this is not a failure
            if ($saved_store_url === false && $existing_store_url === $api_response['myshopify_url']) {
                $saved_store_url = true; // Consider it successful if value is the same
                error_log('Shopify Access Code Submission - Store URL unchanged, treating as success');
            }
        }
        error_log('Shopify Access Code Submission - Store URL save result: ' . ($saved_store_url ? 'SUCCESS' : 'FAILED'));
        if (!$saved_store_url) {
            error_log('Shopify Access Code Submission - Store URL save failed');
            if ($wpdb->last_error) {
                error_log('Shopify Access Code Submission - Database error after store URL save: ' . $wpdb->last_error);
            }
        }

        error_log('Shopify Access Code Submission - Saving API key: ' . substr($api_response['storefront_access_token'], 0, 10) . '...');
        $existing_api_key = get_option('shopify_api_key');
        if ($existing_api_key === false) {
            $saved_api_key = add_option('shopify_api_key', $api_response['storefront_access_token']);
            error_log('Shopify Access Code Submission - Using add_option for API key');
        } else {
            $saved_api_key = update_option('shopify_api_key', $api_response['storefront_access_token']);
            error_log('Shopify Access Code Submission - Using update_option for API key');
            // update_option returns false if value hasn't changed, but this is not a failure
            if ($saved_api_key === false && $existing_api_key === $api_response['storefront_access_token']) {
                $saved_api_key = true; // Consider it successful if value is the same
                error_log('Shopify Access Code Submission - API key unchanged, treating as success');
            }
        }
        error_log('Shopify Access Code Submission - API key save result: ' . ($saved_api_key ? 'SUCCESS' : 'FAILED'));
        if (!$saved_api_key) {
            error_log('Shopify Access Code Submission - API key save failed');
            if ($wpdb->last_error) {
                error_log('Shopify Access Code Submission - Database error after API key save: ' . $wpdb->last_error);
            }
        }

        // Check if the important credentials were saved successfully
        $credentials_saved = $saved_store_url && $saved_api_key;

        // Verify settings were actually saved by retrieving them
        $verify_store_url = get_option('shopify_store_url');
        $verify_api_key = get_option('shopify_api_key');
        $verify_access_code = get_option('shopify_for_wordpress_access_code');

        error_log('Shopify Access Code Submission - Verification after save:');
        error_log('Shopify Access Code Submission - Store URL saved: ' . ($verify_store_url ? $verify_store_url : 'NOT FOUND'));
        error_log('Shopify Access Code Submission - API Key saved: ' . ($verify_api_key ? substr($verify_api_key, 0, 10) . '...' : 'NOT FOUND'));
        error_log('Shopify Access Code Submission - Access Code saved: ' . ($verify_access_code ? substr($verify_access_code, 0, 10) . '...' : 'NOT FOUND'));

        // Also check directly from database to bypass any caching
        global $wpdb;
        $db_store_url = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'shopify_store_url'));
        $db_api_key = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'shopify_api_key'));
        $db_access_code = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'shopify_for_wordpress_access_code'));
        $db_shop_info = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", 'shopify_shop_info'));

        error_log('Shopify Access Code Submission - Database verification:');
        error_log('Shopify Access Code Submission - DB Store URL: ' . ($db_store_url ? $db_store_url : 'NOT FOUND IN DB'));
        error_log('Shopify Access Code Submission - DB API Key: ' . ($db_api_key ? substr($db_api_key, 0, 10) . '...' : 'NOT FOUND IN DB'));
        error_log('Shopify Access Code Submission - DB Access Code: ' . ($db_access_code ? substr($db_access_code, 0, 10) . '...' : 'NOT FOUND IN DB'));
        error_log('Shopify Access Code Submission - DB Shop Info: ' . ($db_shop_info ? $db_shop_info : 'NOT FOUND IN DB'));

        if ($credentials_saved) {
            // Now validate the credentials to get shop info
            require_once SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php';
            $validation_result = Shopify_Wp_GraphQL::validate_store_credentials($api_response['myshopify_url'], $api_response['storefront_access_token']);

            if (is_wp_error($validation_result)) {
                // Clear store credentials since validation failed, but keep the access code
                error_log('Shopify Access Code Submission - Validation failed, clearing store credentials but keeping access code');
                delete_option('shopify_store_url');
                delete_option('shopify_api_key');
                delete_option('shopify_shop_info');
                return array(
                    'success' => false,
                    'message' => sprintf(__e('Access code validated but failed to connect to Shopify: %s', 'shopify-plugin'), $validation_result->get_error_message())
                );
            } else {
                // Save the shop info - use the legacy format for compatibility
                $shop_info = $validation_result['shop'];
                $shop_info['has_wordpress_plugin_signup'] = $api_response['has_wordpress_plugin_signup'] ?? false;

                $shop_info_saved = update_option('shopify_shop_info', $shop_info, false);
                error_log('Shopify Access Code Submission - Shop info save result: ' . ($shop_info_saved ? 'SUCCESS' : 'FAILED'));
                error_log('Shopify Access Code Submission - Shop info being saved: ' . print_r($shop_info, true));

                return array(
                    'success' => true,
                    'message' => __('Access code validated and store connected successfully!', 'shopify-plugin'),
                    'shop_info' => $shop_info
                );
            }
        } else {
            error_log('Shopify Access Code Submission - ERROR: Credentials failed to save');
            error_log('Shopify Access Code Submission - Store URL save: ' . ($saved_store_url ? 'SUCCESS' : 'FAILED'));
            error_log('Shopify Access Code Submission - API key save: ' . ($saved_api_key ? 'SUCCESS' : 'FAILED'));
            // Clear store credentials since save failed, but keep the access code
            error_log('Shopify Access Code Submission - Save failed, clearing store credentials but keeping access code');
            delete_option('shopify_store_url');
            delete_option('shopify_api_key');
            delete_option('shopify_shop_info');
            return array(
                'success' => false,
                'message' => __('Access code validated but failed to save store connection locally.', 'shopify-plugin')
            );
        }
    } else {
        // API call failed, clear store credentials but keep the access code
        error_log('Shopify Access Code Submission - API call failed, clearing store credentials but keeping access code');
        delete_option('shopify_store_url');
        delete_option('shopify_api_key');
        delete_option('shopify_shop_info');

        return array(
            'success' => false,
            'message' => $api_response['message'] ?? __('Failed to submit access code to external service.', 'shopify-plugin')
        );
    }
}



/**
 * Function to submit access code to external API
 * Replace this with your actual API endpoint and logic
 */
function submit_access_code_to_external_api($access_code) {
    error_log('=== SHOPIFY EXTERNAL API SUBMISSION START ===');
    error_log('Shopify External API - Access code: ' . substr($access_code, 0, 10) . '...');

    // Your API endpoint URL
    $api_url = 'https://wordpress-sales-channel.shopify.io/api/connect';
    error_log('Shopify External API - Endpoint: ' . $api_url);

    // Prepare the request data to match your server's expected format
    $request_data = array(
        'install_code' => $access_code, // Your server expects 'install_code'
        'wp_site_url' => get_site_url(),
        'wp_admin_url' => admin_url(),
        'wp_site_name' => html_entity_decode(get_bloginfo('name'), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
    );
    error_log('Shopify External API - Request data: ' . print_r($request_data, true));

    // Make the HTTP request
    error_log('Shopify External API - Making HTTP request...');
    $request_start_time = microtime(true);
    $response = wp_remote_post($api_url, array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($request_data),
        'timeout' => 30, // 30 second timeout
        'sslverify' => true, // Verify SSL certificates
    ));
    $request_duration = round((microtime(true) - $request_start_time) * 1000, 2);
    error_log('Shopify External API - Request completed in ' . $request_duration . 'ms');

    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        $error_code = $response->get_error_code();
        error_log('Shopify External API - HTTP Error: ' . $error_message . ' (Code: ' . $error_code . ')');
        error_log('Shopify External API - Full WP_Error details: ' . print_r($response->get_error_messages(), true));

        // Log additional context about the request
        error_log('Shopify External API - Request URL: ' . $api_url);
        error_log('Shopify External API - Request timeout: 30 seconds');
        error_log('Shopify External API - SSL verify: enabled');

        return array(
            'success' => false,
            'message' => 'Network error: ' . $error_message . ' (Error code: ' . $error_code . ')'
        );
    }

    // Get response code and body
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    error_log('Shopify External API - Response code: ' . $response_code);
    error_log('Shopify External API - Response body: ' . $response_body);

    // Parse JSON response if applicable
    $response_data = json_decode($response_body, true);

    // Extract response fields based on your server's response structure
    $api_success = false;
    $api_message = '';
    $storefront_access_token = '';
    $myshopify_url = '';
    $has_wordpress_plugin_signup = false;

    if ($response_code === 200 && $response_data) {
        $api_success = true;
        $api_message = 'Install code validated successfully';
        $storefront_access_token = $response_data['storefront_access_token'] ?? '';
        $myshopify_url = $response_data['myshopify_url'] ?? '';
        $has_wordpress_plugin_signup = $response_data['has_wordpress_plugin_signup'] ?? false;
    }

    // Handle different response codes
    if ($response_code === 200) {
        error_log('Shopify External API - Success response (200)');
        error_log('Shopify External API - Storefront token: ' . substr($storefront_access_token, 0, 10) . '...');
        error_log('Shopify External API - Shopify URL: ' . $myshopify_url);

        // Validate that we received the required data
        if (empty($storefront_access_token) || empty($myshopify_url)) {
            error_log('Shopify External API - ERROR: Missing required data in response');
            error_log('Shopify External API - Missing storefront_access_token: ' . (empty($storefront_access_token) ? 'YES' : 'NO'));
            error_log('Shopify External API - Missing myshopify_url: ' . (empty($myshopify_url) ? 'YES' : 'NO'));
            return array(
                'success' => false,
                'message' => 'Server response missing required store credentials'
            );
        }

        return array(
            'success' => $api_success,
            'message' => $api_message ?: 'Access code submitted successfully',
            'data' => $response_data,
            // Include extracted variables in return for immediate use
            'storefront_access_token' => $storefront_access_token,
            'myshopify_url' => $myshopify_url,
            'has_wordpress_plugin_signup' => $has_wordpress_plugin_signup
        );
    } else {
        // Enhanced error logging for different response codes
        error_log('Shopify External API - Error response code: ' . $response_code);
        error_log('Shopify External API - Response headers: ' . print_r(wp_remote_retrieve_headers($response), true));

        $detailed_error_message = '';
        switch ($response_code) {
            case 400:
                $detailed_error_message = 'Bad request - Invalid access token format or missing data';
                break;
            case 401:
                $detailed_error_message = 'Unauthorized - Access token is invalid or expired';
                break;
            case 403:
                $detailed_error_message = 'Forbidden - Access token does not have required permissions';
                break;
            case 404:
                $detailed_error_message = 'Not found - Shopify store or access token not found';
                break;
            case 422:
                $detailed_error_message = 'Validation error - Access token format is invalid';
                break;
            case 429:
                $detailed_error_message = 'Rate limited - Too many connection attempts, please try again later';
                break;
            case 500:
                $detailed_error_message = 'Internal server error - Shopify connection service is experiencing issues';
                break;
            case 502:
            case 503:
            case 504:
                $detailed_error_message = 'Service unavailable - Shopify connection service is temporarily down';
                break;
            default:
                $detailed_error_message = "Unexpected error code: $response_code";
        }

        error_log('Shopify External API - Detailed error: ' . $detailed_error_message);

        // Try to extract error message from response if it's JSON
        if ($response_data && isset($response_data['error'])) {
            $server_error = $response_data['error'];
            error_log('Shopify External API - Server error message: ' . $server_error);
            $detailed_error_message .= ' - ' . $server_error;
        } elseif ($response_data && isset($response_data['message'])) {
            $server_message = $response_data['message'];
            error_log('Shopify External API - Server message: ' . $server_message);
            $detailed_error_message .= ' - ' . $server_message;
        }

        return array(
            'success' => false,
            'message' => $detailed_error_message
        );
    }
}

