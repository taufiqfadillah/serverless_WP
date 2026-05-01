import { __ } from "@wordpress/i18n";
import circleImage from "../public/circle.png";
import coneImage from "../public/cone.png";
import cubeImage from "../public/cube.png";

export default function MockProductGrid({
	maxProductsPerRow,
	maxProductsPerPage,
}) {
	const mockProducts = [
		{
			id: 1,
			title: "Product 1",
			image: circleImage,
		},
		{
			id: 2,
			title: "Product 2",
			image: coneImage,
		},
		{
			id: 3,
			title: "Product 3",
			image: cubeImage,
		},
	];

	return (
		<div
			className={`wp-shopify-mock-product-grid max-products-per-row-${
				maxProductsPerRow || 3
			} max-products-per-page-${maxProductsPerPage || 12}`}
		>
			<div className="wp-shopify-product-grid">
				{[...Array(maxProductsPerRow || 3)].map((_, index) => {
					const product = mockProducts[index % mockProducts.length];
					return (
						<div className="shopify-product-block__product" key={index}>
							<div className="shopify-product-block__product__image">
								<img src={product.image} alt={product.title} />
							</div>
							<div className="shopify-product-block__product__content">
								<h3>{product.title}</h3>
								<p className="price">$0</p>
							</div>
						</div>
					);
				})}
			</div>
		</div>
	);
}
