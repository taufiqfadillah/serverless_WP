/**
 * Dashboard Accordion JavaScript
 *
 * Handles accordion functionality and setup progress checkboxes
 */

(function() {
    // Ensure ajaxurl is defined for AJAX requests
    if (typeof ajaxurl === 'undefined') {
        var ajaxurl = window.ajaxurl || '/wp-admin/admin-ajax.php';
    }

    // Check if the function already exists to avoid conflicts
    if (window.toggleDashboardAccordion) {
        return;
    }

    window.toggleDashboardAccordion = function(event) {
        event.preventDefault();
        event.stopPropagation();

        const header = event.currentTarget;
        const content = header.nextElementSibling;
        const isActive = header.classList.contains("active");

        // Prevent rapid firing by checking if we're already processing
        if (header.dataset.processing === 'true') {
            return;
        }

        // Set processing flag
        header.dataset.processing = 'true';

        // Close all other dashboard accordions first
        document.querySelectorAll(".dashboard-accordion__header").forEach((h) => {
            if (h !== header) {
                h.classList.remove("active");
                const otherContent = h.nextElementSibling;
                if (otherContent) {
                    otherContent.style.maxHeight = "0";
                    otherContent.classList.remove("active");
                }
            }
        });

        // Toggle current accordion
        if (!isActive) {
            header.classList.add("active");
            content.classList.add("active");

            // Temporarily remove transition to get accurate scrollHeight
            content.style.transition = "none";
            const scrollHeight = content.scrollHeight;

            // Force a reflow
            content.offsetHeight;

            // Restore transition and set height
            content.style.transition = "max-height 0.3s ease";
            content.style.maxHeight = scrollHeight + "px";

        } else {
            header.classList.remove("active");
            content.style.maxHeight = "0";
            content.classList.remove("active");
        }

        // Clear processing flag after a short delay
        setTimeout(() => {
            header.dataset.processing = 'false';
        }, 100);
    };

    // Add event listeners when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", initializeAccordions);
    } else {
        initializeAccordions();
    }

    function initializeAccordions() {
        const accordionHeaders = document.querySelectorAll(".dashboard-accordion__header");
        accordionHeaders.forEach((header) => {
            // Check if this header already has our custom attribute
            if (header.dataset.accordionInitialized === 'true') {
                return;
            }

            // Mark as initialized
            header.dataset.accordionInitialized = 'true';

            // Add click listener to the entire header
            header.removeEventListener("click", window.toggleDashboardAccordion);
            header.addEventListener("click", window.toggleDashboardAccordion);

            // Prevent link clicks from triggering accordion toggle
            const links = header.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            });

            // Initialize accordions that are open by default
            const content = header.nextElementSibling;
            if (header.classList.contains("active") && content) {
                // Temporarily remove transition to get accurate scrollHeight
                content.style.transition = "none";
                const scrollHeight = content.scrollHeight;

                // Force a reflow
                content.offsetHeight;

                // Restore transition and set height
                content.style.transition = "max-height 0.3s ease";
                content.style.maxHeight = scrollHeight + "px";
            }
        });

        // Add checkbox change handlers for setup progress
        initializeSetupProgressCheckboxes();
    }

    // Initialize setup progress checkbox handlers
    function initializeSetupProgressCheckboxes() {
        const setupAccordion = document.querySelector('#shopify-setup-accordion');
        if (!setupAccordion) return;

        const checkboxes = setupAccordion.querySelectorAll('.dashboard-accordion__checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkboxId = this.id;
                const checked = this.checked;

                // Send AJAX request to save progress
                const formData = new FormData();
                formData.append('action', 'shopify_save_setup_progress');
                formData.append('checkbox_id', checkboxId);
                formData.append('checked', checked ? '1' : '0');
                formData.append('nonce', window.shopifySetupNonce || '');

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        // Revert checkbox state if save failed
                        this.checked = !checked;
                        console.error('Failed to save setup progress:', data.data || 'Unknown error');
                    }
                })
                .catch(error => {
                    // Revert checkbox state if request failed
                    this.checked = !checked;
                    console.error('Error saving setup progress:', error);
                });
            });
        });
    }
})();