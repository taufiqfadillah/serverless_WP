import { __ } from "@wordpress/i18n";
import AttentionIcon from "./AttentionIcon";
import OpenIcon from "./OpenIcon";

export default function StoreUrlMismatchError({
	selectedStoreUrl,
	currentStoreUrl,
	onReconnect,
}) {
	const handleWordPressSettings = () => {
		// Navigate to WordPress > Settings > Shopify
		window.location.href = "/wp-admin/admin.php?page=shopify-settings";
	};

	const handleProductPage = () => {
		// This would ideally navigate to the specific product page in Shopify
		// For now, we'll open the Shopify admin in a new tab
		window.open("https://admin.shopify.com/", "_blank");
	};

	const handleDocumentation = () => {
		// Navigate to the documentation page
		window.location.href = "/wp-admin/admin.php?page=shopify-documentation#accordion-set-up-sell-on-wordpress-sales-channel";
	};

	return (
		<div className="shopify-store-url-mismatch-error">
			<div className="error-content">
				<div className="error-header">
					<div className="error-icon">
						<AttentionIcon />
					</div>
					<h3 className="error-title">
						{__("Collection not connected", "shopify")}
					</h3>
				</div>
				<div className="error-message">
					<p>
						{__(
							"Your collection is no longer connected. To reconnect:",
							"shopify"
						)}
					</p>
					<ul className="reconnect-steps">
						<li>
							{__(
								"Make sure your WordPress site is connected to Shopify in WordPress > Settings.",
								"shopify"
							)}
						</li>
						<li>
							{__(
								"Review your collection in Shopify to confirm it’s linked to the Sell on WordPress sales channel.",
								"shopify"
							)}
						</li>
					</ul>
					<p className="help-link">
						{__("Need more help? ", "shopify")}
						<button className="link-button" onClick={handleDocumentation}>
							{__("Read the documentation", "shopify")}
						</button>
						{__(".", "shopify")}
					</p>
				</div>
				<div className="error-actions">
					<button
						className="sm wp-connect-button inverse"
						onClick={handleWordPressSettings}
					>
						{__("WordPress settings", "shopify")}
					</button>
					<button className="sm wp-connect-button" onClick={handleProductPage}>
						{__("Collection page", "shopify")}
						<OpenIcon width={21} height={21} />
					</button>
				</div>
			</div>
		</div>
	);
}
