function toggleDocumentationAccordion(event) {
	event.preventDefault();
	event.stopPropagation();

	const header = event.currentTarget;
	const content = header.nextElementSibling;
	const isActive = header.classList.contains("active");

	console.log("Documentation accordion clicked:", {
		isActive,
		header: header.textContent.trim(),
	});

	// Toggle current accordion (allow multiple to be open)
	if (!isActive) {
		header.classList.add("active");
		const scrollHeight = content.scrollHeight;
		content.style.maxHeight = scrollHeight + "px";
		content.classList.add("active");
		console.log(
			"Opening documentation accordion, scrollHeight:",
			scrollHeight
		);
	} else {
		header.classList.remove("active");
		content.style.maxHeight = "0";
		content.classList.remove("active");
		console.log("Closing documentation accordion");
	}

	// Update URL hash with open accordions
	updateAccordionHash();
}

// Function to update URL hash with currently open accordions
function updateAccordionHash() {
	const openAccordions = Array.from(
		document.querySelectorAll(".documentation-accordion__header.active")
	)
		.map((header) => header.id)
		.filter((id) => id); // Filter out empty IDs

	const currentUrl = new URL(window.location);
	if (openAccordions.length > 0) {
		currentUrl.hash = openAccordions.join(",");
	} else {
		currentUrl.hash = "";
	}
	window.history.replaceState(null, null, currentUrl.toString());
}

// Function to open accordion by ID
function openAccordionById(accordionId) {
	const header = document.getElementById(accordionId);
	if (header && !header.classList.contains("active")) {
		const content = header.nextElementSibling;
		if (content) {
			header.classList.add("active");
			const scrollHeight = content.scrollHeight;
			content.style.maxHeight = scrollHeight + "px";
			content.classList.add("active");
			console.log("Opened accordion:", accordionId);
		}
	}
}

// Function to close accordion by ID
function closeAccordionById(accordionId) {
	const header = document.getElementById(accordionId);
	if (header && header.classList.contains("active")) {
		const content = header.nextElementSibling;
		if (content) {
			header.classList.remove("active");
			content.style.maxHeight = "0";
			content.classList.remove("active");
			console.log("Closed accordion:", accordionId);
		}
	}
}

// Function to get accordion IDs from URL hash
function getAccordionIdsFromHash() {
	const hash = window.location.hash.substring(1); // Remove the # symbol
	if (!hash) return [];

	// Split by comma and filter out empty strings
	return hash.split(",").filter((id) => id.trim() !== "");
}

// Function to initialize accordions based on URL hash
function initializeAccordionsFromHash() {
	const accordionIds = getAccordionIdsFromHash();
	console.log("Initializing accordions from hash:", accordionIds);

	// Close all accordions first
	document
		.querySelectorAll(".documentation-accordion__header")
		.forEach((header) => {
			header.classList.remove("active");
			const content = header.nextElementSibling;
			if (content) {
				content.style.maxHeight = "0";
				content.classList.remove("active");
			}
		});

	// Open accordions specified in hash
	accordionIds.forEach((accordionId) => {
		openAccordionById(accordionId);
	});

	// Scroll to the first accordion only if there's exactly one open
	if (accordionIds.length === 1) {
		const firstAccordionId = accordionIds[0];
		const accordionElement = document.getElementById(firstAccordionId);
		if (accordionElement) {
			// Small delay to ensure the accordion is fully opened
			setTimeout(() => {
				accordionElement.scrollIntoView({
					behavior: "smooth",
					block: "start",
				});
				console.log("Scrolled to single accordion:", firstAccordionId);
			}, 100);
		}
	} else if (accordionIds.length > 1) {
		console.log("Multiple accordions open, skipping scroll");
	}
}

// Add event listeners when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
	console.log("Documentation page loaded, initializing accordions...");

	const accordionHeaders = document.querySelectorAll(
		".documentation-accordion__header"
	);

	console.log(
		"Found",
		accordionHeaders.length,
		"documentation accordion headers"
	);

	accordionHeaders.forEach((header, index) => {
		// Remove any existing listeners to prevent duplicates
		header.removeEventListener("click", toggleDocumentationAccordion);
		header.addEventListener("click", toggleDocumentationAccordion);

		console.log(
			"Documentation accordion",
			index,
			"initialized:",
			header.textContent.trim(),
			"ID:",
			header.id
		);
	});

	// Initialize accordions based on URL hash
	initializeAccordionsFromHash();

	// Handle browser back/forward navigation
	window.addEventListener("hashchange", function () {
		console.log("Hash changed, reinitializing accordions...");
		initializeAccordionsFromHash();
	});
});
