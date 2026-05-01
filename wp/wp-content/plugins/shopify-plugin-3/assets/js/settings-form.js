/**
 * Settings Form JavaScript
 *
 * Handles tab functionality, form validation, and modal interactions for the settings page
 */

// Ensure ajaxurl is defined
if (typeof ajaxurl === 'undefined') {
    var ajaxurl = window.ajaxurl || '/wp-admin/admin-ajax.php';
}

// Private function to track pageview events
function _trackPageview(pageVariant) {
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'shopify_track_pageview',
            page_url: window.location.href,
            page_name: 'settings',
            page_variant: pageVariant,
            nonce: window.shopifyPageviewNonce || ''
        })
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.shopify-tab-button');
    const tabContents = document.querySelectorAll('.shopify-tab-content');
    const generalContainer = document.querySelector('#general-tab .app-settings-form-container__content');
    const artContainer = document.querySelector('.art-container');

    // Function to switch to a specific tab
    function switchToTab(targetTab) {
        // Remove active class from all buttons and contents
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        // Add active class to target button and corresponding content
        const targetButton = document.querySelector(`[data-tab="${targetTab}"]`);
        const targetContent = document.getElementById(targetTab + '-tab');

        if (targetButton) {
            targetButton.classList.add('active');
        }
        if (targetContent) {
            targetContent.classList.add('active');
        }

        // Toggle blue-bg class based on active tab
        if (targetTab === 'general') {
            if (generalContainer) {
                generalContainer.classList.add('blue-bg');
            }
            if (artContainer) {
                artContainer.style.display = 'block';
            }
        } else {
            if (generalContainer) {
                generalContainer.classList.remove('blue-bg');
            }
            if (artContainer) {
                artContainer.style.display = 'none';
            }
        }
    }

    // Function to update URL hash
    function updateUrlHash(tab) {
        const currentUrl = new URL(window.location);
        currentUrl.hash = tab;
        window.history.replaceState(null, null, currentUrl.toString());
    }

    // Function to get tab from URL hash
    function getTabFromHash() {
        const hash = window.location.hash.substring(1); // Remove the # symbol
        const validTabs = ['general', 'advanced'];
        return validTabs.includes(hash) ? hash : 'general';
    }

    // Handle tab button clicks
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');

            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            const targetContent = document.getElementById(targetTab + '-tab');
            if (targetContent) {
                targetContent.classList.add('active');
            }

            // Track pageview for advanced tab
            if (targetTab === 'advanced') {
                _trackPageview('advanced');
            }

			// Hide error and success messages when switching tabs
			const errorMessage = document.querySelector('.app-settings-form-container__credentials-error');
			const successMessage = document.querySelector('.app-settings-form-container__success-message');

			if (errorMessage) {
				errorMessage.style.display = 'none';

				// Only clear input if there's an error message (connection failed)
				const accessTokenInput = document.getElementById('shopify_for_wordpress_access_code');
				if (accessTokenInput && !accessTokenInput.hasAttribute('readonly')) {
					accessTokenInput.value = '';
					// Also clear the saved value by making an AJAX call to clear the option
					fetch(ajaxurl, {
						method: 'POST',
						headers: {'Content-Type': 'application/x-www-form-urlencoded'},
						body: new URLSearchParams({
							action: 'shopify_clear_access_code',
							nonce: shopifySettings.clearAccessCodeNonce
						})
					});
				}
			}

			// Hide success message when switching tabs
			if (successMessage) {
				successMessage.style.display = 'none';
			}

            // Toggle blue-bg class based on active tab
            if (targetTab === 'general') {
                _trackPageview('general');
                if (generalContainer) {
                    generalContainer.classList.add('blue-bg');
                }
                if (artContainer) {
                    artContainer.style.display = 'block';
                }
            } else {
                if (generalContainer) {
                    generalContainer.classList.remove('blue-bg');
                }
                if (artContainer) {
                    artContainer.style.display = 'none';
                }
            }
            switchToTab(targetTab);
            updateUrlHash(targetTab);
        });
    });

    // Handle browser back/forward navigation
    window.addEventListener('hashchange', function() {
        const targetTab = getTabFromHash();
        switchToTab(targetTab);
    });

    // Initialize tab based on URL hash on page load
    const initialTab = getTabFromHash();
    switchToTab(initialTab);

    // Form validation for access code
    const accessCodeField = document.getElementById('shopify_for_wordpress_access_code');
    const submitAccessCodeButton = document.getElementById('submit-access-code');

    if (accessCodeField && submitAccessCodeButton) {
        // Capture initial value
        const initialAccessCodeValue = accessCodeField.value.trim();
        const isReadOnly = accessCodeField.hasAttribute('readonly');

        function validateAccessCodeForm() {
            const currentValue = accessCodeField.value.trim();
            const hasChanged = currentValue !== initialAccessCodeValue;

            if (isReadOnly) {
                // If field is read-only (store is connected), button is hidden
                // No validation needed
            } else {
                // Enable button if:
                // 1. There's a current value (user has entered something)
                // 2. OR the value has changed from initial state
                if (currentValue.length > 0 || hasChanged) {
                    submitAccessCodeButton.disabled = false;
                    submitAccessCodeButton.style.opacity = '1';
                    submitAccessCodeButton.style.cursor = 'pointer';

                    submitAccessCodeButton.value = window.shopifyConnectText || 'Connect';
                } else {
                    // Disable button only if there's no current value and no initial value
                    submitAccessCodeButton.disabled = true;
                    submitAccessCodeButton.style.opacity = '0.5';
                    submitAccessCodeButton.style.cursor = 'not-allowed';
                }
            }
        }

        // Only add input listener if field is not read-only
        if (!isReadOnly) {
            accessCodeField.addEventListener('input', validateAccessCodeForm);
        }

        // Initial validation
        validateAccessCodeForm();
    }

    // Disconnect store functionality
    const disconnectButton = document.getElementById('disconnect-store');
    const disconnectModal = document.getElementById('disconnect-modal');
    const disconnectModalClose = document.getElementById('disconnect-modal-close');
    const disconnectModalCancel = document.getElementById('disconnect-modal-cancel');
    const disconnectModalConfirm = document.getElementById('disconnect-modal-confirm');
    const disconnectModalBackdrop = disconnectModal ? disconnectModal.querySelector('.shopify-disconnect-modal__backdrop') : null;

    if (disconnectButton && disconnectModal) {
        // Show modal when disconnect button is clicked
        disconnectButton.addEventListener('click', function() {
            disconnectModal.style.display = 'flex';
            disconnectModal.classList.add('show');
            disconnectModal.classList.remove('hide');
        });

        // Function to hide modal
        function hideModal() {
            disconnectModal.classList.add('hide');
            disconnectModal.classList.remove('show');
            setTimeout(() => {
                disconnectModal.style.display = 'none';
            }, 200);
        }

        // Hide modal when close button is clicked
        if (disconnectModalClose) {
            disconnectModalClose.addEventListener('click', hideModal);
        }

        // Hide modal when cancel button is clicked
        if (disconnectModalCancel) {
            disconnectModalCancel.addEventListener('click', hideModal);
        }

        // Hide modal when backdrop is clicked
        if (disconnectModalBackdrop) {
            disconnectModalBackdrop.addEventListener('click', hideModal);
        }

        // Handle disconnect confirmation
        if (disconnectModalConfirm) {
            disconnectModalConfirm.addEventListener('click', function() {
                // Clear the access code field
                const accessCodeField = document.getElementById('shopify_for_wordpress_access_code');
                if (accessCodeField) {
                    accessCodeField.value = '';
                    accessCodeField.removeAttribute('readonly');
                }

                // Create a hidden form to submit the disconnect request
                const disconnectForm = document.createElement('form');
                disconnectForm.method = 'POST';
                disconnectForm.action = window.location.href;
                disconnectForm.style.display = 'none';

                // Add nonce field
                const nonceField = document.createElement('input');
                nonceField.type = 'hidden';
                nonceField.name = 'shopify_access_code_nonce';
                nonceField.value = window.shopifyAccessCodeNonce || '';
                disconnectForm.appendChild(nonceField);

                // Add submit field
                const submitField = document.createElement('input');
                submitField.type = 'hidden';
                submitField.name = 'submit_access_code';
                submitField.value = '1';
                disconnectForm.appendChild(submitField);

                // Add empty access code field
                const accessCodeFieldHidden = document.createElement('input');
                accessCodeFieldHidden.type = 'hidden';
                accessCodeFieldHidden.name = 'shopify_for_wordpress_access_code';
                accessCodeFieldHidden.value = '';
                disconnectForm.appendChild(accessCodeFieldHidden);

                // Add to page and submit
                document.body.appendChild(disconnectForm);
                disconnectForm.submit();
            });
        }
    }

    // Quick View Example Modal functionality
    const quickViewExampleButton = document.querySelector('.quick-view-example-btn');
    const quickViewExampleModal = document.getElementById('quick-view-example-modal');
    const quickViewExampleModalClose = document.getElementById('quick-view-example-modal-close');
    const quickViewExampleModalDismiss = null; // No dismiss button in image modal
    const quickViewExampleModalBackdrop = quickViewExampleModal ? quickViewExampleModal.querySelector('.quick-view-image-modal__backdrop') : null;

    if (quickViewExampleButton && quickViewExampleModal) {
        // Show modal when example button is clicked
        quickViewExampleButton.addEventListener('click', function() {
            quickViewExampleModal.style.display = 'flex';
            quickViewExampleModal.classList.add('show');
            quickViewExampleModal.classList.remove('hide');
        });

        // Function to hide quick view example modal
        function hideQuickViewExampleModal() {
            quickViewExampleModal.classList.add('hide');
            quickViewExampleModal.classList.remove('show');
            setTimeout(() => {
                quickViewExampleModal.style.display = 'none';
            }, 200);
        }

        // Hide modal when close button is clicked
        if (quickViewExampleModalClose) {
            quickViewExampleModalClose.addEventListener('click', hideQuickViewExampleModal);
        }

        // Hide modal when dismiss button is clicked
        if (quickViewExampleModalDismiss) {
            quickViewExampleModalDismiss.addEventListener('click', hideQuickViewExampleModal);
        }

        // Hide modal when backdrop is clicked
        if (quickViewExampleModalBackdrop) {
            quickViewExampleModalBackdrop.addEventListener('click', hideQuickViewExampleModal);
        }
    }

    // PDP Example Modal functionality
    const pdpExampleButton = document.querySelector('.pdp-example-btn');
    const pdpExampleModal = document.getElementById('pdp-example-modal');
    const pdpExampleModalClose = document.getElementById('pdp-example-modal-close');
    const pdpExampleModalDismiss = null; // No dismiss button in image modal
    const pdpExampleModalBackdrop = pdpExampleModal ? pdpExampleModal.querySelector('.quick-view-image-modal__backdrop') : null;

    if (pdpExampleButton && pdpExampleModal) {
        // Show modal when example button is clicked
        pdpExampleButton.addEventListener('click', function() {
            pdpExampleModal.style.display = 'flex';
            pdpExampleModal.classList.add('show');
            pdpExampleModal.classList.remove('hide');
        });

        // Function to hide PDP example modal
        function hidePdpExampleModal() {
            pdpExampleModal.classList.add('hide');
            pdpExampleModal.classList.remove('show');
            setTimeout(() => {
                pdpExampleModal.style.display = 'none';
            }, 200);
        }

        // Hide modal when close button is clicked
        if (pdpExampleModalClose) {
            pdpExampleModalClose.addEventListener('click', hidePdpExampleModal);
        }

        // Hide modal when dismiss button is clicked
        if (pdpExampleModalDismiss) {
            pdpExampleModalDismiss.addEventListener('click', hidePdpExampleModal);
        }

        // Hide modal when backdrop is clicked
        if (pdpExampleModalBackdrop) {
            pdpExampleModalBackdrop.addEventListener('click', hidePdpExampleModal);
        }
    }

    // Handle ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Close disconnect modal
            if (disconnectModal && disconnectModal.style.display === 'flex') {
                disconnectModal.classList.add('hide');
                disconnectModal.classList.remove('show');
                setTimeout(() => {
                    disconnectModal.style.display = 'none';
                }, 200);
            }
            
            // Close quick view example modal
            if (quickViewExampleModal && quickViewExampleModal.style.display === 'flex') {
                quickViewExampleModal.classList.add('hide');
                quickViewExampleModal.classList.remove('show');
                setTimeout(() => {
                    quickViewExampleModal.style.display = 'none';
                }, 200);
            }
            
            // Close PDP example modal
            if (pdpExampleModal && pdpExampleModal.style.display === 'flex') {
                pdpExampleModal.classList.add('hide');
                pdpExampleModal.classList.remove('show');
                setTimeout(() => {
                    pdpExampleModal.style.display = 'none';
                }, 200);
            }
        }
    });

    // Advanced tab Save button state management
    const advancedSaveButton = document.getElementById('submit-advanced-settings');
    if (advancedSaveButton) {
        // Store initial form values
        const advancedForm = advancedSaveButton.closest('form');
        let initialFormData = {};
        
        if (advancedForm) {
            // Get initial values of all form elements
            const formElements = advancedForm.querySelectorAll('input[type="checkbox"], input[type="radio"], select');
            formElements.forEach(element => {
                if (element.type === 'checkbox' || element.type === 'radio') {
                    initialFormData[element.name] = element.checked;
                } else {
                    initialFormData[element.name] = element.value;
                }
            });

            // Function to check if form has changed
            function checkFormChanges() {
                let hasChanged = false;
                
                formElements.forEach(element => {
                    let currentValue;
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        currentValue = element.checked;
                    } else {
                        currentValue = element.value;
                    }
                    
                    if (initialFormData[element.name] !== currentValue) {
                        hasChanged = true;
                    }
                });

                // Enable/disable button based on changes
                advancedSaveButton.disabled = !hasChanged;
            }

            // Add event listeners to all form elements
            formElements.forEach(element => {
                element.addEventListener('change', checkFormChanges);
                if (element.type !== 'checkbox' && element.type !== 'radio') {
                    element.addEventListener('input', checkFormChanges);
                }
            });

            // Set initial disabled state
            advancedSaveButton.disabled = true;
        }
    }

});