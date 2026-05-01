<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get the path to a Shopify template file, checking for theme overrides first
 * 
 * @param string $template_name The template name (e.g., 'product-card.php')
 * @param string $plugin_path The default plugin path to the template
 * @return string The path to the template file to use
 */
function shopify_get_template_path($template_name, $plugin_path) {
    // Check if there's a theme override
    $theme_template_path = get_template_directory() . '/shopify/' . $template_name;
    $child_theme_template_path = get_stylesheet_directory() . '/shopify/' . $template_name;
    
    // Debug logging
    error_log('Shopify - Looking for template: ' . $template_name);
    error_log('Shopify - Child theme path: ' . $child_theme_template_path);
    error_log('Shopify - Parent theme path: ' . $theme_template_path);
    error_log('Shopify - Plugin path: ' . $plugin_path);
    
    // Priority: child theme > parent theme > plugin default
    if (file_exists($child_theme_template_path)) {
        error_log('Shopify - Using child theme override: ' . $child_theme_template_path);
        return $child_theme_template_path;
    } elseif (file_exists($theme_template_path)) {
        error_log('Shopify - Using parent theme override: ' . $theme_template_path);
        return $theme_template_path;
    } else {
        error_log('Shopify - Using plugin default: ' . $plugin_path);
        return $plugin_path;
    }
}

/**
 * Include a Shopify template file, checking for theme overrides first
 * 
 * @param string $template_name The template name (e.g., 'product-card.php')
 * @param string $plugin_path The default plugin path to the template
 */
function shopify_include_template($template_name, $plugin_path) {
    $template_path = shopify_get_template_path($template_name, $plugin_path);
    require_once $template_path;
} 