/**
 * Shopify Cart Page JavaScript
 * 
 * Handles initialization and styling of the cart page component
 */

function hideDismissButton(cartElement) {
    // Target the specific close dialog button in shadow DOM
    if (cartElement.shadowRoot) {
        // Look for the button inside dialog structure: dialog > div > div > button
        const dialogButton = cartElement.shadowRoot.querySelector('dialog .closeButton button');
        if (dialogButton) {
            dialogButton.style.display = 'none';
            dialogButton.style.visibility = 'hidden';
        }
        
        // Also try broader selector as fallback
        const fallbackButton = cartElement.shadowRoot.querySelector('.closeButton button');
        if (fallbackButton) {
            fallbackButton.style.display = 'none';
            fallbackButton.style.visibility = 'hidden';
        }
    }
    
    // Also check main DOM as fallback
    const mainButton = cartElement.querySelector('.closeButton button');
    if (mainButton) {
        mainButton.style.display = 'none';
        mainButton.style.visibility = 'hidden';
    }
}

function initCartPage() {
    // Check if web components are loaded
    if (window.customElements && window.customElements.get('shopify-cart')) {
        // Get the cart display element
        const cartPageDisplay = document.getElementById('cart-page-display');
        if (cartPageDisplay) {
            // Show the cart to initialize it (CSS keeps it hidden initially)
            cartPageDisplay.show();
            
            // Immediately try to hide the dismiss button, then show cart
            setTimeout(function() {
                hideDismissButton(cartPageDisplay);
                // Add ready class to show the cart with transition
                cartPageDisplay.classList.add('ready');
            }, 50);
        }
    } else {
        // Wait for web components to load
        if (window.customElements) {
            window.customElements.whenDefined('shopify-cart').then(function() {
                initCartPage(); // Retry initialization
            });
        } else {
            // Fallback: keep checking every 500ms
            setTimeout(initCartPage, 500);
        }
    }
}

// Start initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCartPage);
} else {
    initCartPage();
}