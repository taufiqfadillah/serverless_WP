import { __ } from "@wordpress/i18n";
import { currencySymbolsMap } from "../../../../../assets/js/utils/currency-symbol-map";
import {
	formatPrice,
	formatPriceRange,
} from "../../../../../assets/js/utils/price-formatter";

export default function ProductGrid({ products, maxProductsPerRow }) {
	if (!products || products.length === 0) {
		return (
			<div className="wp-shopify-flex-grid">
				<p>{__("No products found in this collection.", "shopify")}</p>
			</div>
		);
	}

	const currencyCode =
		products[0]?.node?.variants?.edges[0]?.node?.price?.currencyCode;
	const currencySymbol = currencySymbolsMap[currencyCode]?.symbol || "";

	// Get the first variant's price for comparison
	const firstVariantPrice =
		products[0]?.node?.variants?.edges[0]?.node?.price?.amount;

	// Check if all variants have the same price
	const isPriceRangeTheSame = products.every(({ node }) => {
		const variantPrice = node?.variants?.edges[0]?.node?.price?.amount;
		return variantPrice === firstVariantPrice;
	});

	return (
		<div className="wp-shopify-product-grid">
			{products.map(({ node }) => {
				const variantPrice = node?.variants?.edges[0]?.node?.price?.amount;

				// Format the price using our new utility
				const formattedPrice = isPriceRangeTheSame
					? formatPrice(variantPrice, currencyCode, currencySymbol)
					: formatPrice(variantPrice, currencyCode, currencySymbol); // For now, just show single price

				return (
					<div className="shopify-product-block__product" key={node.id}>
						<div className="shopify-product-block__product__image">
							<img src={node?.images?.edges[0]?.node?.url} alt={node?.title} />
						</div>
						<div className="shopify-product-block__product__content">
							<h3>{node?.title}</h3>
							<p className="price">{formattedPrice}</p>
						</div>
					</div>
				);
			})}
		</div>
	);
}
