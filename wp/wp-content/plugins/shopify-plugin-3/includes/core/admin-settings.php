<?php
/**
 * Admin settings for Shopify WP Connect
 *
 * This file handles all WordPress admin interface functionality for the Shopify WP Connect plugin.
 * It manages the admin menu, settings registration, form handling, and UI rendering.
 *
 * Key responsibilities:
 * - Creates and manages the admin menu structure
 * - Handles settings registration and validation
 * - Processes form submissions (connection testing, settings updates)
 * - Renders the admin interface pages (dashboard, settings, help)
 * - Manages CSS/JS asset loading for admin pages
 * - Provides connection status and store information display
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main admin class for Shopify WP Connect plugin
 *
 * This class encapsulates all admin-related functionality including:
 * - Menu creation and management
 * - Settings registration and validation
 * - Form processing and AJAX handling
 * - Admin page rendering
 * - Asset management for admin interface
 */
class Shopify_Admin {

    /**
     * Constructor - Sets up all admin hooks and actions
     *
     * Initializes the admin interface by registering WordPress hooks for:
     * - Admin menu creation
     * - Settings registration
     * - Plugin action links
     * - Admin asset loading
     * - Form processing handlers
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_filter('plugin_action_links_' . plugin_basename(SHOPIFY_PLUGIN_DIR . 'shopify.php'), array($this, 'add_settings_link'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_init', array($this, 'handle_settings_validation'));
        add_action('admin_init', array($this, 'handle_welcome_redirect'));
        add_action('admin_init', array($this, 'check_connection_health'));
        add_action('admin_init', array($this, 'handle_access_code_submission'));

        // Add AJAX handlers for pageview tracking
        add_action('wp_ajax_shopify_track_pageview', array($this, 'ajax_track_pageview'));
        add_action('wp_ajax_nopriv_shopify_track_pageview', array($this, 'ajax_track_pageview'));

        // Add AJAX handler for setup progress
        add_action('wp_ajax_shopify_save_setup_progress', array($this, 'save_shopify_setup_progress'));

        // Add AJAX handler for clearing access code
        add_action('wp_ajax_shopify_clear_access_code', array($this, 'clear_shopify_access_code'));
    }

    /**
     * Handles redirect to welcome page after plugin activation
     */
    public function handle_welcome_redirect() {
        // Sanitize GET parameters
        $dismiss_welcome = isset($_GET['dismiss_welcome']);
        $wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $current_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

        // Handle dismiss welcome page
        if ($dismiss_welcome && $wpnonce && wp_verify_nonce($wpnonce, 'dismiss_welcome')) {
            delete_option('shopify_show_welcome');
            wp_redirect(admin_url('admin.php?page=shopify'));
            exit;
        }

        // Check if we should show the welcome page
        if (get_option('shopify_show_welcome', false)) {

            // Only redirect if we're on the plugins page after activation (immediate post-activation)
            if ($current_page === 'plugins' && $current_action === 'activate') {
                // Clear the flag immediately to prevent multiple redirects
                delete_option('shopify_show_welcome');

                // Redirect to welcome page
                wp_redirect(admin_url('admin.php?page=shopify-welcome'));
                exit;
            }

            // If user manually visits the main plugin page and welcome flag is set, clear it without redirecting
            if ($current_page === 'shopify') {
                delete_option('shopify_show_welcome');
            }
        }
    }

