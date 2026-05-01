<?php
/**
 * Check if a Shopify collection exists and return its SEO data.
 *
 * @param string $collection_handle The handle of the collection to check.
 * @return array|WP_Error Array containing collection data if found, WP_Error if not found or error occurs.
 */

// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
function shopify_wp_connect_check_collection_exists($collection_handle) {
    $store_url = get_option('shopify_store_url');
    $api_key = get_option('shopify_api_key');

    if (empty($store_url) || empty($api_key)) {
        return new WP_Error('missing_credentials', 'Shopify store URL or API key is missing');
    }

    require_once SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php';

    // Initialize GraphQL client
    Shopify_Wp_GraphQL::init($store_url, $api_key);

    // GraphQL query for collection data
    $query = '
    query GetCollectionSEO($handle: String!) {
        collection(handle: $handle) {
            id
            title
            description
            seo {
                title
                description
            }
        }
    }';

    // Execute the query
    $result = Shopify_Wp_GraphQL::execute_query($query, array('handle' => $collection_handle));

    if (is_wp_error($result)) {
        return $result;
    }

    if (!isset($result['data']['collection']) || empty($result['data']['collection']['title'])) {
        return new WP_Error('collection_not_found', 'Collection not found');
    }

    return $result['data']['collection'];
}

/**
 * Check if a Shopify product exists and return its data.
 *
 * @param string $product_handle The handle of the product to check.
 * @return array|WP_Error Array containing product data if found, WP_Error if not found or error occurs.
 */
function shopify_wp_connect_check_product_exists($product_handle) {
    $store_url = get_option('shopify_store_url');
    $api_key = get_option('shopify_api_key');

    if (empty($store_url) || empty($api_key)) {
        return new WP_Error('missing_credentials', 'Shopify store URL or API key is missing');
    }

    require_once SHOPIFY_PLUGIN_DIR . 'includes/api/shopify-graphql.php';

    // Initialize GraphQL client
    Shopify_Wp_GraphQL::init($store_url, $api_key);

    // GraphQL query for product data
    $query = '
    query GetProduct($handle: String!) {
        product(handle: $handle) {
            id
            title
            description
            seo {
                title
                description
            }
        }
    }';

    // Execute the query
    $result = Shopify_Wp_GraphQL::execute_query($query, array('handle' => $product_handle));

    if (is_wp_error($result)) {
        return $result;
    }

    if (!isset($result['data']['product']) || empty($result['data']['product']['title'])) {
        return new WP_Error('product_not_found', 'Product not found');
    }

    return $result['data']['product'];
}



/**
 * Create analytics metadata for tracking events.
 *
 * Generates standardized metadata including client message ID and timestamps
 * for analytics events sent to the Shopify analytics service.
 *
 * @return array Analytics metadata with client_message_id, event_created_at_ms, and event_sent_at_ms.
 */
function create_analytics_metadata() {
    return array(
        'client_message_id' => wp_generate_uuid4(),
        'event_created_at_ms' => round( microtime( true ) * 1000 ),
        'event_sent_at_ms' => round( microtime( true ) * 1000 ),
    );
}

/**
 * Log analytics attempt for debugging purposes.
 *
 * Logs the event type, identifier, and full payload to the WordPress error log
 * to help with debugging analytics tracking issues.
 *
 * @param string $event_type The type of event being tracked (e.g., 'event', 'pageview').
 * @param string $event_identifier The identifier for the specific event.
 * @param array $payload The complete payload being sent to analytics service.
 */
function log_analytics_attempt( $event_type, $event_identifier, $payload ) {
    if ( function_exists( 'error_log' ) ) {
        error_log( 'Shopify Analytics - Attempting to send ' . $event_type . ': ' . $event_identifier );
        error_log( 'Shopify Analytics - Payload: ' . json_encode( $payload, JSON_UNESCAPED_SLASHES ) );
    }
}

/**
 * Send analytics request to Shopify analytics service and log response.
 *
 * Makes an HTTP POST request to the Shopify analytics endpoint with the provided
 * payload and logs the response for debugging purposes.
 *
 * @param array $payload The analytics payload to send to the service.
 * @return array|WP_Error HTTP response array or WP_Error on failure.
 */
