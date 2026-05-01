<?php
/**
 * Reusable Accordion Component
 */

if (!defined('ABSPATH')) {
    exit;
}

class Shopify_For_Wp_Accordion {
    private $title;
    private $items;
    private $id;
    private $default_open;

    public function __construct($title, $items = array(), $id = '', $default_open = false) {
        $this->title = $title;
        $this->items = $items;
        $this->id = $id ?: 'accordion-' . uniqid();
        $this->default_open = $default_open;
    }

    public function render() {
        ?>
        <div class="shopify-accordion">
            <div class="shopify-accordion__header <?php echo esc_attr($this->default_open ? 'active' : ''); ?>" data-toggle="#<?php echo esc_attr($this->id); ?>">
                <span class="shopify-accordion__title"><?php echo esc_html($this->title); ?></span>
                <span class="shopify-accordion__chevron">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
            </div>
            <div class="shopify-accordion__content <?php echo esc_attr($this->default_open ? 'active' : ''); ?>" id="<?php echo esc_attr($this->id); ?>" <?php echo esc_attr($this->default_open ? 'style="max-height: none;"' : ''); ?>>
                <?php if (!empty($this->items)) : ?>
                    <ul class="shopify-accordion__list">
                        <?php foreach ($this->items as $item) : ?>
                            <li class="shopify-accordion__item">
                                <div class="shopify-accordion__item-content">
                                    <h4 class="shopify-accordion__item-title"><?php echo esc_html($item['title']); ?>
                                    <?php if (!empty($item['icon'])) : ?>
                                    <span class="shopify-accordion__item-icon">
                                        <?php echo wp_kses_post($item['icon']); ?>
                                    </span>
                                <?php endif; ?>
                                </h4>
                                    <?php if (!empty($item['description'])) : ?>
                                        <p class="shopify-accordion__item-description"><?php echo esc_html($item['description']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($item['cta'])) : ?>
                                    <div class="shopify-accordion__item-cta-container">
                                        <a target="_blank" href="<?php echo esc_url($item['cta']['url']); ?>" class="shopify-accordion__item-cta">
                                            <?php echo esc_html($item['cta']['text']); ?>
                                            <?php if (!empty($item['cta']['icon'])) : ?>
                                                <span class="shopify-accordion__item-cta-icon">
                                                    <?php echo wp_kses_post($item['cta']['icon']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                    </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
} 