    /**
     * Handles access code form submission
     *
     * This method processes the Shopify access code form submission on the settings page.
     */
    public function handle_access_code_submission() {
        // Only process on settings page to avoid running on every admin page
        $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        if ($current_page !== 'shopify-settings') {
            return;
        }

        // Check for access code submission
        $has_access_code_submission = (isset($_POST['submit_access_code']) || isset($_POST['shopify_for_wordpress_access_code'])) && isset($_POST['shopify_access_code_nonce']);
        if (!$has_access_code_submission) {
            return;
        }

        error_log('Shopify Settings Form - POST data detected, processing form submission');

        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['shopify_access_code_nonce'])), 'shopify_access_code_nonce')) {
            error_log('Shopify Settings Form - Nonce verification failed');
            return;
        }

        error_log('Shopify Settings Form - Nonce verification passed');

        if (!current_user_can('manage_options')) {
            error_log('Shopify Settings Form - User permissions failed');
            return;
        }

        error_log('Shopify Settings Form - User permissions verified');

        $access_code = isset($_POST['shopify_for_wordpress_access_code']) ? sanitize_text_field($_POST['shopify_for_wordpress_access_code']) : '';
        error_log('Shopify Settings Form - Access code: ' . substr($access_code, 0, 10) . '...');

        // Check if this is a disconnect request (empty access code)
        if (empty($access_code)) {
            error_log('Shopify Settings Form - Disconnect request detected, clearing all credentials');

            // Use shared disconnect function
            require_once SHOPIFY_PLUGIN_DIR . 'includes/core/utils.php';
            shopify_wp_connect_perform_disconnect(true);

            // Redirect back to settings page
            wp_redirect(admin_url('admin.php?page=shopify-settings'));
            exit;
        }

        // Clear shop info immediately at the start of form processing
        delete_option('shopify_shop_info');
        error_log('Shopify Settings Form - Cleared shop info at start of form processing');

        // Process the access code submission
        require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/snippets/settings-form.php';
        $result = handle_shopify_access_code_submission($access_code);
        error_log('Shopify Settings Form - Submission result: ' . print_r($result, true));

        $store_url = get_option('shopify_store_url');

        if ($result['success']) {
            error_log('Shopify Settings Form - Submission successful, restoring shop info and redirecting');

            // Restore shop info for successful submission
            if (isset($result['shop_info'])) {
                update_option('shopify_shop_info', $result['shop_info'], false);
                error_log('Shopify Settings Form - Restored shop info for successful submission');
            }

            // Track connection success
            require_once SHOPIFY_PLUGIN_DIR . 'includes/core/analytics.php';
            track_shopify_connection_success(array(
                'store_url' => $store_url,
                'shop_name' => $result['shop_info']['name'],
                'shop_domain' => $result['shop_info']['primaryDomain']['host'],
            ), $result['shop_info']);

            // Set transient to show success message after redirect
            set_transient('shopify_connection_success', true, 30);

            // Redirect to the Home page using proper wp_redirect
            wp_redirect(admin_url('admin.php?page=shopify'));
            exit;
        } else {
            error_log('Shopify Settings Form - Submission failed: ' . $result['message']);

            // Track connection error
            require_once SHOPIFY_PLUGIN_DIR . 'includes/core/analytics.php';
            track_shopify_connection_error(array('access_code' => $access_code, 'message' => $result['message']));

            // Set transient to show error message on next page load
            set_transient('shopify_connection_error', $result['message'], 30);

            // Redirect back to settings page to display error
            wp_redirect(admin_url('admin.php?page=shopify-settings'));
            exit;
        }
    }


    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets($hook) {
        // Only load assets on our plugin's admin pages
        $shopify_pages = array(
            'toplevel_page_shopify',           // Main dashboard page
            'shopify_page_shopify-settings',   // Settings page
            'shopify_page_shopify-welcome',    // Welcome page
            'shopify_page_shopify-documentation' // Documentation page
        );

        // Check if we're on one of our plugin's pages
        if (!in_array($hook, $shopify_pages, true)) {
            // We're not on a Shopify plugin page, don't load any assets
            return;
        }

        // We're on a Shopify plugin page, load all assets
        // Enqueue admin styles
        wp_enqueue_style(
            'shopify-admin',
            plugins_url('assets/css/base.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array(),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0'
        );

        // Enqueue dashboard card section styles
        wp_enqueue_style(
            'shopify-dashboard-card-section',
            plugins_url('assets/css/dashboard-card-section.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('shopify-admin'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0'
        );

        // Enqueue documentation styles
        wp_enqueue_style(
            'shopify-documentation',
            plugins_url('assets/css/documentation.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('shopify-admin'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0'
        );

        // Enqueue admin scripts
        wp_enqueue_script(
            'shopify-admin',
            plugins_url('assets/js/admin.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('jquery'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0',
            true
        );

        // Enqueue documentation scripts
        wp_enqueue_script(
            'shopify-documentation',
            plugins_url('assets/js/documentation.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('jquery'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0',
            true
        );

        // Enqueue dashboard accordion styles
        wp_enqueue_style(
            'shopify-dashboard-accordion',
            plugins_url('assets/css/dashboard-accordion.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('shopify-admin'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0'
        );

        // Enqueue dashboard accordion scripts
        wp_enqueue_script(
            'shopify-dashboard-accordion',
            plugins_url('assets/js/dashboard-accordion.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('jquery'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0',
            true
        );

        // Enqueue welcome page styles
        wp_enqueue_style(
            'shopify-welcome-page',
            plugins_url('assets/css/welcome-page.css', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('shopify-admin'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0'
        );

        // Enqueue settings form scripts
        wp_enqueue_script(
            'shopify-settings-form',
            plugins_url('assets/js/settings-form.js', SHOPIFY_PLUGIN_DIR . 'shopify.php'),
            array('jquery'),
            defined('shopify_VERSION') ? shopify_VERSION : '1.0.0',
            true
        );

        // Localize script to pass nonces and data to JavaScript
        wp_localize_script('shopify-settings-form', 'shopifySettings', array(
            'clearAccessCodeNonce' => wp_create_nonce('clear_access_code_nonce')
        ));
    }

    /**
     * Add menu icon styles in the admin head
     * This is called on all admin pages to ensure the menu icon displays correctly
     * The styles are highly scoped to only affect the Shopify menu item
     */
    public function add_menu_icon_styles() {
        ?>
        <style type="text/css">
            /* Shopify menu icon styles - scoped to #adminmenu #toplevel_page_shopify only */
            #adminmenu #toplevel_page_shopify .wp-menu-image::before {
                content: '';
                background: url('<?php echo esc_url(plugins_url('assets/icons/menu-icon.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>') no-repeat center;
                background-size: 16px;
                width: 20px;
                display: inline-block;
                vertical-align: middle;
                position: relative;
                top: 6px;
                padding: 0;
            }
            #adminmenu #toplevel_page_shopify:hover .wp-menu-image::before {
                background-image: url('<?php echo esc_url(plugins_url('assets/icons/menu-icon-active.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>');
            }
            #adminmenu #toplevel_page_shopify.current .wp-menu-image::before {
                background-image: url('<?php echo esc_url(plugins_url('assets/icons/menu-icon-active.png', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>');
            }
            <?php if (defined('SHOPIFY_WP_CONNECT_SHOW_WELCOME') && !SHOPIFY_WP_CONNECT_SHOW_WELCOME): ?>
            /* Hide welcome page from menu when disabled */
            #adminmenu .wp-submenu a[href*="shopify-welcome"] {
                display: none !important;
            }
            <?php endif; ?>
        </style>
        <?php
    }

    /**
     * Adds a settings link to the plugin's action links in the plugins list
     *
     * This creates a convenient "Settings" link that appears next to the plugin
     * in the WordPress admin plugins page.
     *
     * @param array $links Array of existing plugin action links
     * @return array Modified array with settings link added
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=shopify-settings')) . '">' . __('Settings', 'shopify-plugin') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Creates the admin menu structure for the plugin
     *
     * Sets up the main menu page and submenu items including:
     * - Main dashboard page
     * - Settings page
     * - Help page
     *
     * Also adds custom CSS for the menu icon to display properly.
     */
    public function add_admin_menu() {
        // Add the main menu page with custom icon
        add_menu_page(
            'Shopify Dashboard',
            'Shopify',
            'manage_options',
            'shopify',
            array($this, 'render_dashboard_page'),
            'dashicons-store',
            30
        );

        // Add menu icon styles that need to be available on all admin pages
        // This is highly scoped to only affect the Shopify menu item
        add_action('admin_head', array($this, 'add_menu_icon_styles'));

        // Add submenu items for different sections
        add_submenu_page(
            'shopify',
            'Home',
            'Home',
            'manage_options',
            'shopify', // Same slug as main menu to override the automatic duplicate
            array($this, 'render_dashboard_page')
        );

        add_submenu_page(
            'shopify',
            'Settings',
            'Settings',
            'manage_options',
            'shopify-settings',
            array($this, 'render_settings_page')
        );

        // Always register the welcome page, but hide it from menu if disabled
        add_submenu_page(
            'shopify',
            'Welcome',
            'Welcome',
            'manage_options',
            'shopify-welcome',
            array($this, 'render_welcome_page')
        );

        // Add Documentation page
        add_submenu_page(
            'shopify',
            'Documentation',
            'Documentation',
            'manage_options',
            'shopify-documentation',
            array($this, 'render_documentation_page')
        );

        // Add custom CSS for the menu icon to display custom SVG icons
        // This action is now handled by enqueue_admin_assets
    }

    /**
     * Registers all plugin settings with WordPress
     *
     * Defines all the settings options that can be stored in the WordPress options table.
     * Each setting includes default values, data types, and sanitization callbacks where needed.
     */
    public function register_settings() {
        // Enable/disable analytics setting
        register_setting('shopify_options', 'shopify_enable_analytics', array(
            'default' => false,
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));

        // Access code setting - the Shopify access code from WordPress sales channel
        register_setting('shopify_options', 'shopify_for_wordpress_access_code', array(
            'default' => '',
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Store URL setting - the Shopify store domain
        register_setting('shopify_options', 'shopify_store_url', array(
            'default' => '',
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // API key setting - the Shopify access token
        register_setting('shopify_options', 'shopify_api_key', array(
            'default' => '',
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Shop info setting - read-only store information from Shopify API
        register_setting('shopify_options', 'shopify_shop_info', array(
            'default' => null,
            'type' => 'object',
            'sanitize_callback' => array($this, 'sanitize_shop_info')
        ));

        // Enable rewrites setting - controls URL rewriting functionality
        register_setting('shopify_options', 'shopify_enable_rewrites', array(
            'default' => true,
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));



        // Enable collection rewrites setting
        register_setting('shopify_options', 'shopify_enable_collection_rewrites', array(
            'default' => true,
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));

        // Enable product rewrites setting
        register_setting('shopify_options', 'shopify_enable_product_rewrites', array(
            'default' => true,
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));



        // Show welcome setting
        register_setting('shopify_options', 'shopify_show_welcome', array(
            'default' => false,
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));

        // Country setting
        register_setting('shopify_options', 'shopify_country', array(
            'default' => '',
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Language setting
        register_setting('shopify_options', 'shopify_language', array(
            'default' => '',
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Card behavior setting - controls how product cards behave when clicked
        register_setting('shopify_options', 'shopify_card_behavior', array(
            'default' => 'both',
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field'
        ));

    }

    /**
     * Sanitizes the shop info object to ensure data integrity
     *
     * @param mixed $input The input value to sanitize
     * @return array|null Sanitized shop info array or null if invalid
     */
    public function sanitize_shop_info($input) {
        error_log('Shopify Sanitize Shop Info - Input: ' . print_r($input, true));

        // If input is null or empty, return null
        if (empty($input)) {
            error_log('Shopify Sanitize Shop Info - Input is empty, returning null');
            return null;
        }

        // Ensure input is an array
        if (!is_array($input)) {
            error_log('Shopify Sanitize Shop Info - Input is not an array, returning null');
            return null;
        }

        // Handle both formats: direct shop data or wrapped in 'shop' key
        $shop_data = null;
        if (isset($input['shop']) && is_array($input['shop'])) {
            // New format: wrapped in 'shop' key
            $shop_data = $input['shop'];
            error_log('Shopify Sanitize Shop Info - Using wrapped shop format');
        } elseif (isset($input['id']) && isset($input['name']) && isset($input['primaryDomain'])) {
            // Legacy format: direct shop data
            $shop_data = $input;
            error_log('Shopify Sanitize Shop Info - Using direct shop format');
        } else {
            error_log('Shopify Sanitize Shop Info - Input does not have valid shop structure, returning null');
            return null;
        }

        // Validate required shop fields
        if (!isset($shop_data['id']) || !isset($shop_data['name']) || !isset($shop_data['primaryDomain'])) {
            error_log('Shopify Sanitize Shop Info - Missing required shop fields, returning null');
            return null;
        }

        if (!is_array($shop_data['primaryDomain']) || !isset($shop_data['primaryDomain']['url']) || !isset($shop_data['primaryDomain']['host'])) {
            error_log('Shopify Sanitize Shop Info - Missing required primaryDomain fields, returning null');
            return null;
        }

        // Sanitize individual fields using WordPress functions
        // Always return in the legacy format (direct shop data) for compatibility with existing UI
        $sanitized = array(
            'id' => sanitize_text_field($shop_data['id']),
            'name' => sanitize_text_field($shop_data['name']),
            'primaryDomain' => array(
                'url' => esc_url_raw($shop_data['primaryDomain']['url']),
                'host' => sanitize_text_field($shop_data['primaryDomain']['host'])
            )
        );

        // Add has_wordpress_plugin_signup if present
        if (isset($shop_data['has_wordpress_plugin_signup'])) {
            $sanitized['has_wordpress_plugin_signup'] = rest_sanitize_boolean($shop_data['has_wordpress_plugin_signup']);
        }

        error_log('Shopify Sanitize Shop Info - Successfully sanitized: ' . print_r($sanitized, true));
        return $sanitized;
    }

    /**
     * Handles settings validation after form submission
     *
     * Automatically validates Shopify credentials when settings are saved.
     * This ensures that the stored credentials are always valid and updates
     * the shop information accordingly.
     */
    public function handle_settings_validation() {
        // Sanitize GET parameters
        $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        $settings_updated = isset($_GET['settings-updated']) ? sanitize_text_field($_GET['settings-updated']) : '';

        // Only run on our plugin's settings page
        if (!$current_page || $current_page !== 'shopify-settings') {
            return;
        }

        // Check if settings were just saved
        if (!$settings_updated || $settings_updated !== 'true') {
            return;
        }

        // Verify nonce for settings update
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'shopify_options-options')) {
            return;
        }

        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get the current store URL and API key from saved settings
        $store_url = get_option('shopify_store_url');
        $api_key = get_option('shopify_api_key');

        // If either field is empty, clear the shop info and don't validate
        if (empty($store_url) || empty($api_key)) {
            delete_option('shopify_shop_info');
            return;
        }

        // Validate the credentials using the GraphQL API
        require_once SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php';
        $result = shopify_GraphQL::validate_store_credentials($store_url, $api_key);

        if (is_wp_error($result)) {
            // Track connection error
            track_shopify_connection_error(array(
                'error_message' => $result->get_error_message(),
                'error_code' => $result->get_error_code(),
                'store_url' => $store_url,
            ));

            // Display error message and clear invalid shop info
            add_settings_error(
                'shopify_options',
                'shopify_validation_error',
                $result->get_error_message(),
                'error'
            );
            // Clear the shop info on validation failure
            delete_option('shopify_shop_info');
        } else {
            // If validation successful, store the shop info as a read-only setting
            update_option('shopify_shop_info', $result['shop'], false);

            // Immediately register the hooks (don't wait for next page load)
            if (function_exists('shopify_register_cart_toggle_hooks')) {
                shopify_register_cart_toggle_hooks();
            }

            add_settings_error(
                'shopify_options',
                'shopify_validation_success',
                'Store connected successfully! You can now view your dashboard.',
                'success'
            );
        }
    }

    /**
     * Helper method to get SVG icon content with theme compatibility
     *
     * Reads SVG files and modifies them for better theme compatibility
     * by replacing black fills with currentColor.
     *
     * @param string $icon_path Path to the SVG file relative to plugin directory
     * @return string SVG content or empty string if file doesn't exist
     */
    public function get_svg_icon($icon_path) {
        $svg_path = SHOPIFY_PLUGIN_DIR . $icon_path;
        if (file_exists($svg_path)) {
            $svg_content = file_get_contents($svg_path);
            // Replace black fill with currentColor for theme compatibility
            $svg_content = str_replace('fill="black"', 'fill="currentColor"', $svg_content);
            return $svg_content;
        }
        return '';
    }

    /**
     * Renders the reusable store header with connection status and navigation
     *
     * This method provides a consistent header across all admin pages that displays:
     * - Store connection status (connected/not connected)
     * - Store name and domain when connected
     * - Navigation menu with appropriate actions based on connection status
     *
     * @param array|null $shop_info Optional shop info array. If not provided, will fetch from options.
     * @param boolean $show_credentials_error Optional flag to show credentials error state
     */
    public function render_store_header($shop_info = null, $show_credentials_error = false) {
        // If shop_info is not provided, get it from options
        if ($shop_info === null) {
            $shop_info = get_option('shopify_shop_info');
        }
        ?>
        <!-- Store header with connection status and navigation -->
        <div class="app-header app-container">
        <?php if ($shop_info && !$show_credentials_error): ?>
                <!-- Connected store display -->
                <div class="app-header__store-name--connected">
                    <div class="app-header__store-name">
                        <h1><?php echo esc_html($shop_info['name']); ?></h1>
                        <span class="app-header__badge"><span class="app-header__badge__icon">
                            <?php include SHOPIFY_PLUGIN_DIR . 'assets/icons/check-circle.svg'; ?>
                        </span><?php esc_html_e('Connected to Shopify', 'shopify-plugin'); ?></span>
                    </div>
                    <div class="app-header__store-info">
                        <p class="store-domain"><?php echo esc_html($shop_info['primaryDomain']['host']); ?></p>
                    </div>
                    </div>
                <?php else: ?>
                    <!-- No store connected display -->
                    <div class="app-header__store-name">
                        <h1>Shopify</h1>
                        <div class="app-header__no-store">
                            <p> <span class="app-header__badge__icon">
                            <?php include SHOPIFY_PLUGIN_DIR . 'assets/icons/caution.svg'; ?>
                            </span><?php esc_html_e('Not connected', 'shopify-plugin'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

            <!-- Navigation menu for different sections -->
            <div class="app-header__menu-actions">
                <ul>
                    <?php if ($shop_info): ?>
                    <li>
                        <a class="wp-connect-button w-icon" target="_blank" href="<?php echo esc_url($shop_info['primaryDomain']['url']); ?>/admin">
                            <?php esc_html_e('Shopify admin', 'shopify-plugin'); ?>
                            <span class="app-header__menu-actions__icon">
                                <?php include SHOPIFY_PLUGIN_DIR . 'assets/icons/open.php'; ?>
                            </span>
                        </a>
                    </li>
                    <?php else: ?>
                        <li>
                            <a target="_blank" class="wp-connect-button inverse" href="https://admin.shopify.com/signup?signup_types[]=shopify_for_wordpress_plugin&utm_source=wordpress&utm_medium=plugin&utm_campaign=wordpress_connector_q225lkpehj"><?php esc_html_e('Start free Shopify trial', 'shopify-plugin'); ?></a>
                        </li>
                        <li>
                            <a class="wp-connect-button" target="_blank" href="https://admin.shopify.com/">Log in</a>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the main dashboard page
     *
     * This is the primary admin page that shows the dashboard with store information
     * and navigation. It includes the store header and dashboard content.
     *
     * Features:
     * - Store connection status display
     * - Dashboard content with store statistics
     * - Navigation to other pages
     */
    public function render_dashboard_page() {
        // Track pageview for home page
        $page_url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        track_pageview_event($page_url, 'home');

        $shop_info = get_option('shopify_shop_info');
        $store_url = get_option('shopify_store_url');
        $api_key = get_option('shopify_api_key');
        $access_code = get_option('shopify_access_code');

        $has_credentials = !empty($store_url) && !empty($api_key);
        $has_shop_info = !empty($shop_info);
        $has_access_code = !empty($access_code);
        $has_wordpress_plugin_signup = self::get_has_wordpress_plugin_signup();

        $show_credentials_error = $has_access_code && (!$has_credentials || !$has_shop_info);
        ?>


        <div class="wrap">

        <!-- other plugins will inject their own content here -->
                <div><h1></h1></div>
                    <div></div>
        <!-- end other plugins content -->


            <?php $this->render_store_header($shop_info, $show_credentials_error); ?>

            <!-- Dashboard content -->
            <div class="dashboard-content">
                    <div class="dashboard-stats">

                        <?php
                        require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/snippets/dashboard/dashboard-header-banner.php';
                        render_dashboard_header_banner();
                        ?>
                        <?php
                        require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/snippets/dashboard/dashboard-accordion.php';

                        // Define the accordion data
                        $title = "Complete your Shopify setup";

                        // Set fallback URL if shop_info is not available
                        $admin_url = "https://admin.shopify.com/";
                        if ($shop_info && isset($shop_info['primaryDomain']['host'])) {
                            $admin_url = "https://" . $shop_info['primaryDomain']['host'] . "/admin";
                        }

                        $caption = __("To start selling on WordPress, you'll need to complete a few essential tasks in your", 'shopify-plugin') . " <a href='" . $admin_url . "' target='_blank' class='shopify-admin-link'>" . __('Shopify admin', 'shopify-plugin') . "</a>.";

                        // Build action URLs with proper fallbacks
                        $products_url = 'https://admin.shopify.com/';
                        $payments_url = 'https://admin.shopify.com/';
                        $shipping_url = 'https://admin.shopify.com/';
                        $redirect_url = 'https://admin.shopify.com/apps/sell-on-wordpress';
                        $plan_url = 'https://admin.shopify.com/';
                        $themes_url = 'https://admin.shopify.com/themes';

                        if ($shop_info && isset($shop_info['primaryDomain']['host'])) {
                            $host = $shop_info['primaryDomain']['host'];
                            $products_url = 'https://' . $host . '/admin/products';
                            $payments_url = 'https://' . $host . '/admin/settings/payments';
                            $shipping_url = 'https://' . $host . '/admin/settings/shipping/profiles/';
                            $redirect_url = 'https://' . $host . '/admin/apps/sell-on-wordpress/app';
                            $plan_url = 'https://' . $host . '/admin/settings/subscribe/select-plan';
                            $themes_url = 'https://' . $host . '/admin/themes';
                        }

                        // Get saved setup progress
                        $saved_progress = get_option('shopify_setup_progress', array());

                        if ($has_wordpress_plugin_signup) {
                            $items = array(
                                array(
                                    'id' => 'add-products',
                                    'checked' => isset($saved_progress['add-products']) ? $saved_progress['add-products'] : false,
                                    'heading' => __('Add products', 'shopify-plugin'),
                                    'caption' => __('To display products on your WordPress site, add products or collections in Shopify.', 'shopify-plugin'),
                                    'action_url' => $products_url,
                                    'fallback_action_url' => 'https://admin.shopify.com/',
                                    'action_text' => __('Add products', 'shopify-plugin'),
                                    'action_icon' => 'open'
                                ),
                                array(
                                    'id' => 'setup-payments',
                                    'checked' => isset($saved_progress['setup-payments']) ? $saved_progress['setup-payments'] : false,
                                    'heading' => __('Setup Shopify Payments', 'shopify-plugin'),
                                    'caption' => __('Provide a few details to activate Shopify Payments and start accepting all major payment methods.', 'shopify-plugin'),
                                    'action_url' => $payments_url,
                                    'fallback_action_url' => 'https://admin.shopify.com/',
                                    'action_text' => __('Complete account setup', 'shopify-plugin'),
                                    'action_icon' => 'open'
                                ),
                                array(
                                    'id' => 'review-shipping',
                                    'checked' => isset($saved_progress['review-shipping']) ? $saved_progress['review-shipping'] : false,
                                    'heading' => __('Review shipping rates', 'shopify-plugin'),
                                    'caption' => __('Kickstart your shipping strategy by reviewing rates that have already been set based on your location.', 'shopify-plugin'),
                                    'action_url' => $shipping_url,
                                    'fallback_action_url' => 'https://admin.shopify.com',
                                    'action_text' => __('Review rates', 'shopify-plugin'),
                                    'action_icon' => 'open'
                                ),
                                array(
                                    'id' => 'setup-redirect',
                                    'checked' => isset($saved_progress['setup-redirect']) ? $saved_progress['setup-redirect'] : false,
                                    'heading' => __('Manage checkout traffic', 'shopify-plugin'),
                                    'caption' => __('Decide where customers go after checkout.', 'shopify-plugin'),
                                    'action_url' => $redirect_url,
                                    'fallback_action_url' => 'https://admin.shopify.com',
                                    'action_text' => __('Setup redirect', 'shopify-plugin'),
                                    'action_icon' => 'open'
                                ),
                                array(
                                    'id' => 'pick-plan',
                                    'checked' => isset($saved_progress['pick-plan']) ? $saved_progress['pick-plan'] : false,
                                    'heading' => 'Pick a plan',
                                    'caption' => 'To start selling on WordPress, choose a Shopify plan and remove your Shopify store password.',
                                    'action_url' => $plan_url,
                                    'fallback_action_url' => 'https://admin.shopify.com/',
                                    'action_text' => 'Choose plan',
                                    'action_icon' => 'open'
                                )
                            );
                        } else {
                            $shop_domain = ($shop_info && isset($shop_info['primaryDomain']['host']))
                                ? $shop_info['primaryDomain']['host']
                                : 'myshopify.com';
                            $wordpress_url = parse_url(home_url(), PHP_URL_HOST);
                            $theme_caption = sprintf(
                                __('Publish the Sell on WordPress theme to redirect all Shopify URLs to their WordPress equivalents. Your Shopify domain %s will redirect to %s.', 'shopify-plugin'),
                                $shop_domain,
                                $wordpress_url
                            );

                            $items = array(
                                array(
                                    'id' => 'connect-shopify-store',
                                    'checked' => isset($saved_progress['connect-shopify-store']) ? $saved_progress['connect-shopify-store'] : false,
                                    'heading' => __('Connect to Shopify store', 'shopify-plugin'),
                                    'caption' => sprintf(
                                        __('To start selling, copy your access token from the Sell on WordPress sales channel in Shopify and paste it in the plugin\'s %s.', 'shopify-plugin'),
                                        '<a href="' . admin_url('admin.php?page=shopify-settings') . '" class="shopify-admin-link">' . __('Settings page on WordPress', 'shopify-plugin') . '</a>'
                                    ),
                                    'action_url' => $redirect_url,
                                    'fallback_action_url' => 'https://admin.shopify.com/apps/sell-on-wordpress',
                                    'action_text' => __('Connect', 'shopify-plugin'),
                                    'action_icon' => 'open'
                                ),
                                array(
                                    'id' => 'publish-wordpress-theme',
                                    'checked' => isset($saved_progress['publish-wordpress-theme']) ? $saved_progress['publish-wordpress-theme'] : false,
                                    'heading' => __('Make WordPress your storefront (optional)', 'shopify-plugin'),
                                    'caption' => $theme_caption,
                                    'action_url' => $themes_url,
                                    'fallback_action_url' => 'https://admin.shopify.com/themes',
                                    'action_text' => __('Publish theme', 'shopify-plugin'),
                                    'action_icon' => 'open'
                                )
                            );
                        }

                        render_dashboard_accordion($title, $caption, $items, 'shopify-setup-accordion', true, 'shopify-glyf-color.png');
                        // Add inline script for setup progress nonce
                        wp_add_inline_script(
                            'shopify-dashboard-accordion',
                            "window.shopifySetupNonce = '" . esc_js(wp_create_nonce('shopify_setup_progress')) . "';"
                        );
                        ?>
                        <?php
                        require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/snippets/dashboard/dashboard-card-section.php';
                        render_dashboard_card_section();
                        ?>
                    </div>

            </div>
        </div>
        <?php
    }

    /**
     * Renders the Settings page
     *
     * This is a dedicated settings page that shows the settings form
     * and setup guide. It includes the store header and settings content.
     */
    public function render_settings_page() {
        // Track pageview for general settings page
        $page_url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        track_pageview_event($page_url, 'settings', 'general');

        $shop_info = get_option('shopify_shop_info');
        ?>
        <div class="wrap">
            <div><h1></h1></div>
            <div></div>
            <?php $this->render_store_header($shop_info); ?>

            <!-- Settings form and setup guide -->
            <?php
            require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/snippets/settings-form.php';
            render_settings_form();
            ?>

        </div>
        <?php
    }

    /**
     * Renders the Welcome page
     *
     * This is the welcome page that users see when they first activate the plugin.
     * It provides an overview of the plugin features and getting started guide.
     */
    public function render_welcome_page() {
        // Track pageview for welcome page
        $page_url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        track_pageview_event($page_url, 'landing');
        ?>
        <div class="wrap">
            <div><h1></h1></div>
            <div></div>
            <?php
            require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/snippets/welcome-page.php';
            render_welcome_page();
            ?>
        </div>
        <?php
    }

    /**
     * Renders the Documentation page
     *
     * This page provides comprehensive documentation for the plugin,
     * organized into sections with accordion-style navigation.
     */
    public function render_documentation_page() {
        // Track pageview for documentation page
        $page_url = isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        track_pageview_event($page_url, 'documentation');
        $shop_info = get_option('shopify_shop_info');
        ?>
        <div class="wrap">
            <div><h1></h1></div>
            <div></div>

            <?php $this->render_store_header($shop_info); ?>
            <?php
            require_once SHOPIFY_PLUGIN_DIR . 'includes/components/admin/snippets/documentation-page.php';
            render_documentation_page();
            ?>
        </div>
        <?php
    }

    /**
     * AJAX handler for pageview tracking
     */
    public function ajax_track_pageview() {
        // Verify nonce for security
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'shopify_pageview_nonce')) {
            wp_die('Security check failed');
        }

        // Get parameters from AJAX request
        $page_url = sanitize_text_field($_POST['page_url']);
        $page_name = sanitize_text_field($_POST['page_name']);
        $page_variant = isset($_POST['page_variant']) ? sanitize_text_field($_POST['page_variant']) : null;

        // Track the pageview event
        $result = track_pageview_event($page_url, $page_name, $page_variant);

        // Return JSON response
        if (is_null($result)) {
            wp_send_json_success('Analytics tracking is disabled');
        } elseif (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success('Pageview tracked successfully');
        }
    }

    /**
     * AJAX handler for saving Shopify setup progress
     */
    public function save_shopify_setup_progress() {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shopify-plugin'));
        }

        // Verify nonce
        if (!check_ajax_referer('shopify_setup_progress', 'nonce', false)) {
            wp_send_json_error(__('Security check failed.', 'shopify-plugin'));
        }

        // Get and sanitize input data
        $checkbox_id = isset($_POST['checkbox_id']) ? sanitize_key($_POST['checkbox_id']) : '';
        $checked = isset($_POST['checked']) ? (bool) $_POST['checked'] : false;

        if (empty($checkbox_id)) {
            wp_send_json_error(__('Invalid checkbox ID.', 'shopify-plugin'));
        }

        // Get current progress
        $progress = get_option('shopify_setup_progress', array());

        // Update the specific checkbox state
        $progress[$checkbox_id] = $checked;

        // Save updated progress
        $result = update_option('shopify_setup_progress', $progress);

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Progress saved successfully.', 'shopify-plugin'),
                'checkbox_id' => $checkbox_id,
                'checked' => $checked
            ));
        } else {
            wp_send_json_error(__('Failed to save progress.', 'shopify-plugin'));
        }
    }

    /**
     * AJAX handler for clearing Shopify access code when navigating away
     */
    public function clear_shopify_access_code() {
        // Security checks
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'shopify-plugin'));
        }

        // Verify nonce
        if (!check_ajax_referer('clear_access_code_nonce', 'nonce', false)) {
            wp_send_json_error(__('Security check failed.', 'shopify-plugin'));
        }

        // Clear the access code option
        delete_option('shopify_for_wordpress_access_code');

        wp_send_json_success(__('Access code cleared.', 'shopify-plugin'));
    }

    /**
     * Checks connection health on admin page loads
     *
     * Validates Shopify credentials when accessing Settings, Home, or Documentation pages.
     * If credentials are invalid (e.g., Shopify has invalidated them), automatically
     * disconnects the store.
     */
    public function check_connection_health() {
        $store_url = get_option('shopify_store_url');
        $api_key = get_option('shopify_api_key');
        $shop_info = get_option('shopify_shop_info');

        // If no credentials exist, nothing to check
        if (empty($store_url) || empty($api_key)) {
            return;
        }

        // Validate the credentials using the GraphQL API
        require_once SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php';
        $result = Shopify_Wp_GraphQL::validate_store_credentials($store_url, $api_key);

        if (is_wp_error($result)) {
			// Only disconnect if the error includes 'Invalid or expired storefront access token'
			if (strpos($result->get_error_message(), 'Invalid or expired storefront access token') !== false) {
				// Connection is invalid - perform automatic disconnect
				shopify_wp_connect_perform_disconnect(true);
			}
        }
    }

    /**
     * Get the has_wordpress_plugin_signup field from shop info
     *
     * @return bool Returns true if the shop has WordPress plugin signup, false otherwise
     */
    public static function get_has_wordpress_plugin_signup() {
        $shop_info = get_option('shopify_shop_info');
        return is_array($shop_info) && ($shop_info['has_wordpress_plugin_signup'] ?? false);
    }
}
