import { __ } from "@wordpress/i18n";

const ProductCard = ({
	selectedProduct,
	formattedPrice,
	mockImage,
	baseUrl,
	apiKey,
}) => {
	return selectedProduct ? (
		<div className="shopify-product-block__product solo-product-block">
			{selectedProduct?.images?.edges[0]?.node?.url && (
				<div className="shopify-product-block__product__image">
					<img
						src={selectedProduct?.images?.edges[0]?.node?.url}
						alt={selectedProduct?.title}
						style={{ maxWidth: "100%", height: "auto" }}
					/>
				</div>
			)}
			<div className="shopify-product-block__product__content">
				<h3 style={{ margin: "10px 0" }}>{selectedProduct?.title}</h3>
				<p style={{ color: "#777", margin: "10px 0 0" }}>{formattedPrice}</p>
			</div>
		</div>
	) : (
		<div className="shopify-product-block__placeholder solo-product-block">
			<div className="shopify-product-block__placeholder__image">
				<img
					src={mockImage}
					alt="Mock Product"
					style={{ maxWidth: "100%", height: "auto", width: "100%" }}
				/>
			</div>

			<div className="shopify-product-block__placeholder__content">
				<h3 style={{ margin: "10px 0" }}>{__("Product", "shopify")}</h3>
				<p style={{ color: "#777", margin: "10px 0 0" }}>
					{__("$0.00", "shopify")}
				</p>
			</div>
		</div>
	);
};

export default ProductCard;
