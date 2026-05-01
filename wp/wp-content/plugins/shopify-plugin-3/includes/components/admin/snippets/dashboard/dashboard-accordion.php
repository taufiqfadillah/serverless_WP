<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dashboard Accordion Component
 * 
 * Renders a collapsible accordion component for the admin dashboard.
 * Each accordion item can have a checkbox, heading, caption, and action button.
 */



/**
 * Render a dynamic dashboard accordion with configurable items
 * 
 * @param string $title The accordion title
 * @param string $caption The accordion caption (can contain HTML/links)
 * @param array $items Array of accordion items with structure:
 *   - 'id': unique identifier
 *   - 'checked': boolean for checkbox state
 *   - 'heading': item heading text
 *   - 'caption': item caption text
 *   - 'action_url': URL for the action link
 *   - 'action_text': text for the action link
 *   - 'action_icon': icon name (optional, defaults to 'open')
 * @param string $accordion_id Unique ID for the accordion (optional)
 * @param bool $default_open Whether accordion should be open by default
 * @param string $title_icon Optional icon path for the title (relative to assets/images/)
 */
function render_dashboard_accordion($title, $caption, $items = array(), $accordion_id = '', $default_open = false, $title_icon = '') {
    $accordion_id = $accordion_id ?: 'dashboard-accordion-' . uniqid();
    ?>
    <div class="dashboard-accordion app-container">
        <div class="dashboard-accordion__item">
                            <div class="dashboard-accordion__header <?php echo esc_attr($default_open ? 'active' : ''); ?>" 
                 data-toggle="#<?php echo esc_attr($accordion_id); ?>">
                <div class="dashboard-accordion__header-content">
                    <div class="dashboard-accordion__title-wrapper">
                        <h3 class="dashboard-accordion__title"><?php echo esc_html($title); ?></h3>
                        <?php if (!empty($title_icon)): ?>
                            <div class="dashboard-accordion__title-icon">
                                <?php 
                                $icon_url = plugins_url('assets/images/' . $title_icon, SHOPIFY_PLUGIN_DIR . 'shopify.php');
                                ?>
                                <img src="<?php echo esc_url($icon_url); ?>" 
                                     alt="<?php echo esc_attr($title); ?> icon" 
                                     class="dashboard-accordion__icon-image">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="dashboard-accordion__caption">
                        <?php echo wp_kses_post($caption); ?>
                    </div>
                </div>
                <div class="dashboard-accordion__header-actions">
                    <span class="dashboard-accordion__chevron">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.5 11.6L12 16L6.5 11.6L7.4 10.4L12 14L16.5 10.4L17.5 11.6Z" fill="#1E1E1E"/>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="dashboard-accordion__content <?php echo esc_attr($default_open ? 'active' : ''); ?>" 
                 id="<?php echo esc_attr($accordion_id); ?>">
                <div class="dashboard-accordion__content-inner">
                    <?php if (!empty($items)): ?>
                        <div class="dashboard-accordion__items">
                            <?php foreach ($items as $item): ?>
                                <div class="dashboard-accordion__item-row">
                                    <div class="dashboard-accordion__item-content">
                                        <div class="dashboard-accordion__checkbox-wrapper">
                                            <input type="checkbox" 
                                                   id="<?php echo esc_attr($item['id']); ?>" 
                                                   class="dashboard-accordion__checkbox" 
                                                   <?php echo esc_attr(!empty($item['checked']) ? 'checked' : ''); ?>>
                                            <label for="<?php echo esc_attr($item['id']); ?>" class="dashboard-accordion__checkbox-label"></label>
                                        </div>
                                        <div class="dashboard-accordion__item-text">
                                            <h4 class="dashboard-accordion__item-heading"><?php echo esc_html($item['heading']); ?></h4>
                                            <p class="dashboard-accordion__item-caption"><?php echo wp_kses_post($item['caption']); ?></p>
                                        </div>
                                    </div>
                                    <?php 
                                    // Determine which URL to use - action_url if available, otherwise fallback_action_url
                                    $final_action_url = '';
                                    if (!empty($item['action_url'])) {
                                        $final_action_url = $item['action_url'];
                                    } elseif (!empty($item['fallback_action_url'])) {
                                        $final_action_url = $item['fallback_action_url'];
                                    }
                                    
                                    if (!empty($final_action_url)): ?>
                                        <div class="dashboard-accordion__item-action">
                                            <a href="<?php echo esc_url($final_action_url); ?>" 
                                               class="dashboard-accordion__action-link"
                                               <?php echo esc_attr(strpos($final_action_url, 'http') === 0 ? 'target="_blank"' : ''); ?>>
                                                <?php echo esc_html($item['action_text']); ?>
                                                <span class="dashboard-accordion__action-icon">
                                                    <?php 
                                                    $icon_name = !empty($item['action_icon']) ? $item['action_icon'] : 'open';
                                                    $icon_path = SHOPIFY_PLUGIN_DIR . 'assets/icons/' . $icon_name . '.php';
                                                    if (file_exists($icon_path)) {
                                                        include $icon_path;
                                                    } else {
                                                        // Fallback to open icon
                                                        include SHOPIFY_PLUGIN_DIR . 'assets/icons/open.php';
                                                    }
                                                    ?>
                                                </span>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="dashboard-accordion__empty">
                            <p>No items available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Example usage function - you can call this to see how to use the accordion
 */
function render_example_dashboard_accordion() {
    $title = "Complete your Shopify setup";
    $caption = "To start selling on WordPress, you'll need to complete a few essential tasks. <a href='#'>Learn more</a> about the setup process.";
    
    $items = array(
        array(
            'id' => 'add-products',
            'checked' => false,
            'heading' => 'Add products',
            'caption' => 'Provide a few details to activate Shopify Payments and start accepting all major payment methods.',
            'action_url' => '#',
            'action_text' => 'Complete account setup',
            'action_icon' => 'open'
        ),
        array(
            'id' => 'setup-payments',
            'checked' => false,
            'heading' => 'Setup Shopify Payments',
            'caption' => 'Kickstart your shipping strategy by reviewing rates that have already been set based on your location.',
            'action_url' => '#',
            'action_text' => 'Review rates',
            'action_icon' => 'settings'
        ),
        array(
            'id' => 'review-shipping',
            'checked' => false,
            'heading' => 'Review shipping rates',
            'caption' => 'Kickstart your shipping strategy by reviewing rates that have already been set based on your location.',
            'action_url' => '#',
            'action_text' => 'Review rates',
            'action_icon' => 'open'
        ),
        array(
            'id' => 'pick-plan',
            'checked' => false,
            'heading' => 'Pick a plan and unlock your store',
            'caption' => 'To start selling on your WordPress site, pick a Shopify plan, then remove your store password.',
            'action_url' => '#',
            'action_text' => 'Choose plan',
            'action_icon' => 'open'
        )
    );
    
    // Example with title icon
    render_dashboard_accordion($title, $caption, $items, 'example-accordion', true, 'shopify-glyf-color.png');
    
    // Example without title icon
    // render_dashboard_accordion($title, $caption, $items, 'example-accordion', true);
}

// Styles and scripts are now properly enqueued via wp_enqueue_style() and wp_enqueue_script() in admin-settings.php
// See: assets/css/dashboard-accordion.css and assets/js/dashboard-accordion.js

// Add inline script to set ajaxurl for the dashboard accordion JavaScript
wp_add_inline_script(
    'shopify-dashboard-accordion',
    "window.ajaxurl = '" . esc_url(admin_url('admin-ajax.php')) . "';"
);
?>
