<?php
/**
 * WPML compatibility
 *
 * @package Sober
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sober WPML Class
 */
class Sober_WPML {
	/**
	 * The single instance of the class
	 *
	 * @var Sober_WPML
	 */
	protected static $instance = null;

	/**
	 * Main instance
	 */
	public static function instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'wpml_pb_shortcode_encode', array( $this, 'shortcode_encode_urlencoded_json' ), 10, 3 );
		add_filter( 'wpml_pb_shortcode_decode', array( $this, 'shortcode_decode_urlencoded_json' ), 10, 3 );

		add_action( 'sober_search_form_fields', array( $this, 'add_search_form_field' ) );
	}

	/**
	 * Encode the param_groups type of js_composer
	 *
	 * @param string $encode_string Encoded string.
	 * @param string $encoding Encoding type.
	 * @param array  $original_data Original data.
	 * @return string
	 */
	public function shortcode_encode_urlencoded_json( $encode_string, $encoding, $original_data ) {
		if ( 'urlencoded_json' === $encoding ) {
			$output = array();

			foreach ( $original_data as $combined_key => $value ) {
				$parts = explode( '_', $combined_key );
				$i     = array_pop( $parts );
				$key   = implode( '_', $parts );

				$output[ $i ][ $key ] = $value;
			}

			$encode_string = urlencode( json_encode( $output ) );
		}

		return $encode_string;
	}

	/**
	 * Decode urleconded string of param_groups type of js_composer
	 *
	 * @param string $decode_data Encoded data.
	 * @param string $encoding Encoding type.
	 * @param string $original_string Original string.
	 * @return string
	 */
	public function shortcode_decode_urlencoded_json( $decode_data, $encoding, $original_string ) {
		if ( 'urlencoded_json' === $encoding ) {
			$rows        = json_decode( urldecode( $original_string ), true );
			$decode_data = array();
			$atts        = array( 'label', 'value', 'image', 'title', 'button_text', 'url' );

			foreach ( (array) $rows as $i => $row ) {
				foreach ( $row as $key => $value ) {
					if ( in_array( $key, $atts ) ) {
						$decode_data[ $key . '_' . $i ] = array(
							'value'     => $value,
							'translate' => true,
						);
					} else {
						$decode_data[ $key . '_' . $i ] = array(
							'value'     => $value,
							'translate' => false,
						);
					}
				}
			}
		}

		return $decode_data;
	}

	/**
	 * Add the language field to the search form
	 */
	public function add_search_form_field() {
		do_action( 'wpml_add_language_form_field' );
	}
}

Sober_WPML::instance();
