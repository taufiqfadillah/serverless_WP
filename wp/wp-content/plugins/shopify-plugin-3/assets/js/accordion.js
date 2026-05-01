function shopifyToggleAccordion(event) {
	const header = event.currentTarget;
	const content = document.querySelector(header.dataset.toggle);

	// Toggle active class on header
	header.classList.toggle("active");

	// Toggle content visibility
	if (header.classList.contains("active")) {
		content.style.maxHeight = content.scrollHeight + "px";
		content.classList.add("active");
	} else {
		content.style.maxHeight = "0";
		content.classList.remove("active");
	}
}

// Initialize accordions with default open state
document.addEventListener("DOMContentLoaded", function () {
	const defaultOpenAccordions = document.querySelectorAll(
		".shopify-accordion__content.active"
	);
	defaultOpenAccordions.forEach((content) => {
		content.style.maxHeight = content.scrollHeight + "px";
	});
});
