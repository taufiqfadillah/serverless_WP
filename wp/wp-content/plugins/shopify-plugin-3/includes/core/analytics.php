<?php

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Track plugin activation event
 * 
 * @param array $activation_data Custom data to include in the analytics event
 */
function track_plugin_activation( $activation_data = array() ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - track_plugin_activation called' );
    }
    
    // Get shop info to extract shop ID
    $shop_info = get_option( 'shopify_shop_info' );
    $shop_id = null;
    
    if ( $shop_info && isset( $shop_info['id'] ) ) {
        // Extract the numeric ID from the Shopify ID (format: gid://shopify/Shop/123456789)
        $shop_id = (int) basename( $shop_info['id'] );
    }
    
    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => $shop_id,
        'my_shopify_url' => get_option( 'shopify_store_url', null ),
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $activation_data );
    
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - Activation event data: ' . wp_json_encode( $event_data ) );
    }
    
    track_analytics_event( 'plugin_activated', $event_data );
}

/**
 * Track plugin deactivation event
 * 
 * @param array $deactivation_data Custom data to include in the analytics event
 */
function track_plugin_deactivation( $deactivation_data = array() ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - track_plugin_deactivation called' );
    }
    
    // Get shop info to extract shop ID
    $shop_info = get_option( 'shopify_shop_info' );
    $shop_id = null;
    
    if ( $shop_info && isset( $shop_info['id'] ) ) {
        // Extract the numeric ID from the Shopify ID (format: gid://shopify/Shop/123456789)
        $shop_id = (int) basename( $shop_info['id'] );
    }
    
    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => $shop_id,
        'my_shopify_url' => get_option( 'shopify_store_url', null ),
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $deactivation_data );
    
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - Deactivation event data: ' . wp_json_encode( $event_data ) );
    }
    
    track_analytics_event( 'plugin_deactivated', $event_data );
}

/**
 * Track plugin installation event
 * 
 * @param array $installation_data Custom data to include in the analytics event
 */
function track_plugin_installation( $installation_data = array() ) {
    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => null,
        'my_shopify_url' => null,
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $installation_data );
    
    track_analytics_event( 'plugin_installed', $event_data );
}

/**
 * Track plugin uninstallation event
 * 
 * @param array $uninstallation_data Custom data to include in the analytics event
 */
function track_plugin_uninstallation( $uninstallation_data = array() ) {
    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => null,
        'my_shopify_url' => null,
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $uninstallation_data );
    
    track_analytics_event( 'plugin_uninstalled', $event_data );
}

/**
 * Track successful Shopify store connection
 * 
 * @param array $connection_data Custom data to include in the analytics event
 * @param array $shop_info Optional shop info to use instead of getting from database
 */
function track_shopify_connection_success( $connection_data = array(), $shop_info = null ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - track_shopify_connection_success called' );
    }
    
    // Get shop info to extract shop ID - use passed parameter or get from database
    if ( $shop_info === null ) {
        $shop_info = get_option( 'shopify_shop_info' );
    }
    
    $shop_id = null;
    
    if ( $shop_info && isset( $shop_info['id'] ) ) {
        // Extract the numeric ID from the Shopify ID (format: gid://shopify/Shop/123456789)
        $shop_id = (int) basename( $shop_info['id'] );
    }
    
    
    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => $shop_id,
        'my_shopify_url' => get_option( 'shopify_store_url', null ),
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $connection_data );

    error_log( 'Shopify WP Connect Analytics - Event data: ' . wp_json_encode( $event_data ) );
    
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - Event data: ' . wp_json_encode( $event_data ) );
    }
    
    track_analytics_event( 'shopify_connect_succeeded', $event_data );
}

/**
 * Track failed Shopify store connection
 * 
 * @param array $connection_data Custom data to include in the analytics event
 * @param array $shop_info Optional shop info to use instead of getting from database
 */
function track_shopify_connection_error( $connection_data = array(), $shop_info = null ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - track_shopify_connection_error called' );
        error_log( 'Shopify WP Connect Analytics - Connection error data: ' . wp_json_encode( $connection_data ) );
    }
    
    // Get shop info to extract shop ID - use passed parameter or get from database
    if ( $shop_info === null ) {
        $shop_info = get_option( 'shopify_shop_info' );
    }
    
    $shop_id = null;
    
    if ( $shop_info && isset( $shop_info['id'] ) ) {
        // Extract the numeric ID from the Shopify ID (format: gid://shopify/Shop/123456789)
        $shop_id = (int) basename( $shop_info['id'] );
    }

    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => $shop_id,
        'my_shopify_url' => get_option( 'shopify_store_url', null ),
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $connection_data );
    
    
    track_analytics_event( 'shopify_connect_failed', $event_data );
}

/**
 * Track successful Shopify store disconnection
 * 
 * @param array $disconnection_data Custom data to include in the analytics event
 * @param int $shop_id Shop ID to include in the event (must be passed since it won't be available after disconnect)
 * @param string $my_shopify_url Shopify URL to include in the event (must be passed since it won't be available after disconnect)
 */
function track_shopify_disconnect_success( $disconnection_data = array(), $shop_id = null, $my_shopify_url = null ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - track_shopify_disconnect_success called' );
    }
    
    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => $shop_id,
        'my_shopify_url' => $my_shopify_url,
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $disconnection_data );
    
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - Disconnect success event data: ' . wp_json_encode( $event_data ) );
    }
    
    track_analytics_event( 'shopify_disconnect_succeeded', $event_data );
}

/**
 * Track failed Shopify store disconnection
 * 
 * @param array $disconnection_data Custom data to include in the analytics event
 * @param int $shop_id Shop ID to include in the event (must be passed since it might not be available after failed disconnect)
 * @param string $my_shopify_url Shopify URL to include in the event (must be passed since it might not be available after failed disconnect)
 */
function track_shopify_disconnect_error( $disconnection_data = array(), $shop_id = null, $my_shopify_url = null ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - track_shopify_disconnect_error called' );
        error_log( 'Shopify WP Connect Analytics - Disconnect error data: ' . wp_json_encode( $disconnection_data ) );
    }
    
    $default_data = array(
        'platform' => 'WordPress',
        'event_timestamp' => (int) ( microtime( true ) * 1000 ), // Milliseconds timestamp
        'shop_id' => $shop_id,
        'my_shopify_url' => $my_shopify_url,
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
    );
    
    $event_data = array_merge( $default_data, $disconnection_data );
    
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify WP Connect Analytics - Disconnect error event data: ' . wp_json_encode( $event_data ) );
    }
    
    track_analytics_event( 'shopify_disconnect_failure', $event_data );
}