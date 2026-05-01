<?php

/**
 * Class Sober_Addons_VC
 */
class Sober_Addons_VC {
	/**
	 * The single instance of the class.
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * Temporary cached terms variable
	 *
	 * @var array
	 */
	protected $terms = array();

	/**
	 * Main Instance.
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *
	 * @return Sober_Addons_VC - Main instance.
	 */
	public static function init() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->modify_elements();
		$this->map_shortcodes();

		remove_action( 'admin_bar_menu', array( vc_frontend_editor(), 'adminBarEditLink' ), 1000 );

		if ( function_exists( 'vc_license' ) ) {
			remove_action( 'admin_notices', array( vc_license(), 'adminNoticeLicenseActivation' ) );
		}

		add_filter( 'vc_google_fonts_get_fonts_filter', array( $this, 'add_google_fonts' ) );
	}

	/**
	 * Modify VC element params
	 */
	public function modify_elements() {
		// Add new option to Custom Header element
		vc_add_param( 'vc_custom_heading', array(
			'heading'     => esc_html__( 'Separate URL', 'sober-addons' ),
			'description' => esc_html__( 'Do not wrap heading text with link tag. Display URL separately', 'sober-addons' ),
			'type'        => 'checkbox',
			'param_name'  => 'separate_link',
			'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
			'weight'      => 0,
		) );
		vc_add_param( 'vc_custom_heading', array(
			'heading'     => esc_html__( 'Link Arrow', 'sober-addons' ),
			'description' => esc_html__( 'Add an arrow to the separated link when hover', 'sober-addons' ),
			'type'        => 'checkbox',
			'param_name'  => 'link_arrow',
			'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
			'weight'      => 0,
			'dependency'  => array(
				'element' => 'separate_link',
				'value'   => 'yes',
			),
		) );
	}

	/**
	 * Register custom shortcodes within Visual Composer interface
	 *
	 * @see http://kb.wpbakery.com/index.php?title=Vc_map
	 */
	public function map_shortcodes() {
		// Product Grid
		vc_map( array(
			'name'        => esc_html__( 'Product Grid', 'sober-addons' ),
			'description' => esc_html__( 'Display products in grid', 'sober-addons' ),
			'base'        => 'sober_product_grid',
			'icon'        => $this->get_icon( 'product-grid.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Number Of Products', 'sober-addons' ),
					'description' => esc_html__( 'Total number of products you want to show', 'sober-addons' ),
					'param_name'  => 'per_page',
					'type'        => 'textfield',
					'value'       => 15,
				),
				array(
					'heading'     => esc_html__( 'Columns', 'sober-addons' ),
					'description' => esc_html__( 'Display products in how many columns', 'sober-addons' ),
					'param_name'  => 'columns',
					'type'        => 'dropdown',
					'std'         => 4,
					'value'       => array(
						esc_html__( '2 Columns', 'sober-addons' ) => 2,
						esc_html__( '3 Columns', 'sober-addons' ) => 3,
						esc_html__( '4 Columns', 'sober-addons' ) => 4,
						esc_html__( '5 Columns', 'sober-addons' ) => 5,
						esc_html__( '6 Columns', 'sober-addons' ) => 6,
					),
				),
				array(
					'heading'     => esc_html__( 'Category', 'sober-addons' ),
					'description' => esc_html__( 'Select what categories you want to use. Leave it empty to use all categories.', 'sober-addons' ),
					'param_name'  => 'category',
					'type'        => 'autocomplete',
					'value'       => '',
					'settings'    => array(
						'multiple' => true,
						'sortable' => true,
						'values'   => $this->get_terms(),
					),
				),
				array(
					'heading'     => esc_html__( 'Product Type', 'sober-addons' ),
					'description' => esc_html__( 'Select product type you want to show', 'sober-addons' ),
					'param_name'  => 'type',
					'type'        => 'dropdown',
					'std'         => 'recent',
					'value'       => array(
						esc_html__( 'Default', 'sober-addons' )               => '',
						esc_html__( 'Recent Products', 'sober-addons' )       => 'recent',
						esc_html__( 'Featured Products', 'sober-addons' )     => 'featured',
						esc_html__( 'Sale Products', 'sober-addons' )         => 'sale',
						esc_html__( 'Best Selling Products', 'sober-addons' ) => 'best_sellers',
						esc_html__( 'Top Rated Products', 'sober-addons' )    => 'top_rated',
					),
				),
				array(
					'heading'    => esc_html__( 'Order By', 'sober-addons' ),
					'param_name' => 'orderby',
					'type'       => 'dropdown',
					'std'        => '',
					'value'      => array(
						__( 'Default', 'sober-addons' )            => '',
						__( 'Default Order (Menu Order)', 'sober-addons' ) => 'menu_order',
						__( 'Date', 'sober-addons' )               => 'date',
						__( 'Product ID', 'sober-addons' )         => 'id',
						__( 'Product Title', 'sober-addons' )      => 'title',
						__( 'Random', 'sober-addons' )             => 'rand',
						__( 'Price', 'sober-addons' )              => 'price',
						__( 'Popularity (Sales)', 'sober-addons' ) => 'popularity',
						__( 'Rating', 'sober-addons' )             => 'rating',
					),
					'dependency'  => array(
						'element' => 'type',
						'value'   => array( '', 'featured', 'sale' ),
					),
				),
				array(
					'heading'    => esc_html__( 'Order', 'sober-addons' ),
					'description' => esc_html__( 'This option will not be used if "Order By" option is "Default"', 'sober-addons' ),
					'param_name' => 'order',
					'type'       => 'dropdown',
					'std'        => 'ASC',
					'value'      => array(
						__( 'Ascending', 'sober-addons' )  => 'ASC',
						__( 'Descending', 'sober-addons' ) => 'DESC',
					),
					'dependency'  => array(
						'element' => 'type',
						'value'   => array( '', 'featured', 'sale' ),
					),
				),
				array(
					'heading'     => esc_html__( 'Load More Button', 'sober-addons' ),
					'description' => esc_html__( 'Show load more button with ajax loading', 'sober-addons' ),
					'param_name'  => 'load_more',
					'type'        => 'checkbox',
					'value'       => array(
						esc_html__( 'Yes', 'sober-addons' ) => 'yes',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Product Tabs
		vc_map( array(
			'name'        => esc_html__( 'Product Tabs', 'sober-addons' ),
			'description' => esc_html__( 'Product grid grouped by tabs', 'sober-addons' ),
			'base'        => 'sober_product_tabs',
			'icon'        => $this->get_icon( 'product-tabs.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Number Of Products', 'sober-addons' ),
					'param_name'  => 'per_page',
					'type'        => 'textfield',
					'value'       => 15,
					'description' => esc_html__( 'Total number of products will be display in single tab', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Columns', 'sober-addons' ),
					'param_name'  => 'columns',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( '4 Columns', 'sober-addons' ) => 4,
						esc_html__( '5 Columns', 'sober-addons' ) => 5,
						esc_html__( '6 Columns', 'sober-addons' ) => 6,
					),
					'description' => esc_html__( 'Display products in how many columns', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Tabs', 'sober-addons' ),
					'description' => esc_html__( 'Select how to group products in tabs', 'sober-addons' ),
					'param_name'  => 'filter',
					'type'        => 'dropdown',
					'std'         => 'group',
					'value'       => array(
						esc_html__( 'Group by category', 'sober-addons' ) => 'category',
						esc_html__( 'Group by tag', 'sober-addons' )      => 'tag',
						esc_html__( 'Group by feature', 'sober-addons' )  => 'group',
					),
				),
				array(
					'heading'     => esc_html__( 'Categories', 'sober-addons' ),
					'description' => esc_html__( 'Select what categories you want to use. Leave it empty to use all categories.', 'sober-addons' ),
					'param_name'  => 'category',
					'type'        => 'autocomplete',
					'value'       => '',
					'settings'    => array(
						'multiple' => true,
						'sortable' => true,
						'values'   => $this->get_terms(),
					),
					'dependency'  => array(
						'element' => 'filter',
						'value'   => 'category',
					),
				),
				array(
					'heading'     => esc_html__( 'Tags', 'sober-addons' ),
					'description' => esc_html__( 'Enter tag slugs. Separates by comma.', 'sober-addons' ),
					'param_name'  => 'tag',
					'type'        => 'textfield',
					'value'       => '',
					'dependency'  => array(
						'element' => 'filter',
						'value'   => 'tag',
					),
				),
				array(
					'heading'     => esc_html__( 'Groups', 'sober-addons' ),
					'param_name'  => 'groups',
					'type'        => 'checkbox',
					'std'         => 'best_sellers,new_products,sale_products',
					'value'       => array(
						esc_html__( 'Best Sellers', 'sober-addons' )  => 'best_sellers',
						esc_html__( 'New Products', 'sober-addons' )  => 'new_products',
						esc_html__( 'Sale Products', 'sober-addons' ) => 'sale_products',
						esc_html__( 'Hot Products', 'sober-addons' )  => 'featured_products',
					),
					'dependency'  => array(
						'element' => 'filter',
						'value'   => 'group',
					),
				),
				array(
					'heading'    => esc_html__( 'Order By', 'sober-addons' ),
					'description' => esc_html__( 'This option can be applied to category tabs and the Featured Products tab.', 'sober-addons' ),
					'param_name' => 'orderby',
					'type'       => 'dropdown',
					'std'        => '',
					'value'      => array(
						__( 'Default', 'sober-addons' )            => '',
						__( 'Default Order (Menu Order)', 'sober-addons' ) => 'menu_order',
						__( 'Date', 'sober-addons' )               => 'date',
						__( 'Product ID', 'sober-addons' )         => 'id',
						__( 'Product Title', 'sober-addons' )      => 'title',
						__( 'Random', 'sober-addons' )             => 'rand',
						__( 'Price', 'sober-addons' )              => 'price',
						__( 'Popularity (Sales)', 'sober-addons' ) => 'popularity',
						__( 'Rating', 'sober-addons' )             => 'rating',
					),
				),
				array(
					'heading'    => esc_html__( 'Order', 'sober-addons' ),
					'description' => esc_html__( 'This option will not be used if "Order By" option is "Default"', 'sober-addons' ),
					'param_name' => 'order',
					'type'       => 'dropdown',
					'std'        => 'ASC',
					'value'      => array(
						__( 'Ascending', 'sober-addons' )  => 'ASC',
						__( 'Descending', 'sober-addons' ) => 'DESC',
					),
				),
				array(
					'heading'     => esc_html__( 'Tabs Effect', 'sober-addons' ),
					'description' => esc_html__( 'Select the way tabs load products', 'sober-addons' ),
					'param_name'  => 'filter_type',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Isotope Toggle', 'sober-addons' ) => 'isotope',
						esc_html__( 'Ajax Load', 'sober-addons' )      => 'ajax',
					),
				),
				array(
					'heading'     => esc_html__( 'Show All Tab', 'sober-addons' ),
					'param_name'  => 'show_all',
					'type'        => 'checkbox',
					'value'       => array(
						esc_html__( 'Yes', 'sober-addons' ) => 'yes',
					),
				),
				array(
					'heading'     => esc_html__( 'Load More Button', 'sober-addons' ),
					'param_name'  => 'load_more',
					'type'        => 'checkbox',
					'value'       => array(
						esc_html__( 'Yes', 'sober-addons' ) => 'yes',
					),
					'description' => esc_html__( 'Show load more button with ajax loading', 'sober-addons' ),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
				),
			),
		) );

		// Product Carousel
		vc_map( array(
			'name'        => esc_html__( 'Product Carousel', 'sober-addons' ),
			'description' => esc_html__( 'Product carousel slider', 'sober-addons' ),
			'base'        => 'sober_product_carousel',
			'icon'        => $this->get_icon( 'product-carousel.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Number Of Products', 'sober-addons' ),
					'description' => esc_html__( 'Total number of products you want to show', 'sober-addons' ),
					'param_name'  => 'per_page',
					'type'        => 'textfield',
					'value'       => 15,
				),
				array(
					'heading'     => esc_html__( 'Columns', 'sober-addons' ),
					'description' => esc_html__( 'Display products in how many columns', 'sober-addons' ),
					'param_name'  => 'columns',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( '3 Columns', 'sober-addons' ) => 3,
						esc_html__( '4 Columns', 'sober-addons' ) => 4,
						esc_html__( '5 Columns', 'sober-addons' ) => 5,
						esc_html__( '6 Columns', 'sober-addons' ) => 6,
					),
				),
				array(
					'heading'     => esc_html__( 'Product Type', 'sober-addons' ),
					'description' => esc_html__( 'Select product type you want to show', 'sober-addons' ),
					'param_name'  => 'type',
					'type'        => 'dropdown',
					'std'         => 'recent',
					'value'       => array(
						esc_html__( 'Default', 'sober-addons' )               => '',
						esc_html__( 'Recent Products', 'sober-addons' )       => 'recent',
						esc_html__( 'Featured Products', 'sober-addons' )     => 'featured',
						esc_html__( 'Sale Products', 'sober-addons' )         => 'sale',
						esc_html__( 'Best Selling Products', 'sober-addons' ) => 'best_sellers',
						esc_html__( 'Top Rated Products', 'sober-addons' )    => 'top_rated',
					),
				),
				array(
					'heading'     => esc_html__( 'Categories', 'sober-addons' ),
					'description' => esc_html__( 'Select what categories you want to use. Leave it empty to use all categories.', 'sober-addons' ),
					'param_name'  => 'category',
					'type'        => 'autocomplete',
					'value'       => '',
					'settings'    => array(
						'multiple' => true,
						'sortable' => true,
						'values'   => $this->get_terms(),
					),
				),
				array(
					'heading'    => esc_html__( 'Order By', 'sober-addons' ),
					'param_name' => 'orderby',
					'type'       => 'dropdown',
					'std'        => '',
					'value'      => array(
						__( 'Default', 'sober-addons' )            => '',
						__( 'Default Order (Menu Order)', 'sober-addons' ) => 'menu_order',
						__( 'Date', 'sober-addons' )               => 'date',
						__( 'Product ID', 'sober-addons' )         => 'id',
						__( 'Product Title', 'sober-addons' )      => 'title',
						__( 'Random', 'sober-addons' )             => 'rand',
						__( 'Price', 'sober-addons' )              => 'price',
						__( 'Popularity (Sales)', 'sober-addons' ) => 'popularity',
						__( 'Rating', 'sober-addons' )             => 'rating',
					),
					'dependency'  => array(
						'element' => 'type',
						'value'   => array( '', 'featured', 'sale' ),
					),
				),
				array(
					'heading'    => esc_html__( 'Order', 'sober-addons' ),
					'description' => esc_html__( 'This option will not be used if "Order By" option is "Default"', 'sober-addons' ),
					'param_name' => 'order',
					'type'       => 'dropdown',
					'std'        => 'ASC',
					'value'      => array(
						__( 'Ascending', 'sober-addons' )  => 'ASC',
						__( 'Descending', 'sober-addons' ) => 'DESC',
					),
					'dependency'  => array(
						'element' => 'type',
						'value'   => array( '', 'featured', 'sale' ),
					),
				),
				array(
					'heading'     => esc_html__( 'Auto Play', 'sober-addons' ),
					'description' => esc_html__( 'Auto play speed in miliseconds. Enter "0" to disable auto play.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'autoplay',
					'value'       => 5000,
				),
				array(
					'heading'    => esc_html__( 'Loop', 'sober-addons' ),
					'type'       => 'checkbox',
					'param_name' => 'loop',
					'value'      => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
				array(
					'heading'     => esc_html__( 'Auto Responsive', 'sober-addons' ),
					'param_name'  => 'auto_responsive',
					'type'        => 'checkbox',
					'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
					'std'         => 'yes',
					'group'       => esc_html__( 'Responsive', 'sober-addons' ),
				),
				array(
					'heading'    => esc_html__( 'Columns on Mobiles', 'sober-addons' ),
					'param_name' => 'columns_mobile',
					'type'       => 'dropdown',
					'std'        => 2,
					'value'      => array(
						esc_html__( '1 Column', 'sober-addons' ) => 1,
						esc_html__( '2 Columns', 'sober-addons' ) => 2,
					),
					'group'       => esc_html__( 'Responsive', 'sober-addons' ),
					'dependency'  => array(
						'element' => 'auto_responsive',
						'value_not_equal_to'   => 'yes',
					),
				),
				array(
					'heading'    => esc_html__( 'Columns on Tablets', 'sober-addons' ),
					'param_name' => 'columns_tablet',
					'type'       => 'dropdown',
					'std'        => 3,
					'value'      => array(
						esc_html__( '2 Columns', 'sober-addons' ) => 2,
						esc_html__( '3 Columns', 'sober-addons' ) => 3,
						esc_html__( '4 Columns', 'sober-addons' ) => 4,
						esc_html__( '5 Columns', 'sober-addons' ) => 5,
						esc_html__( '6 Columns', 'sober-addons' ) => 6,
					),
					'group'       => esc_html__( 'Responsive', 'sober-addons' ),
					'dependency'  => array(
						'element' => 'auto_responsive',
						'value_not_equal_to'   => 'yes',
					),
				),
			),
		) );

		// Post Grid
		vc_map( array(
			'name'        => esc_html__( 'Sober Post Grid', 'sober-addons' ),
			'description' => esc_html__( 'Display posts in grid', 'sober-addons' ),
			'base'        => 'sober_post_grid',
			'icon'        => $this->get_icon( 'post-grid.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'description' => esc_html__( 'Number of posts you want to show', 'sober-addons' ),
					'heading'     => esc_html__( 'Number of posts', 'sober-addons' ),
					'param_name'  => 'per_page',
					'type'        => 'textfield',
					'value'       => 3,
				),
				array(
					'heading'     => esc_html__( 'Columns', 'sober-addons' ),
					'description' => esc_html__( 'Display posts in how many columns', 'sober-addons' ),
					'param_name'  => 'columns',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( '3 Columns', 'sober-addons' ) => 3,
						esc_html__( '4 Columns', 'sober-addons' ) => 4,
					),
				),
				array(
					'heading'     => esc_html__( 'Category', 'sober-addons' ),
					'description' => esc_html__( 'Enter categories name', 'sober-addons' ),
					'param_name'  => 'category',
					'type'        => 'autocomplete',
					'settings'    => array(
						'multiple' => true,
						'sortable' => true,
						'values'   => $this->get_terms( 'category' ),
					),
				),
				array(
					'heading'     => esc_html__( 'Hide Post Meta', 'sober-addons' ),
					'description' => esc_html__( 'Hide information about date, category', 'sober-addons' ),
					'type'        => 'checkbox',
					'param_name'  => 'hide_meta',
					'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Countdown
		vc_map( array(
			'name'        => esc_html__( 'Countdown', 'sober-addons' ),
			'description' => esc_html__( 'Countdown digital clock', 'sober-addons' ),
			'base'        => 'sober_countdown',
			'icon'        => $this->get_icon( 'countdown.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Date', 'sober-addons' ),
					'description' => esc_html__( 'Enter the date in format: YYYY/MM/DD', 'sober-addons' ),
					'admin_label' => true,
					'type'        => 'textfield',
					'param_name'  => 'date',
				),
				array(
					'heading'     => esc_html__( 'Text Align', 'sober-addons' ),
					'description' => esc_html__( 'Select text alignment', 'sober-addons' ),
					'param_name'  => 'text_align',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Left', 'sober-addons' )   => 'left',
						esc_html__( 'Center', 'sober-addons' ) => 'center',
						esc_html__( 'Right', 'sober-addons' )  => 'right',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Button
		vc_map( array(
			'name'        => esc_html__( 'Sober Button', 'sober-addons' ),
			'description' => esc_html__( 'Button in style', 'sober-addons' ),
			'base'        => 'sober_button',
			'icon'        => $this->get_icon( 'button.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter button text', 'sober-addons' ),
					'admin_label' => true,
					'type'        => 'textfield',
					'param_name'  => 'label',
				),
				array(
					'heading'    => esc_html__( 'URL (Link)', 'sober-addons' ),
					'type'       => 'vc_link',
					'param_name' => 'link',
				),
				array(
					'heading'     => esc_html__( 'Style', 'sober-addons' ),
					'description' => esc_html__( 'Select button style', 'sober-addons' ),
					'param_name'  => 'style',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Normal', 'sober-addons' )  => 'normal',
						esc_html__( 'Outline', 'sober-addons' ) => 'outline',
						esc_html__( 'Light', 'sober-addons' )   => 'light',
					),
				),
				array(
					'heading'     => esc_html__( 'Size', 'sober-addons' ),
					'description' => esc_html__( 'Select button size', 'sober-addons' ),
					'param_name'  => 'size',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Normal', 'sober-addons' ) => 'normal',
						esc_html__( 'Large', 'sober-addons' )  => 'large',
						esc_html__( 'Small', 'sober-addons' )  => 'small',
					),
					'dependency'  => array(
						'element' => 'style',
						'value'   => array( 'normal', 'outline' ),
					),
				),
				array(
					'heading'     => esc_html__( 'Color', 'sober-addons' ),
					'description' => esc_html__( 'Select button color', 'sober-addons' ),
					'param_name'  => 'color',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Dark', 'sober-addons' )  => 'dark',
						esc_html__( 'White', 'sober-addons' ) => 'white',
					),
					'dependency'  => array(
						'element' => 'style',
						'value'   => array( 'normal', 'outline' ),
					),
				),
				array(
					'heading'     => esc_html__( 'Alignment', 'sober-addons' ),
					'description' => esc_html__( 'Select button alignment', 'sober-addons' ),
					'param_name'  => 'align',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Inline', 'sober-addons' ) => 'inline',
						esc_html__( 'Left', 'sober-addons' )   => 'left',
						esc_html__( 'Center', 'sober-addons' ) => 'center',
						esc_html__( 'Right', 'sober-addons' )  => 'right',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Banner
		vc_map( array(
			'name'        => esc_html__( 'Banner Image', 'sober-addons' ),
			'description' => esc_html__( 'Banner image for promotion', 'sober-addons' ),
			'base'        => 'sober_banner',
			'icon'        => $this->get_icon( 'banner.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image', 'sober-addons' ),
					'description' => esc_html__( 'Banner Image', 'sober-addons' ),
					'param_name'  => 'image',
					'type'        => 'attach_image',
				),
				array(
					'heading'     => esc_html__( 'Image size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size. Example: "thumbnail", "medium", "large", "full" or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_size',
					'value'       => '',
				),
				array(
					'heading'     => esc_html__( 'Banner description', 'sober-addons' ),
					'description' => esc_html__( 'A short text display before the banner text', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'desc',
				),
				array(
					'heading'     => esc_html__( 'Banner Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter the banner text', 'sober-addons' ),
					'type'        => 'textarea',
					'param_name'  => 'content',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Banner Text Position', 'sober-addons' ),
					'description' => esc_html__( 'Select text position', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'text_position',
					'value'       => array(
						esc_html__( 'Left', 'sober-addons' )   => 'left',
						esc_html__( 'Center', 'sober-addons' ) => 'center',
						esc_html__( 'Right', 'sober-addons' )  => 'right',
					),
				),
				array(
					'type'       => 'font_container',
					'param_name' => 'font_container',
					'value'      => '',
					'settings'   => array(
						'fields' => array(
							'font_size',
							'line_height',
							'color',
							'font_size_description'   => esc_html__( 'Enter text font size.', 'sober-addons' ),
							'line_height_description' => esc_html__( 'Enter text line height.', 'sober-addons' ),
							'color_description'       => esc_html__( 'Select text color.', 'sober-addons' ),
						),
					),
				),
				array(
					'heading'     => esc_html__( 'Use theme default font family?', 'sober-addons' ),
					'description' => esc_html__( 'Use font family from the theme.', 'sober-addons' ),
					'type'        => 'checkbox',
					'param_name'  => 'use_theme_fonts',
					'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
				),
				array(
					'type'       => 'google_fonts',
					'param_name' => 'google_fonts',
					'value'      => 'font_family:Abril%20Fatface%3Aregular|font_style:400%20regular%3A400%3Anormal',
					'settings'   => array(
						'fields' => array(
							'font_family_description' => esc_html__( 'Select font family.', 'sober-addons' ),
							'font_style_description'  => esc_html__( 'Select font styling.', 'sober-addons' ),
						),
					),
					'dependency' => array(
						'element'            => 'use_theme_fonts',
						'value_not_equal_to' => 'yes',
					),
				),
				array(
					'heading'    => esc_html__( 'Link (URL)', 'sober-addons' ),
					'type'       => 'vc_link',
					'param_name' => 'link',
				),
				array(
					'heading'     => esc_html__( 'Button Type', 'sober-addons' ),
					'description' => esc_html__( 'Select button type', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'button_type',
					'value'       => array(
						esc_html__( 'Light Button', 'sober-addons' )  => 'light',
						esc_html__( 'Normal Button', 'sober-addons' ) => 'normal',
						esc_html__( 'Arrow Icon', 'sober-addons' )    => 'arrow_icon',
					),
				),
				array(
					'heading'     => esc_html__( 'Button Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter the text for banner button', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'button_text',
					'dependency'  => array(
						'element' => 'button_type',
						'value'   => array( 'light', 'normal' ),
					),
				),
				array(
					'heading'     => esc_html__( 'Button Visibility', 'sober-addons' ),
					'description' => esc_html__( 'Select button visibility', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'button_visibility',
					'value'       => array(
						esc_html__( 'Always visible', 'sober-addons' ) => 'always',
						esc_html__( 'When hover', 'sober-addons' )     => 'hover',
						esc_html__( 'Hidden', 'sober-addons' )         => 'hidden',
					),
				),
				array(
					'heading'     => esc_html__( 'Banner Color Scheme', 'sober-addons' ),
					'description' => esc_html__( 'Select color scheme for description, button color', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'scheme',
					'value'       => array(
						esc_html__( 'Dark', 'sober-addons' )  => 'dark',
						esc_html__( 'Light', 'sober-addons' ) => 'light',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
				array(
					'heading'    => esc_html__( 'CSS box', 'sober-addons' ),
					'type'       => 'css_editor',
					'param_name' => 'css',
					'group'      => esc_html__( 'Design Options', 'sober-addons' ),
				),
			),
		) );

		// Banner 2
		vc_map( array(
			'name'        => esc_html__( 'Banner Image 2', 'sober-addons' ),
			'description' => esc_html__( 'Simple banner that supports multiple buttons', 'sober-addons' ),
			'base'        => 'sober_banner2',
			'icon'        => $this->get_icon( 'banner2.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image', 'sober-addons' ),
					'description' => esc_html__( 'Banner Image', 'sober-addons' ),
					'param_name'  => 'image',
					'type'        => 'attach_image',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Image size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size. Example: "thumbnail", "medium", "large", "full" or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_size',
					'value'       => '',
				),
				array(
					'heading'     => esc_html__( 'Buttons', 'sober-addons' ),
					'description' => esc_html__( 'Enter link and label for buttons.', 'sober-addons' ),
					'type'        => 'param_group',
					'param_name'  => 'buttons',
					'params'      => array(
						array(
							'heading'    => esc_html__( 'Button Text', 'sober-addons' ),
							'type'       => 'textfield',
							'param_name' => 'text',
						),
						array(
							'heading'    => esc_html__( 'Button Link', 'sober-addons' ),
							'type'       => 'vc_link',
							'param_name' => 'link',
						),
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Banner 3
		vc_map( array(
			'name'        => esc_html__( 'Banner Image 3', 'sober-addons' ),
			'description' => esc_html__( 'Simple banner with text at bottom', 'sober-addons' ),
			'base'        => 'sober_banner3',
			'icon'        => $this->get_icon( 'banner3.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image', 'sober-addons' ),
					'description' => esc_html__( 'Banner Image', 'sober-addons' ),
					'param_name'  => 'image',
					'type'        => 'attach_image',
				),
				array(
					'heading'     => esc_html__( 'Image size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size. Example: "thumbnail", "medium", "large", "full" or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_size',
					'value'       => '',
				),
				array(
					'heading'     => esc_html__( 'Banner Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter banner text', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'text',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Banner Text Position', 'sober-addons' ),
					'description' => esc_html__( 'Select text position', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'text_align',
					'value'       => array(
						esc_html__( 'Left', 'sober-addons' )   => 'left',
						esc_html__( 'Center', 'sober-addons' ) => 'center',
						esc_html__( 'Right', 'sober-addons' )  => 'right',
					),
				),
				array(
					'heading'     => esc_html__( 'Text Color Scheme', 'sober-addons' ),
					'description' => esc_html__( 'Select color scheme for banner content', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'scheme',
					'value'       => array(
						esc_html__( 'Dark', 'sober-addons' )  => 'dark',
						esc_html__( 'Light', 'sober-addons' ) => 'light',
					),
					'std' => 'dark'
				),
				array(
					'heading'    => esc_html__( 'Link (URL)', 'sober-addons' ),
					'type'       => 'vc_link',
					'param_name' => 'link',
				),
				array(
					'heading'     => esc_html__( 'Button Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter the text for banner button', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'button_text',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Banner 4
		vc_map( array(
			'name'        => esc_html__( 'Banner Image 4', 'sober-addons' ),
			'description' => esc_html__( 'Simple banner image with text', 'sober-addons' ),
			'base'        => 'sober_banner4',
			'icon'        => $this->get_icon( 'banner4.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image', 'sober-addons' ),
					'description' => esc_html__( 'Banner Image', 'sober-addons' ),
					'param_name'  => 'image',
					'type'        => 'attach_image',
				),
				array(
					'heading'     => esc_html__( 'Image size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size. Example: "thumbnail", "medium", "large", "full" or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use "thumbnail" size.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_size',
					'value'       => 'full',
				),
				array(
					'heading'    => esc_html__( 'Link (URL)', 'sober-addons' ),
					'type'       => 'vc_link',
					'param_name' => 'link',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
				array(
					'heading'    => esc_html__( 'Banner Content', 'sober-addons' ),
					'type'       => 'textarea_html',
					'param_name' => 'content',
					'group'      => esc_html__( 'Text', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Button Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter the text for banner button', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'button_text',
					'group'       => esc_html__( 'Text', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Button Style', 'sober-addons' ),
					'description' => esc_html__( 'Select button style', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'button_style',
					'group'       => esc_html__( 'Text', 'sober-addons' ),
					'std'         => 'light',
					'value'       => array(
						esc_html__( 'Normal', 'sober-addons' )  => 'normal',
						esc_html__( 'Outline', 'sober-addons' ) => 'outline',
						esc_html__( 'Light', 'sober-addons' ) => 'light',
					),
				),
				array(
					'heading'     => esc_html__( 'Text Color Scheme', 'sober-addons' ),
					'description' => esc_html__( 'Select color scheme for banner content', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'scheme',
					'group'       => esc_html__( 'Text', 'sober-addons' ),
					'value'       => array(
						esc_html__( 'Dark', 'sober-addons' )  => 'dark',
						esc_html__( 'Light', 'sober-addons' ) => 'light',
					),
				),
				array(
					'heading'     => esc_html__( 'Content Horizontal Alignment', 'sober-addons' ),
					'description' => esc_html__( 'Horizontal alignment of banner text', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'align_horizontal',
					'group'       => esc_html__( 'Text', 'sober-addons' ),
					'value'       => array(
						esc_html__( 'Left', 'sober-addons' )   => 'left',
						esc_html__( 'Center', 'sober-addons' ) => 'center',
						esc_html__( 'Right', 'sober-addons' )  => 'right',
					),
				),
				array(
					'heading'     => esc_html__( 'Content Vertical Alignment', 'sober-addons' ),
					'description' => esc_html__( 'Vertical alignment of banner text', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'align_vertical',
					'group'       => esc_html__( 'Text', 'sober-addons' ),
					'value'       => array(
						esc_html__( 'Top', 'sober-addons' )    => 'top',
						esc_html__( 'Middle', 'sober-addons' ) => 'middle',
						esc_html__( 'Bottom', 'sober-addons' ) => 'bottom',
					),
				),
			),
		) );

		// Category Banner
		vc_map( array(
			'name'        => esc_html__( 'Category Banner', 'sober-addons' ),
			'description' => esc_html__( 'Banner image with special style', 'sober-addons' ),
			'base'        => 'sober_category_banner',
			'icon'        => $this->get_icon( 'category-banner.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image', 'sober-addons' ),
					'description' => esc_html__( 'Banner Image', 'sober-addons' ),
					'param_name'  => 'image',
					'type'        => 'attach_image',
				),
				array(
					'heading'     => esc_html__( 'Image Position', 'sober-addons' ),
					'description' => esc_html__( 'Select image position', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'image_position',
					'value'       => array(
						esc_html__( 'Left', 'sober-addons' )         => 'left',
						esc_html__( 'Right', 'sober-addons' )        => 'right',
						esc_html__( 'Top', 'sober-addons' )          => 'top',
						esc_html__( 'Bottom', 'sober-addons' )       => 'bottom',
						esc_html__( 'Top Left', 'sober-addons' )     => 'top-left',
						esc_html__( 'Top Right', 'sober-addons' )    => 'top-right',
						esc_html__( 'Bottom Left', 'sober-addons' )  => 'bottom-left',
						esc_html__( 'Bottom Right', 'sober-addons' ) => 'bottom-right',
					),
				),
				array(
					'heading'     => esc_html__( 'Title', 'sober-addons' ),
					'description' => esc_html__( 'The banner title', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'title',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Description', 'sober-addons' ),
					'description' => esc_html__( 'The banner description', 'sober-addons' ),
					'type'        => 'textarea',
					'param_name'  => 'content',
				),
				array(
					'heading'     => esc_html__( 'Text Position', 'sober-addons' ),
					'description' => esc_html__( 'Select the position for title and description', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'text_position',
					'value'       => array(
						esc_html__( 'Top Left', 'sober-addons' )     => 'top-left',
						esc_html__( 'Top Right', 'sober-addons' )    => 'top-right',
						esc_html__( 'Middle Left', 'sober-addons' )  => 'middle-left',
						esc_html__( 'Middle Right', 'sober-addons' ) => 'middle-right',
						esc_html__( 'Bottom Left', 'sober-addons' )  => 'bottom-left',
						esc_html__( 'Bottom Right', 'sober-addons' ) => 'bottom-right',
					),
				),
				array(
					'heading'    => esc_html__( 'Link (URL)', 'sober-addons' ),
					'type'       => 'vc_link',
					'param_name' => 'link',
				),
				array(
					'heading'     => esc_html__( 'Button Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter the text for banner button', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'button_text',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
				array(
					'heading'    => __( 'CSS box', 'sober-addons' ),
					'type'       => 'css_editor',
					'param_name' => 'css',
					'group'      => esc_html__( 'Design Options', 'sober-addons' ),
				),
			),
		) );

		// Product
		vc_map( array(
			'name'        => esc_html__( 'Sober Product', 'sober-addons' ),
			'description' => esc_html__( 'Display single product banner', 'sober-addons' ),
			'base'        => 'sober_product',
			'icon'        => $this->get_icon( 'product.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Button Behaviour', 'sober-addons' ),
					'description' => esc_html__( 'Select color scheme for product', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'button_behaviour',
					'value'       => array(
						esc_html__( 'Link to product', 'sober-addons' ) => 'link',
						esc_html__( 'Add to Cart', 'sober-addons' )     => 'add_to_cart',
					),
					'std' => 'link',
				),
				array(
					'heading'     => esc_html__( 'Product ID', 'sober-addons' ),
					'description' => esc_html__( 'Enter product ID. Allow number only.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'product_id',
					'admin_label' => true,
					'dependency'  => array(
						'element' => 'button_behaviour',
						'value'   => 'add_to_cart',
					),
				),
				array(
					'heading'    => esc_html__( 'Product URL', 'sober-addons' ),
					'type'       => 'vc_link',
					'param_name' => 'link',
					'dependency'  => array(
						'element' => 'button_behaviour',
						'value'   => 'link',
					),
				),
				array(
					'heading'     => esc_html__( 'Images', 'sober-addons' ),
					'description' => esc_html__( 'Upload a product image', 'sober-addons' ),
					'param_name'  => 'image',
					'type'        => 'attach_image',
					'value'       => '',
				),
				array(
					'heading'     => esc_html__( 'Product name', 'sober-addons' ),
					'description' => esc_html__( 'Enter product name', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'title',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Product description', 'sober-addons' ),
					'description' => esc_html__( 'Enter product description', 'sober-addons' ),
					'type'        => 'textarea',
					'param_name'  => 'content',
				),
				array(
					'heading'     => esc_html__( 'Product price', 'sober-addons' ),
					'description' => esc_html__( 'Enter product price. Only allow number.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'price',
					'dependency'  => array(
						'element' => 'button_behaviour',
						'value'   => 'link',
					),
				),
				array(
					'heading'     => esc_html__( 'Text Color Scheme', 'sober-addons' ),
					'description' => esc_html__( 'Select color scheme for product', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'scheme',
					'value'       => array(
						esc_html__( 'Light', 'sober-addons' ) => 'light',
						esc_html__( 'Dark', 'sober-addons' )  => 'dark',
					),
					'std' => 'light',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Banner Grid 4
		vc_map( array(
			'name'                    => esc_html__( 'Banner Grid 4', 'sober-addons' ),
			'description'             => esc_html__( 'Arrange 4 banners per row with unusual structure.', 'sober-addons' ),
			'base'                    => 'sober_banner_grid_4',
			'icon'                    => $this->get_icon( 'banner-grid-4.png' ),
			'category'                => esc_html__( 'Sober', 'sober-addons' ),
			'js_view'                 => 'VcColumnView',
			'content_element'         => true,
			'show_settings_on_create' => false,
			'as_parent'               => array( 'only' => 'sober_banner,sober_banner2,sober_banner3' ),
			'params'                  => array(
				array(
					'heading'     => esc_html__( 'Reverse Order', 'sober-addons' ),
					'description' => esc_html__( 'Reverse the order of banners inside this grid', 'sober-addons' ),
					'param_name'  => 'reverse',
					'type'        => 'checkbox',
					'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
				),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Banner Grid 5
		vc_map( array(
			'name'                    => esc_html__( 'Banner Grid 5', 'sober-addons' ),
			'description'             => esc_html__( 'Arrange 5 banners in 3 columns.', 'sober-addons' ),
			'base'                    => 'sober_banner_grid_5',
			'icon'                    => $this->get_icon( 'banner-grid-5.png' ),
			'category'                => esc_html__( 'Sober', 'sober-addons' ),
			'js_view'                 => 'VcColumnView',
			'content_element'         => true,
			'show_settings_on_create' => false,
			'as_parent'               => array( 'only' => 'sober_banner,sober_banner2,sober_banner3' ),
			'params'                  => array(
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Banner Grid 5 v2
		vc_map( array(
			'name'                    => esc_html__( 'Banner Grid 5 (v2)', 'sober-addons' ),
			'description'             => esc_html__( 'Arrange 5 banners in 2 rows.', 'sober-addons' ),
			'base'                    => 'sober_banner_grid_5_2',
			'icon'                    => $this->get_icon( 'banner-grid-5-v2.png' ),
			'category'                => esc_html__( 'Sober', 'sober-addons' ),
			'js_view'                 => 'VcColumnView',
			'content_element'         => true,
			'show_settings_on_create' => false,
			'as_parent'               => array( 'only' => 'sober_banner,sober_banner2,sober_banner3,sober_banner4' ),
			'params'                  => array(
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Banner Grid 6
		vc_map( array(
			'name'                    => esc_html__( 'Banner Grid 6', 'sober-addons' ),
			'description'             => esc_html__( 'Arrange 6 banners in 4 columns.', 'sober-addons' ),
			'base'                    => 'sober_banner_grid_6',
			'icon'                    => $this->get_icon( 'banner-grid-6.png' ),
			'category'                => esc_html__( 'Sober', 'sober-addons' ),
			'js_view'                 => 'VcColumnView',
			'content_element'         => true,
			'show_settings_on_create' => false,
			'as_parent'               => array( 'only' => 'sober_banner,sober_banner2,sober_banner3' ),
			'params'                  => array(
				array(
					'heading'     => esc_html__( 'Reverse Order', 'sober-addons' ),
					'description' => esc_html__( 'Reverse the order of banners inside this grid', 'sober-addons' ),
					'param_name'  => 'reverse',
					'type'        => 'checkbox',
					'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
				),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Circle Chart
		vc_map( array(
			'name'        => esc_html__( 'Circle Chart', 'sober-addons' ),
			'description' => esc_html__( 'Circle chart with animation', 'sober-addons' ),
			'base'        => 'sober_chart',
			'icon'        => $this->get_icon( 'chart.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Value', 'sober-addons' ),
					'description' => esc_html__( 'Enter the chart value in percentage. Minimum 0 and maximum 100.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'value',
					'value'       => 100,
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Circle Size', 'sober-addons' ),
					'description' => esc_html__( 'Width of the circle', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'size',
					'value'       => 200,
				),
				array(
					'heading'     => esc_html__( 'Circle thickness', 'sober-addons' ),
					'description' => esc_html__( 'Width of the arc', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'thickness',
					'value'       => 8,
				),
				array(
					'heading'     => esc_html__( 'Color', 'sober-addons' ),
					'description' => esc_html__( 'Pick color for the circle', 'sober-addons' ),
					'type'        => 'colorpicker',
					'param_name'  => 'color',
					'value'       => '#6dcff6',
				),
				array(
					'heading'     => esc_html__( 'Label Source', 'sober-addons' ),
					'description' => esc_html__( 'Chart label source', 'sober-addons' ),
					'param_name'  => 'label_source',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Auto', 'sober-addons' )   => 'auto',
						esc_html__( 'Custom', 'sober-addons' ) => 'custom',
					),
				),
				array(
					'heading'     => esc_html__( 'Custom label', 'sober-addons' ),
					'description' => esc_html__( 'Text label for the chart', 'sober-addons' ),
					'param_name'  => 'label',
					'type'        => 'textfield',
					'dependency'  => array(
						'element' => 'label_source',
						'value'   => 'custom',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// Message Box
		vc_map( array(
			'name'        => esc_html__( 'Sober Message Box', 'sober-addons' ),
			'description' => esc_html__( 'Notification box with close button', 'sober-addons' ),
			'base'        => 'sober_message_box',
			'icon'        => $this->get_icon( 'message-box.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'          => esc_html__( 'Type', 'sober-addons' ),
					'description'      => esc_html__( 'Select message box type', 'sober-addons' ),
					'edit_field_class' => 'vc_col-xs-12 vc_message-type',
					'type'             => 'dropdown',
					'param_name'       => 'type',
					'default'          => 'success',
					'admin_label'      => true,
					'value'            => array(
						esc_html__( 'Success', 'sober-addons' )       => 'success',
						esc_html__( 'Informational', 'sober-addons' ) => 'info',
						esc_html__( 'Error', 'sober-addons' )         => 'danger',
						esc_html__( 'Warning', 'sober-addons' )       => 'warning',
					),
				),
				array(
					'heading'    => esc_html__( 'Message Text', 'sober-addons' ),
					'type'       => 'textarea_html',
					'param_name' => 'content',
					'holder'     => 'div',
				),
				array(
					'heading'     => esc_html__( 'Closeable', 'sober-addons' ),
					'description' => esc_html__( 'Display close button for this box', 'sober-addons' ),
					'type'        => 'checkbox',
					'param_name'  => 'closeable',
					'value'       => array(
						esc_html__( 'Yes', 'sober-addons' ) => true,
					),
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
				),
			),
		) );

		// Icon Box
		vc_map( array(
			'name'        => esc_html__( 'Icon Box', 'sober-addons' ),
			'description' => esc_html__( 'Information box with icon', 'sober-addons' ),
			'base'        => 'sober_icon_box',
			'icon'        => $this->get_icon( 'icon-box.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Icon library', 'sober-addons' ),
					'description' => esc_html__( 'Select icon library.', 'sober-addons' ),
					'param_name'  => 'icon_type',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Font Awesome', 'sober-addons' )   => 'fontawesome',
						esc_html__( 'Open Iconic', 'sober-addons' )    => 'openiconic',
						esc_html__( 'Typicons', 'sober-addons' )       => 'typicons',
						esc_html__( 'Entypo', 'sober-addons' )         => 'entypo',
						esc_html__( 'Linecons', 'sober-addons' )       => 'linecons',
						esc_html__( 'Mono Social', 'sober-addons' )    => 'monosocial',
						esc_html__( 'Material', 'sober-addons' )       => 'material',
						esc_html__( 'Custom Image', 'sober-addons' )   => 'image',
						esc_html__( 'External Image', 'sober-addons' ) => 'external_link',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon', 'sober-addons' ),
					'description' => esc_html__( 'Select icon from library.', 'sober-addons' ),
					'type'        => 'iconpicker',
					'param_name'  => 'icon_fontawesome',
					'value'       => 'fa fa-adjust',
					'settings'    => array(
						'emptyIcon'    => false,
						'iconsPerPage' => 4000,
					),
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'fontawesome',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon', 'sober-addons' ),
					'description' => esc_html__( 'Select icon from library.', 'sober-addons' ),
					'type'        => 'iconpicker',
					'param_name'  => 'icon_openiconic',
					'value'       => 'vc-oi vc-oi-dial',
					'settings'    => array(
						'emptyIcon'    => false,
						'type'         => 'openiconic',
						'iconsPerPage' => 4000,
					),
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'openiconic',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon', 'sober-addons' ),
					'description' => esc_html__( 'Select icon from library.', 'sober-addons' ),
					'type'        => 'iconpicker',
					'param_name'  => 'icon_typicons',
					'value'       => 'typcn typcn-adjust-brightness',
					'settings'    => array(
						'emptyIcon'    => false,
						'type'         => 'typicons',
						'iconsPerPage' => 4000,
					),
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'typicons',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon', 'sober-addons' ),
					'description' => esc_html__( 'Select icon from library.', 'sober-addons' ),
					'type'        => 'iconpicker',
					'param_name'  => 'icon_entypo',
					'value'       => 'entypo-icon entypo-icon-note',
					'settings'    => array(
						'emptyIcon'    => false,
						'type'         => 'entypo',
						'iconsPerPage' => 4000,
					),
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'entypo',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon', 'sober-addons' ),
					'description' => esc_html__( 'Select icon from library.', 'sober-addons' ),
					'type'        => 'iconpicker',
					'param_name'  => 'icon_linecons',
					'value'       => 'vc_li vc_li-heart',
					'settings'    => array(
						'emptyIcon'    => false,
						'type'         => 'linecons',
						'iconsPerPage' => 4000,
					),
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'linecons',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon', 'sober-addons' ),
					'description' => esc_html__( 'Select icon from library.', 'sober-addons' ),
					'type'        => 'iconpicker',
					'param_name'  => 'icon_monosocial',
					'value'       => 'vc-mono vc-mono-fivehundredpx',
					'settings'    => array(
						'emptyIcon'    => false,
						'type'         => 'monosocial',
						'iconsPerPage' => 4000,
					),
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'monosocial',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon', 'sober-addons' ),
					'description' => esc_html__( 'Select icon from library.', 'sober-addons' ),
					'type'        => 'iconpicker',
					'param_name'  => 'icon_material',
					'value'       => 'vc-material vc-material-cake',
					'settings'    => array(
						'emptyIcon'    => false,
						'type'         => 'material',
						'iconsPerPage' => 4000,
					),
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'material',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon Image', 'sober-addons' ),
					'description' => esc_html__( 'Upload icon image', 'sober-addons' ),
					'type'        => 'attach_image',
					'param_name'  => 'image',
					'value'       => '',
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'image',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon Image URL', 'sober-addons' ),
					'description' => esc_html__( 'Enter image URL', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_url',
					'value'       => '',
					'dependency'  => array(
						'element' => 'icon_type',
						'value'   => 'external_link',
					),
				),
				array(
					'heading'     => esc_html__( 'Icon Style', 'sober-addons' ),
					'description' => esc_html__( 'Select icon style', 'sober-addons' ),
					'param_name'  => 'style',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Normal', 'sober-addons' ) => 'normal',
						esc_html__( 'Circle', 'sober-addons' ) => 'circle',
						esc_html__( 'Round', 'sober-addons' )  => 'round',
					),
				),
				array(
					'heading'     => esc_html__( 'Title', 'sober-addons' ),
					'description' => esc_html__( 'The box title', 'sober-addons' ),
					'admin_label' => true,
					'param_name'  => 'title',
					'type'        => 'textfield',
					'value'       => esc_html__( 'I am Icon Box', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Content', 'sober-addons' ),
					'description' => esc_html__( 'The box title', 'sober-addons' ),
					'holder'      => 'div',
					'param_name'  => 'content',
					'type'        => 'textarea_html',
					'value'       => esc_html__( 'I am icon box. Click edit button to change this text.', 'sober-addons' ),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// Pricing Table
		vc_map( array(
			'name'        => esc_html__( 'Pricing Table', 'sober-addons' ),
			'description' => esc_html__( 'Eye catching pricing table', 'sober-addons' ),
			'base'        => 'sober_pricing_table',
			'icon'        => $this->get_icon( 'pricing-table.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Plan Name', 'sober-addons' ),
					'admin_label' => true,
					'param_name'  => 'name',
					'type'        => 'textfield',
				),
				array(
					'heading'     => esc_html__( 'Price', 'sober-addons' ),
					'description' => esc_html__( 'Plan pricing', 'sober-addons' ),
					'param_name'  => 'price',
					'type'        => 'textfield',
				),
				array(
					'heading'     => esc_html__( 'Currency', 'sober-addons' ),
					'description' => esc_html__( 'Price currency', 'sober-addons' ),
					'param_name'  => 'currency',
					'type'        => 'textfield',
					'value'       => '$',
				),
				array(
					'heading'     => esc_html__( 'Recurrence', 'sober-addons' ),
					'description' => esc_html__( 'Recurring payment unit', 'sober-addons' ),
					'param_name'  => 'recurrence',
					'type'        => 'textfield',
					'value'       => esc_html__( 'Per Month', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Features', 'sober-addons' ),
					'description' => esc_html__( 'Feature list of this plan. Click to arrow button to edit.', 'sober-addons' ),
					'param_name'  => 'features',
					'type'        => 'param_group',
					'params'      => array(
						array(
							'heading'    => esc_html__( 'Feature name', 'sober-addons' ),
							'param_name' => 'name',
							'type'       => 'textfield',
						),
						array(
							'heading'    => esc_html__( 'Feature value', 'sober-addons' ),
							'param_name' => 'value',
							'type'       => 'textfield',
						),
					),
				),
				array(
					'heading'    => esc_html__( 'Button Text', 'sober-addons' ),
					'param_name' => 'button_text',
					'type'       => 'textfield',
					'value'      => esc_html__( 'Get Started', 'sober-addons' ),
				),
				array(
					'heading'    => esc_html__( 'Button Link', 'sober-addons' ),
					'param_name' => 'button_link',
					'type'       => 'vc_link',
					'value'      => esc_html__( 'Get Started', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Table color', 'sober-addons' ),
					'description' => esc_html__( 'Pick color scheme for this table. It will be applied to table header and button.', 'sober-addons' ),
					'param_name'  => 'color',
					'type'        => 'colorpicker',
					'value'       => '#6dcff6',
				),
				vc_map_add_css_animation(),
				array(
					'type'        => 'textfield',
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'param_name'  => 'el_class',
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
				),
			),
		) );

		// Google Map
		vc_map( array(
			'name'        => esc_html__( 'Sober Maps', 'sober-addons' ),
			'description' => esc_html__( 'Google maps in style', 'sober-addons' ),
			'base'        => 'sober_map',
			'icon'        => $this->get_icon( 'map.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'API Key', 'sober-addons' ),
					'description' => esc_html__( 'Google requires an API key to work.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'api_key',
				),
				array(
					'heading'     => esc_html__( 'Address', 'sober-addons' ),
					'description' => esc_html__( 'Enter address for map marker. If this option does not work correctly, use the Latitude and Longitude options bellow.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'address',
					'admin_label' => true,
				),
				array(
					'heading'          => esc_html__( 'Latitude', 'sober-addons' ),
					'type'             => 'textfield',
					'edit_field_class' => 'vc_col-xs-6',
					'param_name'       => 'lat',
					'admin_label'      => true,
				),
				array(
					'heading'          => esc_html__( 'Longitude', 'sober-addons' ),
					'type'             => 'textfield',
					'param_name'       => 'lng',
					'edit_field_class' => 'vc_col-xs-6',
					'admin_label'      => true,
				),
				array(
					'heading'     => esc_html__( 'Marker', 'sober-addons' ),
					'description' => esc_html__( 'Upload custom marker icon or leave this to use default marker.', 'sober-addons' ),
					'param_name'  => 'marker',
					'type'        => 'attach_image',
				),
				array(
					'heading'     => esc_html__( 'Width', 'sober-addons' ),
					'description' => esc_html__( 'Map width in pixel or percentage.', 'sober-addons' ),
					'param_name'  => 'width',
					'type'        => 'textfield',
					'value'       => '100%',
				),
				array(
					'heading'     => esc_html__( 'Height', 'sober-addons' ),
					'description' => esc_html__( 'Map height in pixel.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'height',
					'value'       => '625px',
				),
				array(
					'heading'     => esc_html__( 'Zoom', 'sober-addons' ),
					'description' => esc_html__( 'Enter zoom level. The value is between 1 and 20.', 'sober-addons' ),
					'param_name'  => 'zoom',
					'type'        => 'textfield',
					'value'       => '15',
				),
				array(
					'heading'          => esc_html__( 'Color', 'sober-addons' ),
					'description'      => esc_html__( 'Select map color style', 'sober-addons' ),
					'edit_field_class' => 'vc_col-xs-12 vc_btn3-colored-dropdown vc_colored-dropdown',
					'param_name'       => 'color',
					'type'             => 'dropdown',
					'value'            => array(
						esc_html__( 'Default', 'sober-addons' )       => '',
						esc_html__( 'Grey', 'sober-addons' )          => 'grey',
						esc_html__( 'Classic Black', 'sober-addons' ) => 'inverse',
						esc_html__( 'Vista Blue', 'sober-addons' )    => 'vista-blue',
					),
				),
				array(
					'heading'     => esc_html__( 'Content', 'sober-addons' ),
					'description' => esc_html__( 'Enter content of info window.', 'sober-addons' ),
					'type'        => 'textarea_html',
					'param_name'  => 'content',
					'holder'      => 'div',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// Open Street Map
		vc_map( array(
			'name'        => esc_html__( 'Sober Maps 2', 'sober-addons' ),
			'description' => esc_html__( 'Open Street Map in style', 'sober-addons' ),
			'base'        => 'sober_map2',
			'icon'        => $this->get_icon( 'map.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Address', 'sober-addons' ),
					'description' => esc_html__( 'Enter address for map marker.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'address',
					'admin_label' => true,
				),
				array(
					'heading'          => esc_html__( 'Latitude', 'sober-addons' ),
					'type'             => 'textfield',
					'edit_field_class' => 'vc_col-xs-6',
					'param_name'       => 'lat',
					'admin_label'      => true,
				),
				array(
					'heading'          => esc_html__( 'Longitude', 'sober-addons' ),
					'type'             => 'textfield',
					'param_name'       => 'lng',
					'edit_field_class' => 'vc_col-xs-6',
					'admin_label'      => true,
				),
				array(
					'heading'     => esc_html__( 'Height', 'sober-addons' ),
					'description' => esc_html__( 'Map height in pixel.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'height',
					'value'       => '625px',
				),
				array(
					'heading'     => esc_html__( 'Zoom', 'sober-addons' ),
					'description' => esc_html__( 'Enter zoom level. The value is between 1 and 20.', 'sober-addons' ),
					'param_name'  => 'zoom',
					'type'        => 'textfield',
					'value'       => '15',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// Testimonial
		vc_map( array(
			'name'        => esc_html__( 'Testimonial', 'sober-addons' ),
			'description' => esc_html__( 'Written review from a satisfied customer', 'sober-addons' ),
			'base'        => 'sober_testimonial',
			'icon'        => $this->get_icon( 'testimonial.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Photo', 'sober-addons' ),
					'description' => esc_html__( 'Author photo or avatar. Recommend 160x160 in dimension.', 'sober-addons' ),
					'type'        => 'attach_image',
					'param_name'  => 'image',
				),
				array(
					'heading'     => esc_html__( 'Name', 'sober-addons' ),
					'description' => esc_html__( 'Enter full name of the author', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'name',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Company', 'sober-addons' ),
					'description' => esc_html__( 'Enter company name of author', 'sober-addons' ),
					'param_name'  => 'company',
					'type'        => 'textfield',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Alignment', 'sober-addons' ),
					'description' => esc_html__( 'Select testimonial alignment', 'sober-addons' ),
					'param_name'  => 'align',
					'type'        => 'dropdown',
					'value'       => array(
						esc_html__( 'Center', 'sober-addons' ) => 'center',
						esc_html__( 'Left', 'sober-addons' )   => 'left',
						esc_html__( 'Right', 'sober-addons' )  => 'right',
					),
				),
				array(
					'heading'     => esc_html__( 'Content', 'sober-addons' ),
					'description' => esc_html__( 'Testimonial content', 'sober-addons' ),
					'type'        => 'textarea_html',
					'param_name'  => 'content',
					'holder'      => 'div',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// Partners
		vc_map( array(
			'name'        => esc_html__( 'Partner Logos', 'sober-addons' ),
			'description' => esc_html__( 'Show list of partner logo', 'sober-addons' ),
			'base'        => 'sober_partners',
			'icon'        => $this->get_icon( 'partners.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image source', 'sober-addons' ),
					'description' => esc_html__( 'Select images source', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'source',
					'value'       => array(
						esc_html__( 'Media library', 'sober-addons' )  => 'media_library',
						esc_html__( 'External Links', 'sober-addons' ) => 'external_link',
					),
				),
				array(
					'heading'     => esc_html__( 'Images', 'sober-addons' ),
					'description' => esc_html__( 'Select images from media library', 'sober-addons' ),
					'type'        => 'attach_images',
					'param_name'  => 'images',
					'dependency'  => array(
						'element' => 'source',
						'value'   => 'media_library',
					),
				),
				array(
					'heading'     => esc_html__( 'Image size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)). Leave empty to use "thumbnail" size.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_size',
					'dependency'  => array(
						'element' => 'source',
						'value'   => 'media_library',
					),
				),
				array(
					'heading'     => esc_html__( 'External links', 'sober-addons' ),
					'description' => esc_html__( 'Enter external links for partner logos (Note: divide links with linebreaks (Enter)).', 'sober-addons' ),
					'type'        => 'exploded_textarea_safe',
					'param_name'  => 'custom_srcs',
					'dependency'  => array(
						'element' => 'source',
						'value'   => 'external_link',
					),
				),
				array(
					'heading'     => esc_html__( 'Image size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size in pixels. Example: 200x100 (Width x Height).', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'external_img_size',
					'dependency'  => array(
						'element' => 'source',
						'value'   => 'external_link',
					),
				),
				array(
					'heading'     => esc_html__( 'Custom links', 'sober-addons' ),
					'description' => esc_html__( 'Enter links for each image here. Divide links with linebreaks (Enter).', 'sober-addons' ),
					'type'        => 'exploded_textarea_safe',
					'param_name'  => 'custom_links',
				),
				array(
					'heading'     => esc_html__( 'Custom link target', 'sober-addons' ),
					'description' => esc_html__( 'Select where to open custom links.', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'custom_links_target',
					'value'       => array(
						esc_html__( 'Same window', 'sober-addons' ) => '_self',
						esc_html__( 'New window', 'sober-addons' )  => '_blank',
					),
				),
				array(
					'heading'     => esc_html__( 'Layout', 'sober-addons' ),
					'description' => esc_html__( 'Select the layout images source', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'layout',
					'value'       => array(
						esc_html__( 'Bordered', 'sober-addons' ) => 'bordered',
						esc_html__( 'Plain', 'sober-addons' )    => 'plain',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Contact Box
		vc_map( array(
			'name'        => esc_html__( 'Contact Box', 'sober-addons' ),
			'description' => esc_html__( 'Contact information', 'sober-addons' ),
			'base'        => 'sober_contact_box',
			'icon'        => $this->get_icon( 'contact.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Address', 'sober-addons' ),
					'description' => esc_html__( 'The office address', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'address',
					'holder'      => 'p',
				),
				array(
					'heading'     => esc_html__( 'Phone', 'sober-addons' ),
					'description' => esc_html__( 'The phone number', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'phone',
					'holder'      => 'p',
				),
				array(
					'heading'     => esc_html__( 'Fax', 'sober-addons' ),
					'description' => esc_html__( 'The fax number', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'fax',
					'holder'      => 'p',
				),
				array(
					'heading'     => esc_html__( 'Email', 'sober-addons' ),
					'description' => esc_html__( 'The email adress', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'email',
					'holder'      => 'p',
				),
				array(
					'heading'     => esc_html__( 'Website', 'sober-addons' ),
					'description' => esc_html__( 'The phone number', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'website',
					'holder'      => 'p',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// Info List
		vc_map( array(
			'name'        => esc_html__( 'Info List', 'sober-addons' ),
			'description' => esc_html__( 'List of information', 'sober-addons' ),
			'base'        => 'sober_info_list',
			'icon'        => $this->get_icon( 'info-list.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Information', 'sober-addons' ),
					'description' => esc_html__( 'Enter information', 'sober-addons' ),
					'type'        => 'param_group',
					'param_name'  => 'info',
					'value'       => urlencode( json_encode( array(
						array(
							'icon'  => 'fas fa-map-marker-alt',
							'label' => esc_html__( 'Address', 'sober-addons' ),
							'value' => '9606 North MoPac Expressway',
						),
						array(
							'icon'  => 'fas fa-phone-alt',
							'label' => esc_html__( 'Phone', 'sober-addons' ),
							'value' => '+1 248-785-8545',
						),
						array(
							'icon'  => 'fas fa-fax',
							'label' => esc_html__( 'Fax', 'sober-addons' ),
							'value' => '123123123',
						),
						array(
							'icon'  => 'far fa-envelope',
							'label' => esc_html__( 'Email', 'sober-addons' ),
							'value' => 'sober@uix.store',
						),
						array(
							'icon'  => 'fas fa-globe',
							'label' => esc_html__( 'Website', 'sober-addons' ),
							'value' => 'http://uix.store',
						),
					) ) ),
					'params'      => array(
						array(
							'type'       => 'iconpicker',
							'heading'    => esc_html__( 'Icon', 'sober-addons' ),
							'param_name' => 'icon',
							'settings'   => array(
								'emptyIcon'    => false,
								'iconsPerPage' => 4000,
							),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Label', 'sober-addons' ),
							'param_name'  => 'label',
							'admin_label' => true,
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Value', 'sober-addons' ),
							'param_name'  => 'value',
							'admin_label' => true,
						),
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// FAQ
		vc_map( array(
			'name'        => esc_html__( 'FAQ', 'sober-addons' ),
			'description' => esc_html__( 'Question and answer toggle', 'sober-addons' ),
			'base'        => 'sober_faq',
			'icon'        => $this->get_icon( 'faq.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'js_view'     => 'VcToggleView',
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Question', 'sober-addons' ),
					'description' => esc_html__( 'Enter title of toggle block.', 'sober-addons' ),
					'type'        => 'textfield',
					'holder'      => 'h4',
					'class'       => 'vc_toggle_title wpb_element_title',
					'param_name'  => 'title',
					'value'       => esc_html__( 'Question content goes here', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Answer', 'sober-addons' ),
					'description' => esc_html__( 'Toggle block content.', 'sober-addons' ),
					'type'        => 'textarea_html',
					'holder'      => 'div',
					'class'       => 'vc_toggle_content',
					'param_name'  => 'content',
					'value'       => esc_html__( 'Answer content goes here, click edit button to change this text.', 'sober-addons' ),
				),
				array(
					'heading'     => esc_html__( 'Default state', 'sober-addons' ),
					'description' => esc_html__( 'Select "Open" if you want toggle to be open by default.', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'open',
					'value'       => array(
						esc_html__( 'Closed', 'sober-addons' ) => 'false',
						esc_html__( 'Open', 'sober-addons' )   => 'true',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Team Member
		vc_map( array(
			'name'        => esc_html__( 'Team Member', 'sober-addons' ),
			'description' => esc_html__( 'Single team member information', 'sober-addons' ),
			'base'        => 'sober_team_member',
			'icon'        => $this->get_icon( 'member.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image', 'sober-addons' ),
					'description' => esc_html__( 'Member photo', 'sober-addons' ),
					'param_name'  => 'image',
					'type'        => 'attach_image',
				),
				array(
					'heading'     => esc_html__( 'Image Size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)). Leave empty to use "thumbnail" size.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_size',
					'value'       => 'full',
				),
				array(
					'heading'     => esc_html__( 'Full Name', 'sober-addons' ),
					'description' => esc_html__( 'Member name', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'name',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Job', 'sober-addons' ),
					'description' => esc_html__( 'The job/position name of member in your team', 'sober-addons' ),
					'param_name'  => 'job',
					'type'        => 'textfield',
					'admin_label' => true,
				),
				array(
					'heading'    => esc_html__( 'Facebook', 'sober-addons' ),
					'type'       => 'textfield',
					'param_name' => 'facebook',
				),
				array(
					'heading'    => esc_html__( 'Twitter', 'sober-addons' ),
					'type'       => 'textfield',
					'param_name' => 'twitter',
				),
				array(
					'heading'    => esc_html__( 'Pinterest', 'sober-addons' ),
					'type'       => 'textfield',
					'param_name' => 'pinterest',
				),
				array(
					'heading'    => esc_html__( 'Linkedin', 'sober-addons' ),
					'type'       => 'textfield',
					'param_name' => 'linkedin',
				),
				array(
					'heading'    => esc_html__( 'Youtube', 'sober-addons' ),
					'type'       => 'textfield',
					'param_name' => 'youtube',
				),
				array(
					'heading'    => esc_html__( 'Instagram', 'sober-addons' ),
					'type'       => 'textfield',
					'param_name' => 'instagram',
				),
				array(
					'heading'    => esc_html__( 'Email', 'sober-addons' ),
					'type'       => 'textfield',
					'param_name' => 'email',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
				),
			),
		) );

		// Subscribe Box.
		$forms = get_posts( array( 'post_type' => 'mc4wp-form', 'numberposts' => -1 ));

		if ( $forms ) {
			$options = array();

			foreach( $forms as $form ) {
				$options[$form->post_title . " - ID: $form->ID"] = $form->ID;
			}

			vc_map(array(
				'name' => esc_html__('Subscribe Box', 'sober-addons' ),
				'description' => esc_html__('MailChimp subscribe form', 'sober-addons' ),
				'base' => 'sober_subscribe_box',
				'icon' => $this->get_icon('mail.png'),
				'category' => esc_html__( 'Sober', 'sober-addons' ),
				'params' => array(
					array(
						'heading' => esc_html__( 'Title', 'sober-addons' ),
						'admin_label' => true,
						'type' => 'textfield',
						'param_name' => 'title',
					),
					array(
						'heading' => esc_html__( 'Description', 'sober-addons' ),
						'admin_label' => true,
						'type' => 'textarea',
						'param_name' => 'content',
					),
					array(
						'heading' => esc_html__( 'Form', 'sober-addons' ),
						'description' => esc_html__( 'Select the MailChimp form', 'sober-addons' ),
						'param_name' => 'form_id',
						'type' => 'dropdown',
						'value' => $options,
					),
					array(
						'heading' => esc_html__( 'Form Style', 'sober-addons' ),
						'description' => esc_html__( 'Select the style for this form', 'sober-addons' ),
						'param_name' => 'form_style',
						'type' => 'dropdown',
						'std' => 'default',
						'value' => array(
							esc_html__( 'Default', 'sober-addons' ) => 'default',
							esc_html__( 'Inline' ) => 'inline',
						),
					),
					vc_map_add_css_animation(),
					array(
						'heading' => esc_html__( 'Extra class name', 'sober-addons' ),
						'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
						'param_name' => 'el_class',
						'type' => 'textfield',
						'value' => '',
					),
					array(
						'type' => 'css_editor',
						'heading' => esc_html__( 'CSS box', 'sober-addons' ),
						'param_name' => 'css',
						'group' => esc_html__( 'Design Options', 'sober-addons' ),
					),
				),
			));
		}

		// Banner Simple
		vc_map( array(
			'name'        => esc_html__( 'Simple Banner', 'sober-addons' ),
			'description' => esc_html__( 'Simple banner image with text bellow', 'sober-addons' ),
			'base'        => 'sober_banner_simple',
			'icon'        => $this->get_icon( 'banner.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Image Source', 'sober-addons' ),
					'description' => esc_html__( 'Select image source.', 'sober-addons' ),
					'param_name'  => 'image_source',
					'type'        => 'dropdown',
					'std'         => 'media_library',
					'value'       => array(
						esc_html__( 'Media library', 'sober-addons' )  => 'media_library',
						esc_html__( 'External Links', 'sober-addons' ) => 'external_link',
					),
				),
				array(
					'heading'     => esc_html__( 'Image', 'sober-addons' ),
					'description' => esc_html__( 'Select image from media library', 'sober-addons' ),
					'type'        => 'attach_image',
					'param_name'  => 'image',
					'dependency'  => array(
						'element' => 'image_source',
						'value'   => 'media_library',
					),
				),
				array(
					'heading'     => esc_html__( 'Image size', 'sober-addons' ),
					'description' => esc_html__( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)). Leave empty to use "thumbnail" size.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_size',
					'value'       => 'full',
					'dependency'  => array(
						'element' => 'image_source',
						'value'   => 'media_library',
					),
				),
				array(
					'heading'     => esc_html__( 'External link', 'sober-addons' ),
					'description' => esc_html__( 'Enter external link of the image.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'image_url',
					'dependency'  => array(
						'element' => 'image_source',
						'value'   => 'external_link',
					),
				),
				array(
					'heading'     => esc_html__( 'Text', 'sober-addons' ),
					'description' => esc_html__( 'Enter the banner text', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'text',
					'admin_label' => true,
				),
				array(
					'heading'     => esc_html__( 'Alignment', 'sober-addons' ),
					'description' => esc_html__( 'Select image & text alignment', 'sober-addons' ),
					'type'        => 'dropdown',
					'param_name'  => 'text_position',
					'std'         => 'center',
					'value'       => array(
						esc_html__( 'Left', 'sober-addons' )   => 'left',
						esc_html__( 'Center', 'sober-addons' ) => 'center',
						esc_html__( 'Right', 'sober-addons' )  => 'right',
					),
				),


				array(
					'heading'    => esc_html__( 'Link (URL)', 'sober-addons' ),
					'type'       => 'vc_link',
					'param_name' => 'link',
				),
				array(
					'heading'     => esc_html__( 'Button Text (optional)', 'sober-addons' ),
					'description' => esc_html__( 'Display a button at bottom', 'sober-addons' ),
					'param_name'  => 'button_text',
					'type'        => 'textfield',
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
				array(
					'heading'    => esc_html__( 'CSS box', 'sober-addons' ),
					'type'       => 'css_editor',
					'param_name' => 'css',
					'group'      => esc_html__( 'Design Options', 'sober-addons' ),
				),
			),
		) );

		// Empty Space.
		vc_map(array(
			'name' => esc_html__('Empty Space Advanced', 'sober-addons' ),
			'description' => esc_html__('Empty spacing with resposive options', 'sober-addons' ),
			'base' => 'sober_empty_space',
			'icon' => $this->get_icon('empty.png'),
			'category' => esc_html__('Sober', 'sober-addons' ),
			'params' => array(
				array(
					'heading' => esc_html__('Height', 'sober-addons' ),
					'admin_label' => true,
					'type' => 'textfield',
					'param_name' => 'height',
					'value' => '32px',
				),
				array(
					'heading' => esc_html__('Extra class name', 'sober-addons' ),
					'description' => esc_html__('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name' => 'el_class',
					'type' => 'textfield',
					'value' => '',
				),
				array(
					'heading'          => esc_html__('Desktop', 'sober-addons' ),
					'type'             => 'textfield',
					'param_name'       => 'height_lg',
					'edit_field_class' => 'vc_col-xs-10',
					'group'            => esc_html__('Responsive Options', 'sober-addons' ),
				),
				array(
					'heading'          => esc_html__('Hide', 'sober-addons' ),
					'type'             => 'checkbox',
					'value'            => array( '' => 'yes' ),
					'param_name'       => 'hidden_lg',
					'edit_field_class' => 'vc_col-xs-2',
					'group'            => esc_html__('Responsive Options', 'sober-addons' ),
				),
				array(
					'heading'          => esc_html__('Tablet', 'sober-addons' ),
					'type'             => 'textfield',
					'param_name'       => 'height_md',
					'edit_field_class' => 'vc_col-xs-10',
					'group'            => esc_html__('Responsive Options', 'sober-addons' ),
				),
				array(
					'heading'          => esc_html__('Hide', 'sober-addons' ),
					'type'             => 'checkbox',
					'value'            => array( '' => 'yes' ),
					'param_name'       => 'hidden_md',
					'edit_field_class' => 'vc_col-xs-2',
					'group'            => esc_html__('Responsive Options', 'sober-addons' ),
				),
				array(
					'heading'          => esc_html__('Mobile', 'sober-addons' ),
					'type'             => 'textfield',
					'param_name'       => 'height_xs',
					'edit_field_class' => 'vc_col-xs-10',
					'group'            => esc_html__('Responsive Options', 'sober-addons' ),
				),
				array(
					'heading'          => esc_html__('Hide', 'sober-addons' ),
					'type'             => 'checkbox',
					'value'            => array( '' => 'yes' ),
					'param_name'       => 'hidden_xs',
					'edit_field_class' => 'vc_col-xs-2',
					'group'            => esc_html__('Responsive Options', 'sober-addons' ),
				),
			),
		));

		// Collection Carousel
		vc_map( array(
			'name'        => esc_html__( 'Collection Carousel', 'sober-addons' ),
			'description' => esc_html__( 'Image carousel', 'sober-addons' ),
			'base'        => 'sober_collection_carousel',
			'icon'        => $this->get_icon( 'collection-carousel.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'heading'     => esc_html__( 'Information', 'sober-addons' ),
					'type'        => 'param_group',
					'param_name'  => 'collections',
					'params'      => array(
						array(
							'heading'     => esc_html__( 'Image', 'sober-addons' ),
							'description' => esc_html__( 'Select image from media library', 'sober-addons' ),
							'type'        => 'attach_image',
							'param_name'  => 'image',
							'dependency'  => array(
								'element' => 'image_source',
								'value'   => 'media_library',
							),
						),
						array(
							'heading'     => esc_html__( 'Image size', 'sober-addons' ),
							'description' => esc_html__( 'Enter image size (Example: "thumbnail", "medium", "large", "full" or other sizes defined by theme). Alternatively enter size in pixels (Example: 200x100 (Width x Height)). Leave empty to use "thumbnail" size.', 'sober-addons' ),
							'type'        => 'textfield',
							'param_name'  => 'image_size',
							'value'       => 'full',
							'dependency'  => array(
								'element' => 'image_source',
								'value'   => 'media_library',
							),
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Title', 'sober-addons' ),
							'param_name'  => 'title',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'Button Text', 'sober-addons' ),
							'param_name'  => 'button_text',
						),
						array(
							'type'        => 'textfield',
							'heading'     => esc_html__( 'URL', 'sober-addons' ),
							'param_name'  => 'url',
						),
					),
				),
				array(
					'heading'     => esc_html__( 'Auto Play', 'sober-addons' ),
					'description' => esc_html__( 'Auto play speed in miliseconds. Enter "0" to disable auto play.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'autoplay',
					'value'       => 5000,
				),
				array(
					'heading'    => esc_html__( 'Loop', 'sober-addons' ),
					'type'       => 'checkbox',
					'param_name' => 'loop',
					'std'        => 'yes',
					'value'      => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
				),
				array(
					'heading'     => esc_html__( 'Free mode', 'sober-addons' ),
					'description' => esc_html__( 'Display images in their width and also make neighbour slides visible', 'sober-addons' ),
					'type'        => 'checkbox',
					'param_name'  => 'freemode',
					'value'       => array( esc_html__( 'Yes', 'sober-addons' ) => 'yes' ),
				),
				array(
					'heading'    => esc_html__( 'Navigation', 'sober-addons' ),
					'type'       => 'dropdown',
					'param_name' => 'navigation',
					'std'        => 'arrows',
					'value'      => array(
						esc_html__( 'None', 'sober-addons' )            => '',
						esc_html__( 'Arrows', 'sober-addons' )          => 'arrows',
						esc_html__( 'Dots', 'sober-addons' )            => 'dots',
						esc_html__( 'Arrows and Dots', 'sober-addons' ) => 'arrows_and_dots',
					),
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'type'        => 'textfield',
					'param_name'  => 'el_class',
				),
			),
		) );

		// Portfolio Grid
		vc_map( array(
			'name'        => esc_html__( 'Portfolio Grid', 'sober-addons' ),
			'description' => esc_html__( 'Display portfolio in grid', 'sober-addons' ),
			'base'        => 'sober_portfolio_grid',
			'icon'        => $this->get_icon( 'product-grid.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'description' => esc_html__( 'Number of portfolio you want to show', 'sober-addons' ),
					'heading'     => esc_html__( 'Number of portfolio', 'sober-addons' ),
					'param_name'  => 'per_page',
					'type'        => 'textfield',
					'value'       => 9,
				),
				array(
					'heading'     => esc_html__( 'Filter', 'sober-addons' ),
					'description' => esc_html__( 'Show Filter', 'sober-addons' ),
					'param_name'  => 'filter',
					'type'        => 'checkbox',
					'value'       => array(
						esc_html__( 'Yes', 'sober-addons' ) => 'yes',
					),
					'std' => '1'
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Portfolio Masonry
		vc_map( array(
			'name'        => esc_html__( 'Portfolio Masonry', 'sober-addons' ),
			'description' => esc_html__( 'Display portfolio in masonry', 'sober-addons' ),
			'base'        => 'sober_portfolio_masonry',
			'icon'        => $this->get_icon( 'banner-grid-5.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'description' => esc_html__( 'Number of portfolio you want to show', 'sober-addons' ),
					'heading'     => esc_html__( 'Number of portfolio', 'sober-addons' ),
					'param_name'  => 'per_page',
					'type'        => 'textfield',
					'value'       => 8,
				),
				array(
					'heading'     => esc_html__( 'Filter', 'sober-addons' ),
					'description' => esc_html__( 'Show Filter', 'sober-addons' ),
					'param_name'  => 'filter',
					'type'        => 'checkbox',
					'value'       => array(
						esc_html__( 'Yes', 'sober-addons' ) => 'yes',
					),
					'std' => '1'
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );

		// Portfolio Metro
		vc_map( array(
			'name'        => esc_html__( 'Portfolio Metro', 'sober-addons' ),
			'description' => esc_html__( 'Display portfolio in metro', 'sober-addons' ),
			'base'        => 'sober_portfolio_metro',
			'icon'        => $this->get_icon( 'banner-grid-4.png' ),
			'category'    => esc_html__( 'Sober', 'sober-addons' ),
			'params'      => array(
				array(
					'description' => esc_html__( 'Number of portfolio you want to show', 'sober-addons' ),
					'heading'     => esc_html__( 'Number of portfolio', 'sober-addons' ),
					'param_name'  => 'per_page',
					'type'        => 'textfield',
					'value'       => 8,
				),
				array(
					'heading'     => esc_html__( 'Filter', 'sober-addons' ),
					'description' => esc_html__( 'Show Filter', 'sober-addons' ),
					'param_name'  => 'filter',
					'type'        => 'checkbox',
					'value'       => array(
						esc_html__( 'Yes', 'sober-addons' ) => 'yes',
					),
					'std' => '1'
				),
				vc_map_add_css_animation(),
				array(
					'heading'     => esc_html__( 'Extra class name', 'sober-addons' ),
					'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'sober-addons' ),
					'param_name'  => 'el_class',
					'type'        => 'textfield',
					'value'       => '',
				),
			),
		) );
	}

	/**
	 * Get Icon URL
	 *
	 * @param string $file_name The icon file name with extension
	 *
	 * @return string Full URL of icon image
	 */
	protected function get_icon( $file_name ) {

		if ( file_exists( SOBER_ADDONS_DIR . 'assets/icons/' . $file_name ) ) {
			$url = SOBER_ADDONS_URL . 'assets/icons/' . $file_name;
		} else {
			$url = SOBER_ADDONS_URL . 'assets/icons/default.png';
		}

		return $url;
	}

	/**
	 * Get category for auto complete field
	 *
	 * @param string $taxonomy Taxnomy to get terms
	 *
	 * @return array
	 */
	public function get_terms( $taxonomy = 'product_cat' ) {
		// We don't want to query all terms again
		if ( isset( $this->terms[ $taxonomy ] ) ) {
			return $this->terms[ $taxonomy ];
		}

		$cats = get_terms( $taxonomy );
		if ( ! $cats || is_wp_error( $cats ) ) {
			return array();
		}

		$categories = array();
		foreach ( $cats as $cat ) {
			$categories[] = array(
				'label' => $cat->name,
				'value' => $cat->slug,
				'group' => 'category',
			);
		}

		// Store this in order to avoid double query this
		$this->terms[ $taxonomy ] = $categories;

		return $categories;
	}

	/**
	 * Add new fonts into Google font list
	 *
	 * @param array $fonts Array of objects
	 *
	 * @return array
	 */
	public function add_google_fonts( $fonts ) {
		$fonts[] = (object) array(
			'font_family' => 'Amatic SC',
			'font_styles' => '400,700',
			'font_types'  => '400 regular:400:normal,700 regular:700:normal',
		);

		$fonts[] = (object) array(
			'font_family' => 'Montez',
			'font_styles' => '400',
			'font_types'  => '400 regular:400:normal',
		);

		usort( $fonts, array( $this, 'sort_fonts' ) );

		return $fonts;
	}

	/**
	 * Sort fonts base on name
	 *
	 * @param object $a
	 * @param object $b
	 *
	 * @return int
	 */
	private function sort_fonts( $a, $b ) {
		return strcmp( $a->font_family, $b->font_family );
	}
}

