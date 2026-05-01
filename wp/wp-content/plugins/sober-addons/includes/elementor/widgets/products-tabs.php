<?php
namespace SoberAddons\Elementor\Widgets;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Products tabs widget
 */
class Products_Tabs extends Products_Grid {
	/**
	 * Retrieve the widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'sober-products-tabs';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Products Tabs', 'sober-addons' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'sober-elementor-widget eicon-product-tabs';
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
		return [ 'products tabs', 'products', 'woocommerce', 'tabs', 'sober' ];
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
			'limit',
			[
				'label' => __( 'Number Of Products', 'sober-addons' ),
				'type' => Controls_Manager::NUMBER,
				'min' => -1,
				'max' => 100,
				'step' => 1,
				'default' => 12,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'columns',
			[
				'label' => __( 'Columns', 'sober-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					3 => __( '3 Columns', 'sober-addons' ),
					4 => __( '4 Columns', 'sober-addons' ),
					5 => __( '5 Columns', 'sober-addons' ),
					6 => __( '6 Columns', 'sober-addons' ),
				],
				'default' => 4,
			]
		);

		$this->add_control(
			'orderby',
			[
				'label'   => __( 'Order By', 'sober-addons' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_product_orderby(),
				'default' => '',
			]
		);

		$this->add_control(
			'order',
			[
				'label'   => __( 'Order', 'sober-addons' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => [
					'ASC'  => __( 'Ascending', 'sober-addons' ),
					'DESC' => __( 'Descending', 'sober-addons' ),
				],
				'condition' => [
					'orderby!' => '',
				],
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

		$this->start_controls_section(
			'section_tabs',
			[ 'label' => __( 'Tabs', 'sober-addons' ) ]
		);

		$this->add_control(
			'tabs_effect',
			[
				'label' => __( 'Tabs Effect', 'sober-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'isotope' => __( 'Isotope', 'sober-addons' ),
					'ajax'    => __( 'Ajax Loads', 'sober-addons' ),
				],
				'default' => 'ajax',
			]
		);

		$this->add_control(
			'tabs',
			[
				'label' => __( 'Tabs', 'sober-addons' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'group'    => __( 'Groups', 'sober-addons' ),
					'category' => __( 'Categories', 'sober-addons' ),
					'tag'      => __( 'Tags', 'sober-addons' ),
				],
				'default' => 'group',
			]
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'category_slug',
			[
				'label'    => __( 'Category', 'sober-addons' ),
				'type'     => Controls_Manager::SELECT,
				'options'  => $this->get_product_categories(),
			]
		);

		$this->add_control(
			'category',
			[
				'label'       => __( 'Categories', 'sober-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ category_slug }}}',
				'condition'   => [
					'tabs' => 'category',
				],
				'prevent_empty' => false
			]
		);

		$this->add_control(
			'tag',
			[
				'label'       => __( 'Tags', 'sober-addons' ),
				'description' => __( 'Product tags, separate by commas', 'sober-addons' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'condition'   => [
					'tabs' => 'tag',
				],
			]
		);

		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'group_type',
			[
				'label'    => __( 'Group', 'sober-addons' ),
				'type'     => Controls_Manager::SELECT,
				'options'  => [
					'best_sellers'      => esc_html__( 'Best Sellers', 'sober-addons' ),
					'new_products'      => esc_html__( 'New Products', 'sober-addons' ),
					'sale_products'     => esc_html__( 'Sale Products', 'sober-addons' ),
					'featured_products' => esc_html__( 'Hot Products', 'sober-addons' ),
				],
			]
		);

		$this->add_control(
			'groups',
			[
				'label'       => __( 'Groups', 'sober-addons' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ group_type }}}',
				'condition'   => [
					'tabs' => 'group',
				],
				'default' => [
					[
						'group_type' => 'best_sellers',
					],
					[
						'group_type' => 'new_products',
					],
					[
						'group_type' => 'sale_products',
					],
				],
			]
		);

		$this->add_control(
			'show_all',
			[
				'label'     => __( 'Show All Button', 'sober-addons' ),
				'type'      => Controls_Manager::SWITCHER,
				'label_on'  => __( 'Show', 'sober-addons' ),
				'label_off' => __( 'Hide', 'sober-addons' ),
				'default'   => '',
				'conditions' => [
					'relation' => 'or',
					'terms'    => [
						[
							'name'  => 'tabs',
							'value' => 'category',
						],
						[
							'name'  => 'tabs',
							'value' => 'tag',
						],
						[
							'terms' => [
								[
									'name'  => 'tabs',
									'value' => 'group',
								],
								[
									'name'  => 'tabs_effect',
									'value' => 'ajax',
								],
							]
						]
					]
				]
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
			'per_page'    => $settings['limit'],
			'columns'     => $settings['columns'],
			'category'    => is_array( $settings['category'] ) ? implode( ',', wp_list_pluck( $settings['category'], 'category_slug' ) ) : $settings['category'],
			'tag'         => $settings['tag'],
			'groups'      => is_array( $settings['groups'] ) ? implode( ',', wp_list_pluck( $settings['groups'], 'group_type' ) ) : $settings['groups'],
			'load_more'   => $settings['load_more'],
			'orderby'     => $settings['orderby'],
			'order'       => $settings['order'],
			'filter'      => $settings['tabs'],
			'filter_type' => $settings['tabs_effect'],
			'show_all'    => ( 'yes' === $settings['show_all'] ),
		];

		if ( 'group' == $atts['filter'] ) {
			unset( $settings['category'] );
		}

		echo \Sober_Shortcodes::product_tabs( $atts );
	}
}