function send_analytics_request( $payload ) {
    $analytics_endpoint = 'https://monorail-edge.shopifysvc.com/v1/produce';

    // Send HTTP request to analytics service (temporarily blocking for debugging)
    $response = wp_remote_post( $analytics_endpoint, array(
        'body'      => json_encode( $payload, JSON_UNESCAPED_SLASHES ),
        'headers'   => array( 'Content-Type' => 'application/json' ),
        'timeout'   => 30, // Increased timeout for debugging
        'blocking'  => true, // Temporarily blocking to capture response
    ) );

    // Log the response (now we can capture it since it's blocking)
    if ( function_exists( 'error_log' ) ) {
        if ( is_wp_error( $response ) ) {
            error_log( 'Shopify Analytics - HTTP Error: ' . $response->get_error_message() );
        } else {
            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body = wp_remote_retrieve_body( $response );
            $response_headers = wp_remote_retrieve_headers( $response );

            error_log( 'Shopify Analytics - Response Code: ' . $response_code );
            error_log( 'Shopify Analytics - Response Headers: ' . wp_json_encode( $response_headers ) );
            error_log( 'Shopify Analytics - Response Body: ' . $response_body );

            if ( $response_code !== 200 ) {
                error_log( 'Shopify Analytics - Non-200 response code indicates potential issue' );
            }
        }
    }

    return $response;
}

/**
 * Create nested payload structure for analytics events.
 *
 * @param string $schema_id The schema identifier for the analytics event.
 * @param array $payload_data The payload data to include in the analytics event.
 * @return array|WP_Error Nested payload structure with schema, payload, and metadata, or WP_Error on invalid input.
 */
function create_nested_payload( $schema_id, $payload_data ) {
    // Validate schema_id
    if ( empty( $schema_id ) || ! is_string( $schema_id ) ) {
        return new WP_Error( 'invalid_schema_id', 'Schema ID must be a non-empty string' );
    }

    // Validate payload_data
    if ( ! is_array( $payload_data ) ) {
        return new WP_Error( 'invalid_payload', 'Payload data must be an array' );
    }

    return array(
        'schema_id' => $schema_id,
        'payload' => $payload_data,
        'metadata' => create_analytics_metadata(),
    );
}

/**
 * Send analytics event with logging to analytics service.
 *
 * @param string $event_type The type of event being tracked (e.g., 'event', 'pageview').
 * @param string $event_identifier The identifier for the specific event.
 * @param array $nested_payload The complete payload structure to send.
 * @return array|WP_Error Response from the analytics service or WP_Error on failure.
 */
function send_analytics_event_with_logging( $event_type, $event_identifier, $nested_payload ) {
    // Validate event_type
    if ( empty( $event_type ) || ! is_string( $event_type ) ) {
        return new WP_Error( 'invalid_event_type', 'Event type must be a non-empty string' );
    }

    // Validate event_identifier
    if ( empty( $event_identifier ) || ! is_string( $event_identifier ) ) {
        return new WP_Error( 'invalid_event_identifier', 'Event identifier must be a non-empty string' );
    }

    // Validate nested_payload
    if ( ! is_array( $nested_payload ) ) {
        return new WP_Error( 'invalid_nested_payload', 'Nested payload must be an array' );
    }

    log_analytics_attempt( $event_type, $event_identifier, $nested_payload );
    return send_analytics_request( $nested_payload );
}

/**
 * Track analytics events - server side.
 *
 * @param string $event_name The name of the event being tracked.
 * @param array $event_data Additional data to include with the event.
 * @return array|WP_Error|null Response from the analytics service, WP_Error on failure, or null if analytics are disabled.
 */
function track_analytics_event( $event_name, $event_data ) {
	// If analytics are disabled, don't track the event
	if(!get_option('shopify_enable_analytics', false)) {
		return;
	}
    // Prepare payload data
    $payload_data = array_merge( array( 'event_name' => $event_name ), $event_data );

    // Create the nested payload structure
    $nested_payload = create_nested_payload( 'plugin_lifecycle_events/1.0', $payload_data );

    // Check if payload creation failed
    if ( is_wp_error( $nested_payload ) ) {
        return $nested_payload;
    }

    return send_analytics_event_with_logging( 'event', $event_name, $nested_payload );
}

/**
 * Get or create a session token for analytics tracking.
 *
 * Session tokens persist for the duration of a user's WordPress admin session
 * and are used to group multiple pageviews together.
 *
 * @return string Session token UUID.
 */
function get_analytics_session_token() {
    // Check if we already have a session token stored
    $session_token = get_transient( 'shopify_analytics_session_token' );

    if ( ! $session_token ) {
        // Generate new session token and store it for 30 minutes (aligned with Shopify's session duration)
        $session_token = wp_generate_uuid4();
        set_transient( 'shopify_analytics_session_token', $session_token, 30 * MINUTE_IN_SECONDS );
    }

    return $session_token;
}

