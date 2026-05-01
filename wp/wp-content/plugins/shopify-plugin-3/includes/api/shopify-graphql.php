<?php
/**
 * Shopify GraphQL API functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class Shopify_Wp_GraphQL {
    private static $store_url;
    private static $api_key;

    public static function init($store_url, $api_key) {
        self::$store_url = $store_url;
        self::$api_key = $api_key;
    }

    public static function execute_query($query, $variables = array()) {
        error_log('=== SHOPIFY GRAPHQL EXECUTE QUERY START ===');
        error_log('Shopify GraphQL Execute - Store URL: ' . self::$store_url);
        error_log('Shopify GraphQL Execute - API Key: ' . substr(self::$api_key, 0, 10) . '...');
        
        if (empty(self::$store_url) || empty(self::$api_key)) {
            error_log('Shopify GraphQL Execute - ERROR: Missing credentials');
            return new WP_Error('missing_credentials', 'Store URL and API key are required');
        }

        $endpoint = 'https://' . self::$store_url . '/api/2024-01/graphql.json';
        error_log('Shopify GraphQL Execute - Endpoint: ' . $endpoint);
        
        $headers = array(
            'Content-Type' => 'application/json',
            'X-Shopify-Storefront-Access-Token' => self::$api_key
        );

        $body = array(
            'query' => $query,
            'variables' => $variables
        );

        error_log('Shopify GraphQL Execute - Request headers: ' . print_r($headers, true));
        error_log('Shopify GraphQL Execute - Request body: ' . json_encode($body));

        $response = wp_remote_post($endpoint, array(
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error_code = $response->get_error_code();
            error_log('Shopify GraphQL Execute - wp_remote_post ERROR: ' . $error_message . ' (Code: ' . $error_code . ')');
            error_log('Shopify GraphQL Execute - Full WP_Error details: ' . print_r($response->get_error_messages(), true));
            error_log('Shopify GraphQL Execute - Request endpoint: ' . $endpoint);
            error_log('Shopify GraphQL Execute - Store URL: ' . self::$store_url);
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        error_log('Shopify GraphQL Execute - Response code: ' . $response_code);
        error_log('Shopify GraphQL Execute - Response body: ' . $response_body);
        
        // Check for HTTP errors before processing JSON
        if ($response_code >= 400) {
            $error_context = '';
            switch ($response_code) {
                case 401:
                    $error_context = 'Unauthorized - Invalid or expired storefront access token';
                    break;
                case 403:
                    $error_context = 'Forbidden - Storefront access token lacks required permissions';
                    break;
                case 404:
                    $error_context = 'Not Found - Store URL is incorrect or store does not exist';
                    break;
                case 429:
                    $error_context = 'Rate Limited - Too many API requests, please try again later';
                    break;
                case 500:
                case 502:
                case 503:
                case 504:
                    $error_context = 'Shopify API Server Error - Service temporarily unavailable';
                    break;
                default:
                    $error_context = "HTTP Error $response_code";
            }
            error_log('Shopify GraphQL Execute - HTTP Error: ' . $error_context);
            return new WP_Error('http_error', $error_context);
        }

        $data = json_decode($response_body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Shopify GraphQL Execute - JSON decode ERROR: ' . json_last_error_msg());
            return new WP_Error('json_error', 'Failed to parse GraphQL response');
        }

        if (isset($data['errors'])) {
            $error_message = isset($data['errors'][0]['message']) 
                ? $data['errors'][0]['message'] 
                : 'Unknown GraphQL error';
            error_log('Shopify GraphQL Execute - GraphQL ERROR: ' . $error_message);
            error_log('Shopify GraphQL Execute - All GraphQL errors: ' . print_r($data['errors'], true));
            
            // Check for specific error types and provide more context
            if (strpos($error_message, 'access denied') !== false || strpos($error_message, 'unauthorized') !== false) {
                error_log('Shopify GraphQL Execute - ACCESS DENIED: Check if storefront access token has correct permissions');
            } elseif (strpos($error_message, 'not found') !== false) {
                error_log('Shopify GraphQL Execute - NOT FOUND: Store URL may be incorrect or store may not exist');
            } elseif (strpos($error_message, 'rate limit') !== false || strpos($error_message, 'throttled') !== false) {
                error_log('Shopify GraphQL Execute - RATE LIMITED: Too many API requests, need to wait');
            }
            
            return new WP_Error('graphql_error', $error_message);
        }

        error_log('Shopify GraphQL Execute - SUCCESS: Query executed successfully');
        return $data;
    }

    /**
     * Validate store credentials by making a GraphQL request
     * 
     * @param string $store_url The Shopify store URL
     * @param string $api_key The Shopify Storefront API key
     * @return array|WP_Error Array with store info on success, WP_Error on failure
     */
    public static function validate_store_credentials($store_url, $api_key) {
        error_log('=== SHOPIFY GRAPHQL VALIDATION START ===');
        error_log('Shopify GraphQL Validation - Store URL: ' . $store_url);
        error_log('Shopify GraphQL Validation - API Key: ' . substr($api_key, 0, 10) . '...');
        
        self::init($store_url, $api_key);

        $query = '
        query {
            shop {
                id
                name
                primaryDomain {
                    url
                    host
                }
            }
        }';

        error_log('Shopify GraphQL Validation - Executing query...');
        $result = self::execute_query($query);

        if (is_wp_error($result)) {
            error_log('Shopify GraphQL Validation - ERROR: ' . $result->get_error_message());
            error_log('Shopify GraphQL Validation - Error code: ' . $result->get_error_code());
            return $result;
        }

        error_log('Shopify GraphQL Validation - Query successful, response: ' . print_r($result, true));

        if (!isset($result['data']['shop'])) {
            error_log('Shopify GraphQL Validation - ERROR: Invalid response - no shop data found');
            return new WP_Error('invalid_response', 'Invalid response from Shopify API');
        }

        error_log('Shopify GraphQL Validation - SUCCESS: Shop info retrieved');
        error_log('Shopify GraphQL Validation - Shop name: ' . $result['data']['shop']['name']);
        error_log('Shopify GraphQL Validation - Shop domain: ' . $result['data']['shop']['primaryDomain']['host']);
        
        return $result['data'];
    }
} 