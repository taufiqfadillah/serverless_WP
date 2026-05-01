import { currencySymbolsMap } from "./currency-symbol-map";

/**
 * Formats a price amount by removing trailing zeros based on currency configuration
 * @param {string|number} amount - The price amount
 * @param {string} currencyCode - The currency code (e.g., 'USD', 'EUR', 'JPY')
 * @param {string} currencySymbol - The currency symbol to prepend
 * @returns {string} Formatted price string
 */
export function formatPrice(amount, currencyCode, currencySymbol = "") {
	if (!amount || !currencyCode) {
		return "";
	}

	// Get currency configuration
	const currencyConfig = currencySymbolsMap[currencyCode];
	if (!currencyConfig) {
		// Fallback to default formatting if currency not found
		return `${currencySymbol}${amount}`;
	}

	// Convert amount to number if it's a string
	const numericAmount =
		typeof amount === "string" ? parseFloat(amount) : amount;

	// Check if the amount is a valid number
	if (isNaN(numericAmount)) {
		return `${currencySymbol}${amount}`;
	}

	// Format based on currency's decimal digits configuration
	const decimalDigits = currencyConfig.decimal_digits;

	// For currencies with 0 decimal digits (like JPY, KRW), don't show decimals
	if (decimalDigits === 0) {
		return `${currencySymbol}${Math.round(numericAmount)}`;
	}

	// For currencies with decimal digits, format and remove trailing zeros
	const formattedAmount = numericAmount.toFixed(decimalDigits);

	// Remove trailing zeros after decimal point
	const trimmedAmount = formattedAmount.replace(/\.?0+$/, "");

	// Ensure we don't end up with just a decimal point
	const finalAmount = trimmedAmount.endsWith(".")
		? trimmedAmount.slice(0, -1)
		: trimmedAmount;

	return `${currencySymbol}${finalAmount}`;
}

/**
 * Formats a price range by removing trailing zeros based on currency configuration
 * @param {string|number} minAmount - The minimum price amount
 * @param {string|number} maxAmount - The maximum price amount
 * @param {string} currencyCode - The currency code
 * @param {string} currencySymbol - The currency symbol to prepend
 * @returns {string} Formatted price range string
 */
export function formatPriceRange(
	minAmount,
	maxAmount,
	currencyCode,
	currencySymbol = ""
) {
	if (!minAmount || !maxAmount || !currencyCode) {
		return "";
	}

	const formattedMin = formatPrice(minAmount, currencyCode, "");
	const formattedMax = formatPrice(maxAmount, currencyCode, "");

	return `${currencySymbol}${formattedMin} - ${currencySymbol}${formattedMax}`;
}
