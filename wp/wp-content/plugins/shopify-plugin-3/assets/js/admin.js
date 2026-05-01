/**
 * Shopify Admin JavaScript
 * 
 * Main admin functionality for the Shopify WordPress plugin
 */

jQuery(document).ready(function($) {
    'use strict';
    
    /**
     * Initialize admin functionality
     */
    function init() {
        initializeTooltips();
        initializeModals();
        initializeFormHandlers();
        console.log('Shopify admin scripts initialized');
    }
    
    /**
     * Initialize tooltips
     */
    function initializeTooltips() {
        // Add tooltip functionality if needed
        $('.shopify-tooltip').hover(
            function() {
                $(this).find('.tooltip-content').show();
            },
            function() {
                $(this).find('.tooltip-content').hide();
            }
        );
    }
    
    /**
     * Initialize modal functionality
     */
    function initializeModals() {
        // Modal close handlers
        $('.shopify-modal .close, .shopify-modal .modal-background').on('click', function() {
            $(this).closest('.shopify-modal').hide();
        });
        
        // ESC key to close modals
        $(document).on('keyup', function(e) {
            if (e.keyCode === 27) { // ESC key
                $('.shopify-modal:visible').hide();
            }
        });
    }
    
    /**
     * Initialize form handlers
     */
    function initializeFormHandlers() {
        // Generic form validation
        $('.shopify-form').on('submit', function(e) {
            const form = $(this);
            const submitButton = form.find('input[type="submit"], button[type="submit"]');
            
            // Show loading state
            submitButton.prop('disabled', true).addClass('loading');
            
            // Remove loading state after a delay (form submission will reload/redirect)
            setTimeout(function() {
                submitButton.prop('disabled', false).removeClass('loading');
            }, 5000);
        });
    }
    
    /**
     * Utility function to show notices
     */
    function showNotice(message, type = 'info') {
        const noticeClass = 'notice notice-' + type + ' is-dismissible';
        const notice = $('<div class="' + noticeClass + '"><p>' + message + '</p></div>');
        
        $('.wrap h1').after(notice);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
    }
    
    /**
     * Utility function for AJAX requests
     */
    function ajaxRequest(action, data, callback) {
        const requestData = {
            action: action,
            nonce: shopifyAdmin.nonce,
            ...data
        };
        
        $.post(ajaxurl, requestData, function(response) {
            if (typeof callback === 'function') {
                callback(response);
            }
        }).fail(function() {
            showNotice('An error occurred. Please try again.', 'error');
        });
    }
    
    // Make functions available globally
    window.shopifyAdmin = {
        showNotice: showNotice,
        ajaxRequest: ajaxRequest
    };
    
    // Initialize when DOM is ready
    init();
});