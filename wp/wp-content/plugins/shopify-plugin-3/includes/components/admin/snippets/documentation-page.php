<?php
/**
 * Documentation page snippet for Shopify WP Connect
 */

if (!defined('ABSPATH')) {
    exit;
}

function render_documentation_page() {
    // Prepare translatable strings
    $documentation_strings = array(
        'page_title' => __('Documentation', 'shopify-plugin'),
        'page_subtitle' => __('Complete guide to using Shopify plugin', 'shopify-plugin'),
        'connect_title' => __('Connect Shopify to WordPress site', 'shopify-plugin'),
        'using_title' => __('Using the Shopify plugin', 'shopify-plugin'),
        'advanced_title' => __('Advanced customizations for the Shopify plugin', 'shopify-plugin'),
        'faq_title' => __('FAQs', 'shopify-plugin'),
    );

    // About the plugin section (standalone)
    $about_plugin_content = sprintf(
        '<p>%s</p>',
        __('With the Shopify plugin, you can connect Shopify to your WordPress site to display products and collections on your WordPress site, and sell using Shopify\'s checkout. Customers can add products to a cart on your site and complete checkout with Shopify.', 'shopify-plugin')
    );

    // Set up Shopify section (standalone)
    $setup_shopify_content = sprintf(
        '<p>%s</p>
        <br>
        <ul>
            <li><strong>%s</strong> - %s <a href="https://www.shopify.com/ca/pricing" target="_blank" rel="noopener noreferrer">%s</a></li>
            <li><strong>%s</strong> - %s <a href="https://help.shopify.com/en/manual/payments/shopify-payments/onboarding/account-setup" target="_blank" rel="noopener noreferrer">%s</a></li>
            <li><strong>%s</strong> - %s <a href="https://help.shopify.com/en/manual/fulfillment/setup" target="_blank" rel="noopener noreferrer">%s</a></li>
            <li><strong>%s</strong> - %s <a href="https://help.shopify.com/en/manual/online-store/themes/password-page#remove-password-protection-from-your-online-store" target="_blank" rel="noopener noreferrer">%s</a></li>
        </ul>
        <br>
        <p>%s <a href="https://help.shopify.com/manual/online-sales-channels/sell-on-wordpress/connect-shopify-to-wordpress" target="_blank" rel="noopener noreferrer">%s</a> %s</p>',
        __('Before you can start selling on your WordPress site, there are a few essential steps to complete in your Shopify admin.', 'shopify-plugin'),
        __('Pick a plan', 'shopify-plugin'),
        __('You must select a plan to start selling. Shopify offers several plans to help your business grow at every stage. Each Shopify pricing plan offers a variety of features.', 'shopify-plugin'),
        __('Learn more', 'shopify-plugin'),
        __('Set up Shopify Payments', 'shopify-plugin'),
        __('Provide a few details to complete your account setup and start getting paid for your sales.', 'shopify-plugin'),
        __('Learn more', 'shopify-plugin'),
        __('Review and select shipping rates', 'shopify-plugin'),
        __('Kickstart your shipping strategy by reviewing rates that have already been set based on your location. Confirm that your rates fit your shipping strategy and reflect any discounts from Shopify\'s shipping labels.', 'shopify-plugin'),
        __('Learn more', 'shopify-plugin'),
        __('Remove your Shopify password', 'shopify-plugin'),
        __('In order to start selling, you need to remove your Shopify store\'s password.', 'shopify-plugin'),
        __('Learn more', 'shopify-plugin'),
        __('If you want detailed guidance on completing these key tasks, review Shopify\'s Setup Guide, located on the homepage of your Shopify admin. You can also visit', 'shopify-plugin'),
        __('Shopify\'s Help Center', 'shopify-plugin'),
        __('for more help.', 'shopify-plugin')
    );

    // Using the Shopify plugin accordion data
    $using_accordions = array(
        array(
            'title' => __('Add products to your WordPress site', 'shopify-plugin'),
            'content' => sprintf(
                '<ol>
                    <li><strong>%s</strong>
                        %s
                        <ul>
                            <li><a href="https://help.shopify.com/en/manual/intro-to-shopify/initial-setup/setup-getting-started#add-your-first-product" target="_blank" rel="noopener noreferrer">%s</a></li>
                            <li><a href="https://help.shopify.com/en/manual/products/collections" target="_blank" rel="noopener noreferrer">%s</a></li>
                        </ul>
                    </li>
                    <br>
                    <li><strong>%s</strong>
                        %s
                        <ol style="list-style-type: lower-alpha;">
                           <li>%s</li>
                           <li>%s</li>
                           <li>%s</li>
                        </ol>
                    </li>
                </ol>',
                __('Add a product or collection in the Shopify admin', 'shopify-plugin'),
                __('- To display products on your WordPress site, you first need to add products or collections in your Shopify admin.', 'shopify-plugin'),
                __('Add your first product - Shopify Help Center', 'shopify-plugin'),
                __('Add collections - Shopify Help Center', 'shopify-plugin'),
                __('Publish a product or collection on your WordPress site', 'shopify-plugin'),
                __('- To display a product or collection on a WordPress page, head to the Pages section of your WordPress dashboard, then click the Add page button.', 'shopify-plugin'),
                __('In the edit page state, start by clicking the + block inserter icon.', 'shopify-plugin'),
                __('In the Blocks section, you can search for or scroll to the Shopify section, where you\'ll find options to Add a Shopify product or Add a Shopify collection.', 'shopify-plugin'),
                __('Once selected, you can search for the specific products or collections you want to display on your WordPress site. Simply enter your search query in the Product search or Collection search section, located on the right-hand sidebar under block settings.', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('Choose where to display your products', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s</p>
                <br>
                <ul>
                    <li><strong>%s</strong> - %s</li>
                    <li><strong>%s</strong> - %s</li>
                </ul>
                <br>
                <p>%s %s %s</p>',
                __('There are two ways you can display products on your WordPress site using blocks:', 'shopify-plugin'),
                __('Create a dedicated product page', 'shopify-plugin'),
                __('Showcase all your products and collections in one central place, and make it easy for your customers to browse everything you have to offer.', 'shopify-plugin'),
                __('Add products directly into any page or blog post', 'shopify-plugin'),
                __('Weave products and collections into any page to create engaging, shoppable content that connects with your customers.', 'shopify-plugin'),
                __('You can further customize how your products are displayed on the', 'shopify-plugin'),
                __('Advanced Settings', 'shopify-plugin'),
                __('page of your Shopify plugin.', 'shopify-plugin')
            )
        )
    );

    // Advanced customizations accordion data
    $advanced_accordions = array(
        array(
            'title' => __('Shopify plugin and Shopify storefront components', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s <a href="https://shopify.dev/docs/api/storefront-web-components" target="_blank" rel="noopener noreferrer">%s</a>. %s</p>
                <br>
                <ul>
                    <li><strong>%s:</strong> %s</li>
                    <li><strong>%s:</strong> %s</li>
                    <li><strong>%s:</strong> %s</li>
                    <li><strong>%s:</strong> %s</li>
                </ul>',
                __('The Shopify plugin is built with', 'shopify-plugin'),
                __('Shopify Storefront Web Components', 'shopify-plugin'),
                __('The following components are used as the building blocks of your commerce:', 'shopify-plugin'),
                __('Product Card Component', 'shopify-plugin'),
                __('This component displays individual product information, typically seen in product listings or collection pages. It often includes elements such as the product image, title, price, and an Add to cart button.', 'shopify-plugin'),
                __('Product Quick View Modal Component', 'shopify-plugin'),
                __('This component displays a modal over the existing content with product details, an Add to Cart button, and a link to view full product details.', 'shopify-plugin'),
                __('Product Detail Page Component', 'shopify-plugin'),
                __('This component displays a single product, with full product details. Each product detail page component has a unique URL.', 'shopify-plugin'),
                __('Collection Component', 'shopify-plugin'),
                __('This component displays collections of products that you have created.', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('Shopify products and collections display customization', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s</p>
                <br>
                <p>%s</p>
                <br>
                <ol>
                    <li><strong>%s</strong> %s</li>
                    <li><strong>%s</strong> %s 
                        <ul>
                            <li>%s</li>
                            <li>%s</li>
                            <li>%s</li>
                            <li>%s</li>
                        </ul>
                    </li>
                </ol>
                <br>
                <p>%s</p>
                <br>
                <p>%s</p>
                <br>
                <p>%s <a href="https://shopify.dev/docs/storefronts/headless/bring-your-own-stack/wordpress" target="_blank" rel="noopener noreferrer">%s</a>.</p>',
                __('The default Shopify storefront components on your plugin have everything you and your customers need for a seamless shopping experience. You also have the flexibility to customize the display of these components to align with your specific brand identity and design requirements.', 'shopify-plugin'),
                __('To undertake such customizations, you must have the ability to access and modify your WordPress theme files and directories. The process involves creating a dedicated folder within your active WordPress theme\'s directory.', 'shopify-plugin'),
                __('Create the \'Shopify\' Folder:', 'shopify-plugin'),
                __('Begin by creating a new folder named Shopify directly within your active theme\'s directory. This folder serves as the central location for your custom component overrides.', 'shopify-plugin'),
                __('Override Individual Components:', 'shopify-plugin'),
                __('To modify a specific component, you can add a PHP file with a designated name inside the newly created Shopify folder. The naming convention for these override files is crucial for the plugin to recognize and apply your custom versions:', 'shopify-plugin'),
                __('To update the Product Card Component, create a PHP file named product-card.php within the Shopify folder.', 'shopify-plugin'),
                __('To update the Product Quick View Modal Component, create a PHP file named modal.php within the Shopify folder.', 'shopify-plugin'),
                __('To update the Product Detail Page Component, create a PHP file named pdp.php within the Shopify folder.', 'shopify-plugin'),
                __('To update the Collection Component, create a PHP file named component.php within the Shopify folder.', 'shopify-plugin'),
                __('This complete control means that after your custom PHP file is created, the plugin disregards its default component file for that specific element. You can implement any code within these override files, from static HTML blocks to complex PHP logic.', 'shopify-plugin'),
                __('While it\'s highly recommended to leverage the Shopify Storefront Web Components (as utilized in the default plugin structure) for consistency and access to built-in Shopify functionalities, you aren\'t limited to them. You can integrate other methods for fetching and displaying product and collection data, allowing for advanced customizations.', 'shopify-plugin'),
                __('For more information and best practices, refer to', 'shopify-plugin'),
                __('Shopify plugin Dev docs', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('Shopify products and collections rewrites', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s</p>
                <ol>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                </ol>',
                __('When you publish a product or a collection on Shopify, each product and collection has a unique URL. These URLs, by default, are created with your Shopify domain. With the rewrites function, you can access those unique URLs with your public WordPress domain.', 'shopify-plugin'),
                __('From the Shopify plugin, go to Settings.', 'shopify-plugin'),
                __('In the Advanced tab, ensure the two checkboxes in the Shopify links rewrites section are enabled.', 'shopify-plugin'),
                __('Go to Permalink.', 'shopify-plugin'),
                __('Click Save to apply this new rule.', 'shopify-plugin'),
                __('Go to your Shopify admin, and then navigate to the product or collection detail page that you\'d like to create a WordPress link for.', 'shopify-plugin'),
                __('From the search engine listing card, there is a URL displayed that links customers directly to your product or collection. For example: https://johns-apparel.myshopify.com › products › huskee-reusable-cup.', 'shopify-plugin'),
                __('Add (products/huskee-reusable-cup) at the end of your WordPress site URL. For example: https://johns-apparel-wordpress.com/products/huskee-reusable-cup.', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('Customize your product card behaviour', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s</p>
                <br>
                <ul>
                    <li><strong>%s</strong> %s <a href="https://webcomponents.shopify.dev/playground?view=editor&preset=ready-product-page-first" target="_blank" rel="noopener noreferrer">%s</a> %s</li>
                    <li><strong>%s</strong> %s <a href="https://webcomponents.shopify.dev/playground?view=editor&preset=product-detail-page" target="_blank" rel="noopener noreferrer">%s</a> %s</li>
                </ul>
                <br>
                <p>%s</p>
                <ol>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                </ol>',
                __('With Shopify storefront components, there are two product detail card types:', 'shopify-plugin'),
                __('Quick view:', 'shopify-plugin'),
                __('When hovering on the product card, the buyer has an option to Quick Shop. When clicked, the product details are displayed in a', 'shopify-plugin'),
                __('popup or modal style over existing content', 'shopify-plugin'),
                __('in the background.', 'shopify-plugin'),
                __('Detailed product page:', 'shopify-plugin'),
                __('When a customer clicks on the product card (for example, on the image or product name), product details will', 'shopify-plugin'),
                __('appear in a full page view', 'shopify-plugin'),
                __('with a unique URL.', 'shopify-plugin'),
                __('You can change this setting to display either a quick view only, a detailed product page only, or both.', 'shopify-plugin'),
                __('From the Shopify plugin in WordPress, go to Settings.', 'shopify-plugin'),
                __('From the Advanced tab, go to the Product View section.', 'shopify-plugin'),
                __('Choose the option that works best for your store.', 'shopify-plugin'),
                __('Click Save.', 'shopify-plugin')
            )
        )
    );

    // Prepare accordion data with translatable strings
    $connect_accordions = array(
        array(
            'title' => __('Set up Sell on WordPress sales channel', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s <strong>%s</strong> %s</p>
                <ol>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                </ol>',
                __('In order to connect Shopify to the WordPress site, you need to ensure you have install the', 'shopify-plugin'),
                __('Sell on WordPress sales channel', 'shopify-plugin'),
                __('in Shopify admin.', 'shopify-plugin'),
                __('In your Shopify admin, go to Settings > App and Sales channels', 'shopify-plugin'),
                __('Click Shopify App Store.', 'shopify-plugin'),
                __('If applicable, then log in to continue to the Shopify App Store.', 'shopify-plugin'),
                __('From the Shopify App Store, search for and then click Sell on WordPress.', 'shopify-plugin'),
                __('From the Sell on WordPress sales channel listing, click Install.', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('Connect WordPress to Shopify', 'shopify-plugin'),
            'content' => sprintf(
                '<ol>
                    <li>%s <strong>%s</strong> %s <strong>%s</strong>.</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                </ol>',
                __('After installing the Shopify plugin, follow the prompts to either', 'shopify-plugin'),
                __('sign up', 'shopify-plugin'),
                __('for Shopify or', 'shopify-plugin'),
                __('connect your existing Shopify account', 'shopify-plugin'),
                __('Once you\'re in the Shopify admin, navigate to the Sell on WordPress sales channel.', 'shopify-plugin'),
                __('In the Sell on WordPress sales channel, find and copy your Shopify access token.', 'shopify-plugin'),
                __('Return to your Shopify plugin and go to Settings > General. Paste your Shopify access token into the relevant field and click Connect.', 'shopify-plugin'),
                __('To start selling on your WordPress site, you\'ll need to set up payments and shipping in Shopify, and remove your Shopify store\'s password.', 'shopify-plugin')
            )
        )
    );




    // Shopify Partners section (standalone)
    $shopify_partners_content = sprintf(
        '<p>%s</p>
        <br>
        <p>%s <a href="https://partners.shopify.com/organizations" target="_blank" rel="noopener noreferrer">%s</a> %s <a href="https://help.shopify.com/en/partners/manage-clients-stores/development-stores/create-development-stores" target="_blank" rel="noopener noreferrer">%s</a>. %s, %s <a href="https://apps.shopify.com/sell-on-wordpress" target="_blank" rel="noopener noreferrer">%s</a> %s %s</p>
        <br>
        <p>%s <a href="http://partners.shopify.com/signup" target="_blank" rel="noopener noreferrer">%s</a> %s <a href="https://www.shopify.com/partners" target="_blank" rel="noopener noreferrer">%s</a>.</p>',
        __('Shopify Partners can leverage development stores to help clients get set up with the Shopify plugin.', 'shopify-plugin'),
        __('After you or your client has downloaded the Shopify plugin, you can log into your', 'shopify-plugin'),
        __('Partner Dashboard', 'shopify-plugin'),
        __('and select an existing development store or', 'shopify-plugin'),
        __('create a new one', 'shopify-plugin'),
        __('After that', 'shopify-plugin'),
        __('you can add the', 'shopify-plugin'),
        __('Sell on WordPress sales channel', 'shopify-plugin'),
        __('to your development store to start the process of connecting Shopify to your client\'s WordPress site,', 'shopify-plugin'),
        __('following the guidance provided above.', 'shopify-plugin'),
        __('Want to become a Shopify Partner? You can', 'shopify-plugin'),
        __('sign up', 'shopify-plugin'),
        __('for free and', 'shopify-plugin'),
        __('start earning by building on Shopify', 'shopify-plugin')
    );

    // FAQ accordion data
    $faq_accordions = array(
        array(
            'title' => __('If I have an existing Shopify store, can I connect it to my WordPress site?', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s</p>',
                __('Yes. You can connect your existing Shopify store to WordPress. Simply install the Shopify plugin on WordPress and follow the prompts to connect an existing Shopify store.', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('Do I need a paid Shopify plan to use the plugin?', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s <a href="https://www.shopify.com/pricing" target="_blank" rel="noopener noreferrer">%s</a> %s</p>',
                __('Yes. To sell on WordPress, a paid', 'shopify-plugin'),
                __('Shopify plan', 'shopify-plugin'),
                __('is required. You can start with Shopify\'s free trial and pick a paid plan later.', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('What are the requirements for using the Shopify plugin?', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s</p>
                <ul>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                    <li>%s</li>
                </ul>',
                __('To use the Shopify plugin, you\'ll need:', 'shopify-plugin'),
                __('Admin access to your WordPress site with permission to install and activate plugins', 'shopify-plugin'),
                __('A supported WordPress theme (block themes are recommended)', 'shopify-plugin'),
                __('A Shopify account or development store', 'shopify-plugin'),
                __('Shopify admin or staff access with permission to install apps and manage the Storefront API', 'shopify-plugin')
            )
        ),
        array(
            'title' => __('Do I have to maintain both a Shopify and WordPress store?', 'shopify-plugin'),
            'content' => sprintf(
                '<p>%s</p>',
                __('No. The Shopify plugin lets you sell on WordPress using Shopify\'s world-class checkout. You can sell exclusively on your WordPress site or on both platforms—whichever fits your business best.', 'shopify-plugin')
            )
        )
    );

    ?>
    <div class="shopify-form app-container app-documentation-page-container">
        <div class="app-documentation-page-container__content">
            <div class="documentation-layout app-container">

                <div class="dashboard-intro-card">
                    <div class="dashboard-intro-card__content">
                        <h1><?php echo esc_html("More support when you need it", "shopify-plugin") ?></h1>
                        <p class="documentation-subtitle"><?php echo esc_html("Visit the Shopify Help Center for step-by-step guides and troubleshooting tips, or chat with a support advisor.", "shopify-plugin") ?></p>
                        <div class="cta">
                            <a href="https://help.shopify.com/manual/online-sales-channels/sell-on-wordpress/connect-shopify-to-wordpress" target="_blank" rel="noopener noreferrer" class="wp-connect-button sm"><?php echo esc_html("Shopify Help Center", "shopify") ?> <span class="wp-connect-button__icon"><?php include SHOPIFY_PLUGIN_DIR . 'assets/icons/open.php'; ?></span></a>
                        </div>
                    </div>
                    <div class="dashboard-intro-card__image">
                        <img src="<?php echo esc_url(plugins_url('assets/images/shopify-bag.svg', SHOPIFY_PLUGIN_DIR . 'shopify.php')); ?>" alt="Shopify bag logo" />
                    </div>
                </div>

                <div class="documentation-content">
                    <!-- About the Plugin Section (Standalone) -->
                    <div class="documentation-section documentation-section--no-title">
                        <div class="documentation-accordions">
                            <?php render_documentation_accordion(__('About the plugin', 'shopify-plugin'), $about_plugin_content, true); ?>
                        </div>
                    </div>

                    <!-- Connect Section -->
                    <div class="documentation-section">
                        <h2 class="documentation-section-title"><?php echo esc_html($documentation_strings['connect_title']); ?></h2>
                        <div class="documentation-accordions">
                            <?php
                            foreach ($connect_accordions as $accordion) {
                                render_documentation_accordion($accordion['title'], $accordion['content']);
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Set up Shopify Section (Standalone) -->
                    <div class="documentation-section documentation-section--no-title">
                        <div class="documentation-accordions">
                            <?php render_documentation_accordion(__('Set up Shopify', 'shopify-plugin'), $setup_shopify_content, true); ?>
                        </div>
                    </div>

                    <!-- Using the Shopify plugin Section -->
                    <div class="documentation-section">
                        <h2 class="documentation-section-title"><?php echo esc_html($documentation_strings['using_title']); ?></h2>
                        <div class="documentation-accordions">
                            <?php
                            foreach ($using_accordions as $accordion) {
                                render_documentation_accordion($accordion['title'], $accordion['content']);
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Advanced customizations Section -->
                    <div class="documentation-section">
                        <h2 class="documentation-section-title"><?php echo esc_html($documentation_strings['advanced_title']); ?></h2>
                        <div class="documentation-accordions">
                            <?php
                            foreach ($advanced_accordions as $accordion) {
                                render_documentation_accordion($accordion['title'], $accordion['content']);
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Shopify Partners Section (Standalone) -->
                    <div class="documentation-section documentation-section--no-title">
                        <div class="documentation-accordions">
                            <?php render_documentation_accordion(__('Shopify Partners', 'shopify-plugin'), $shopify_partners_content, true); ?>
                        </div>
                    </div>

                    <!-- FAQs Section -->
                    <div class="documentation-section">
                        <h2 class="documentation-section-title"><?php echo esc_html($documentation_strings['faq_title']); ?></h2>
                        <div class="documentation-accordions">
                            <?php
                            foreach ($faq_accordions as $accordion) {
                                render_documentation_accordion($accordion['title'], $accordion['content']);
                            }
                            ?>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
    <?php
}

function render_documentation_accordion($title, $content, $is_section_header = false) {
    $id = 'doc-accordion-' . uniqid();
    // Create a URL-friendly ID based on the title
    $header_id = 'accordion-' . sanitize_title($title);

    $accordion_class = 'documentation-accordion';
    $header_class = 'documentation-accordion__header';
    $title_class = 'documentation-accordion__title';

    if ($is_section_header) {
        $accordion_class .= ' documentation-accordion--section-header';
        $title_class .= ' documentation-accordion__title--section-header';
    }
    ?>
    <div class="<?php echo esc_attr($accordion_class); ?>">
        <div class="<?php echo esc_attr($header_class); ?>" id="<?php echo esc_attr($header_id); ?>" data-toggle="#<?php echo esc_attr($id); ?>">
            <span class="<?php echo esc_attr($title_class); ?>"><?php echo esc_html($title); ?></span>
            <span class="documentation-accordion__chevron">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="24" viewBox="0 0 25 24" fill="none">
                    <path d="M7 12.4L12.5 8L18 12.4L17.1 13.6L12.5 10L8 13.6L7 12.4Z" fill="#1E1E1E"/>
                </svg>
            </span>
        </div>
        <div class="documentation-accordion__content" id="<?php echo esc_attr($id); ?>" style="max-height: 0;">
            <div class="documentation-accordion__content-inner">
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
    </div>
    <?php
}
