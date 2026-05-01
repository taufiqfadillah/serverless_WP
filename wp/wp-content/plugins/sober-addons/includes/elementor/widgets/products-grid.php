<?php
namespace SoberAddons\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Products grid widget
 */
class Products_Grid extends Widget_Base {
	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'sober-products-grid';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Products Grid', 'sober-addons' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'sober-elementor-widget eicon-products';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return ['sober'];
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return [ 'products grid', 'products', 'woocommerce', 'sober' ];
	}

	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @access protected
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_products',
			[ 'label' => __( 'Products', 'sober-addons' ) ]
		);

		$this->add_control(
			'type',
			[
				'label' => __( 'Type', 'sober-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_product_types(),
				'default' => 'recent',
			]
		);

		$this->add_control(
			'category',
			[
				'label' => __( 'Category', 'sober-addons' ),
				'type' => Controls_Manager::SELECT2,
				'options' => $this->get_product_categories(),
				'default' => '',
				'multiple' => true,
			]
		);

		$this->add_control(
			'tag',
			[
				'label' => __( 'Tags', 'sober-addons' ),
				'description' => __( 'Product tags, separate by commas', 'sober-addons' ),
				'type' => Controls_Manager::TEXT,
				'default' => '',
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'sober-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => $this->get_product_orderby(),
				'default' => 'menu_order',
				'condition' => [
					'type' => ['', 'featured', 'sale']
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'sober-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'ASC'  => __( 'Ascending', 'sober-addons' ),
					'DESC' => __( 'Descending', 'sober-addons' ),
				],
				'default' => 'ASC',
				'condition' => [
					'type'     => ['', 'featured', 'sale'],
					'orderby!' => ['', 'rand'],
				],
			]
		);

		$this->add_control(
			'limit',
			[
				'label'     => __( 'Number Of Products', 'sober-addons' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => -1,
				'max'       => 100,
				'step'      => 1,
				'default'   => 12,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'columns',
			[
				'label' => __( 'Columns', 'sober-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					2 => __( '2 Columns', 'sober-addons' ),
					3 => __( '3 Columns', 'sober-addons' ),
					4 => __( '4 Columns', 'sober-addons' ),
					5 => __( '5 Columns', 'sober-addons' ),
					6 => __( '6 Columns', 'sober-addons' ),
				],
				'default' => 4,
			]
		);

		$this->add_control(
			'load_more',
			[
				'label'        => __( 'Load More Button', 'sober-addons' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'sober-addons' ),
				'label_off'    => __( 'No', 'sober-addons' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'separator'    => 'before',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$atts = [
			'type'      => $settings['type'],
			'per_page'  => $settings['limit'],
			'columns'   => $settings['columns'],
			'category'  => is_array( $settings['category'] ) ? implode( ',', $settings['category'] ): $settings['category'],
			'tag'       => $settings['tag'],
			'orderby'   => $settings['orderby'],
			'order'     => $settings['order'],
			'load_more' => $settings['load_more'],
		];

		echo \Sober_Shortcodes::product_grid( $atts );
	}

	/**
	 * Get product categories
	 *
	 * @param int $parent
	 * @param int $level
	 * @return array
	 */
	protected function get_product_categories( $parent = 0, $level = 0 ) {
		$output = [];
		$level = $parent ? $level : 0; // Level 0 if parent is 0.

		$cats = get_terms( [
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'orderby'    => 'title',
			'order'      => 'ASC',
			'parent'     => $parent,
		] );

		if ( ! $cats || is_wp_error( $cats ) ) {
			return $output;
		}

		foreach ( $cats as $cat ) {
			$output[ $cat->slug ] = str_repeat( '--', $level ) . $cat->name;

			$level++;

			$output += $this->get_product_categories( $cat->term_id, $level );

			$level--;
		}

		return $output;
	}

	/**
	 * Supported product types.
	 *
	 * @return array
	 */
	protected function get_product_types() {
		return [
			''             => __( 'Default', 'sober-addons' ),
			'recent'       => __( 'Recent Products', 'sober-addons' ),
			'featured'     => __( 'Featured Products', 'sober-addons' ),
			'sale'         => __( 'Sale Products', 'sober-addons' ),
			'best_sellers' => __( 'Best Selling Products', 'sober-addons' ),
			'top_rated'    => __( 'Top Rated Products', 'sober-addons' ),
		];
	}

	/**
	 * Supported product order by.
	 *
	 * @return array
	 */
	protected function get_product_orderby() {
		return [
			''           => __( 'Default', 'sober-addons' ),
			'menu_order' => __( 'Default Sorting (Menu Order)', 'sober-addons' ),
			'date'       => __( 'Date', 'sober-addons' ),
			'id'         => __( 'Product ID', 'sober-addons' ),
			'title'      => __( 'Product Title', 'sober-addons' ),
			'rand'       => __( 'Random', 'sober-addons' ),
			'price'      => __( 'Price', 'sober-addons' ),
			'popularity' => __( 'Popularity (Sales)', 'sober-addons' ),
			'rating'     => __( 'Rating', 'sober-addons' ),
		];
	}
}