/**
 * Get or create a pageview token for a specific page URL.
 *
 * Pageview tokens are unique per page URL within a session. The same URL
 * visited multiple times in a session will have the same pageview token.
 *
 * @param string $page_url The URL of the page being viewed.
 * @return string Pageview token UUID.
 */
function get_analytics_pageview_token( $page_url ) {
    // Create a unique key for this page URL within the session
    $transient_key = 'shopify_pageview_token_' . md5( $page_url );

    // Check if we already have a pageview token for this URL
    $pageview_token = get_transient( $transient_key );

    if ( ! $pageview_token ) {
        // Generate new pageview token and store it for 30 minutes (aligned with session duration)
        $pageview_token = wp_generate_uuid4();
        set_transient( $transient_key, $pageview_token, 30 * MINUTE_IN_SECONDS );
    }

    return $pageview_token;
}

/**
 * Track pageview events - server side.
 *
 * @param string $page_url The URL of the page being viewed.
 * @param string $page_name The name identifier for the page.
 * @param string|null $page_variant Optional variant of the page (e.g., 'advanced', 'developer').
 * @return array|WP_Error|null Response from the analytics service, WP_Error on failure, or null if analytics are disabled.
 */
function track_pageview_event( $page_url, $page_name, $page_variant = null ) {
	// If analytics are disabled, don't track the pageview
	if(!get_option('shopify_enable_analytics', false)) {
		return;
	}

    // Get shop info to extract shop ID
    $shop_info = get_option( 'shopify_shop_info' );
    $shop_id = null;

    if ( $shop_info && isset( $shop_info['id'] ) ) {
        // Extract the numeric ID from the Shopify ID (format: gid://shopify/Shop/123456789)
        $shop_id = (int) basename( $shop_info['id'] );
    }

    // Create pageview payload structure
    $payload_data = array(
        'pageview_token' => get_analytics_pageview_token( $page_url ), // Same token for same URL
        'session_token' => get_analytics_session_token(), // Persistent across pageviews
        'platform_unique_identifier' => 'admin_url',
        'platform_unique_identifier_value' => get_site_url(),
        'shop_id' => $shop_id,
        'my_shopify_url' => get_option( 'shopify_store_url', null ),
        'event_timestamp' => round( microtime( true ) * 1000 ),
        'platform' => 'WordPress',
        'page_url' => $page_url,
        'page_name' => $page_name,
    );

    // Add page_variant if provided
    if ( $page_variant !== null ) {
        $payload_data['page_variant'] = $page_variant;
    }

    // Create the nested payload structure for pageviews
    $nested_payload = create_nested_payload( 'plugin_pageviews/2.0', $payload_data );

    // Check if payload creation failed
    if ( is_wp_error( $nested_payload ) ) {
        return $nested_payload;
    }

    return send_analytics_event_with_logging( 'pageview', $page_name, $nested_payload );
}

/**
 * Performs a complete disconnect from Shopify
 *
 * @param bool $track_analytics Whether to track the disconnect event (default: true)
 * @return bool True on success, false on failure
 */
function shopify_wp_connect_perform_disconnect($track_analytics = true) {
    // Store shop data before deleting for analytics tracking
    $shop_info = get_option('shopify_shop_info');
    $shop_id = null;
    $my_shopify_url = get_option('shopify_store_url');

    if ($shop_info && isset($shop_info['id'])) {
        // Extract the numeric ID from the Shopify ID (format: gid://shopify/Shop/123456789)
        $shop_id = (int) basename($shop_info['id']);
    }

    try {
        delete_option('shopify_shop_info');
        delete_option('shopify_store_url');
        delete_option('shopify_api_key');
        delete_option('shopify_for_wordpress_access_code');

        // Remove fallback cart page if it exists
        $fallback_cart_page_id = get_option('shopify_fallback_cart_page_id');
        if ($fallback_cart_page_id) {
            wp_delete_post($fallback_cart_page_id, true); // true = force delete, bypass trash
            delete_option('shopify_fallback_cart_page_id');
        }

        // Track analytics if requested
        if ($track_analytics && function_exists('track_shopify_disconnect_success')) {
            track_shopify_disconnect_success(array(), $shop_id, $my_shopify_url);
        }

        return true;
    } catch (Exception $e) {
        error_log('Shopify Utils - Disconnect failed: ' . $e->getMessage());

        // Track failed disconnection
        if ($track_analytics && function_exists('track_shopify_disconnect_error')) {
            track_shopify_disconnect_error(array('error_message' => $e->getMessage()), $shop_id, $my_shopify_url);
        }

        return false;
    }
}

// ExampleHook into WordPress events
add_action( 'user_register', function( $user_id ) {
    track_analytics_event( 'user_signup', array( 'user_id' => $user_id ) );
});
