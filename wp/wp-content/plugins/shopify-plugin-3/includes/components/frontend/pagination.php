<?php
/**
 * Reusable pagination snippet for Shopify collections
 * 
 * @param string $list_id The ID of the shopify-list-context element
 * @param string $previous_icon_path Path to the previous icon (optional)
 * @param string $next_icon_path Path to the next icon (optional)
 * @param array $additional_classes Additional CSS classes for the pagination container (optional)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
function render_shopify_pagination($list_id = 'product-list', $previous_icon_path = null, $next_icon_path = null, $additional_classes = []) {
    // Default icon paths if not provided
    if (!$previous_icon_path) {
        $previous_icon_path = SHOPIFY_PLUGIN_DIR . 'assets/icons/chevron-left.php';
    }
    if (!$next_icon_path) {
        $next_icon_path = SHOPIFY_PLUGIN_DIR . 'assets/icons/chevron-right.php';
    }
    
    // Build CSS classes
    $container_classes = ['collection-pagination'];
    if (!empty($additional_classes)) {
        $container_classes = array_merge($container_classes, $additional_classes);
    }
    $container_class_string = implode(' ', $container_classes);
    ?>
    
    <ul class="<?php echo esc_attr($container_class_string); ?> pagination-button-list">
        <li>
            <button class="pagination-button pagination-button--previous" 
                    onclick="document.getElementById('<?php echo esc_js($list_id); ?>').previousPage(); window.scrollTo(0, 0);">
                <?php include $previous_icon_path; ?>
            </button>
        </li>
        <li>
            <button class="pagination-button pagination-button--next" 
                    onclick="document.getElementById('<?php echo esc_js($list_id); ?>').nextPage(); window.scrollTo(0, 0);">
                <?php include $next_icon_path; ?>
            </button>
        </li>
    </ul>

    <!-- Pagination functionality is now handled by shopify-pagination.js enqueued in frontend-assets.php -->
    <?php
}