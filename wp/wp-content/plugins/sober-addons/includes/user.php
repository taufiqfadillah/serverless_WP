<?php
/**
 * Add more data for user
 */

/**
 * Add more contact method for user
 *
 * @param array $methods
 *
 * @return array
 */
function sober_addons_user_contact_methods( $methods ) {
	$methods['facebook']  = esc_html__( 'Facebook', 'sober-addons' );
	$methods['twitter']   = esc_html__( 'Twitter', 'sober-addons' );
	$methods['pinterest'] = esc_html__( 'Pinterest', 'sober-addons' );
	$methods['instagram'] = esc_html__( 'Instagram', 'sober-addons' );

	return $methods;
}

add_filter( 'user_contactmethods', 'sober_addons_user_contact_methods' );
