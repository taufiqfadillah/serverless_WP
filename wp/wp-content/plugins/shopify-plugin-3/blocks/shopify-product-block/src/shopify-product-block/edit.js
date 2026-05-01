import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
	ComboboxControl,
	Spinner,
	PanelBody,
	Notice,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "@wordpress/element";
import { request, gql } from "graphql-request";
import { currencySymbolsMap } from "../../../../assets/js/utils/currency-symbol-map";
import {
	formatPrice,
	formatPriceRange,
} from "../../../../assets/js/utils/price-formatter";
import circleImage from "./public/circle.png";
import coneImage from "./public/cone.png";
import cubeImage from "./public/cube.png";
import "./editor.scss";
import { INITIAL_PRODUCTS, SEARCH_PRODUCTS } from "./utils/graphql-queries";
import ProductCard from "./components/ProductCard";
import OpenIcon from "./components/OpenIcon";
import StoreUrlMismatchError from "./components/StoreUrlMismatchError";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const { selectedProduct, selectedStoreUrl } = attributes;

	const isBrowser = typeof window !== "undefined";

	const country = isBrowser
		? document.documentElement.lang.split("-")[1]
		: "US";

	const language = isBrowser
		? document.documentElement.lang.split("-")[0].toUpperCase()
		: "EN";

	const { storeUrl: pluginBaseUrl, apiKey: pluginApiKey } =
		window.shopifyPluginSettings || {};
	const version = "2024-01";

	const baseUrl = pluginBaseUrl || "";
	const apiKey = pluginApiKey || "";

	const storeUrl = `https://${baseUrl}/api/${version}/graphql.json`;

	const [searchTerm, setSearchTerm] = useState(
		selectedProduct ? selectedProduct?.title : "",
	);
	const [options, setOptions] = useState([
		{
			label: __("Type to search products...", "shopify"),
			value: "",
		},
	]);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);
	const [mockImage, setMockImage] = useState(null);

	// Function to get a random mock image
	const getRandomMockImage = () => {
		const mockImages = [circleImage, coneImage, cubeImage];
		return mockImages[Math.floor(Math.random() * mockImages.length)];
	};

	// Set mock image once when component mounts
	useEffect(() => {
		setMockImage(getRandomMockImage());
	}, []);

	// Fetch initial products when component mounts
	useEffect(() => {
		const fetchInitialProducts = async () => {
			setIsLoading(true);
			try {
				const data = await request(
					storeUrl,
					INITIAL_PRODUCTS,
					{
						language,
						country,
					},
					{
						"X-Shopify-Storefront-Access-Token": apiKey,
					},
				);

				const initialOptions = data.products.edges.map(({ node }) => ({
					label: node.title,
					value: node.handle,
					product: node,
				}));
				setOptions(initialOptions);
			} catch (err) {
				setError(err.message);
				setOptions([
					{
						label: __("Error fetching products", "shopify"),
						value: "",
					},
				]);
			} finally {
				setIsLoading(false);
			}
		};

		fetchInitialProducts();
	}, []);

	// Fetch products when search term changes
	useEffect(() => {
		if (!searchTerm) {
			setOptions([
				{
					label: __("Type to search products...", "shopify"),
					value: "",
				},
			]);
			setIsLoading(false);
			setError(null);
			return;
		}

		const fetchProducts = async () => {
			setIsLoading(true);
			setError(null);
			try {
				const data = await request(
					storeUrl,
					SEARCH_PRODUCTS,
					{
						query: `title:${searchTerm}*`,
						language,
						country,
					},
					{
						"X-Shopify-Storefront-Access-Token": apiKey,
					},
				);

				const newOptions = data.products.edges.length
					? data.products.edges.map(({ node }) => ({
							label: node.title,
							value: node.handle,
							product: node,
					  }))
					: [
							{
								label: __("No products found", "shopify"),
								value: "",
							},
					  ];
				setOptions(newOptions);
			} catch (err) {
				setError(err.message);
				setOptions([
					{
						label: __("Error fetching products", "shopify"),
						value: "",
					},
				]);
			} finally {
				setIsLoading(false);
			}
		};

		// Debounce to avoid excessive API calls
		const timeout = setTimeout(fetchProducts, 300);
		return () => clearTimeout(timeout);
	}, [searchTerm]);

	const currencyCode =
		selectedProduct?.priceRange?.minVariantPrice?.currencyCode;
	const currencySymbol = currencySymbolsMap[currencyCode]?.symbol || "";

	const isPriceRangeTheSame =
		selectedProduct?.priceRange?.minVariantPrice?.amount ===
		selectedProduct?.priceRange?.maxVariantPrice?.amount;

	// Format prices using our new utility
	const formattedPrice = selectedProduct
		? isPriceRangeTheSame
			? formatPrice(
					selectedProduct?.variants?.edges[0]?.node?.price?.amount,
					currencyCode,
					currencySymbol,
			  )
			: formatPriceRange(
					selectedProduct?.priceRange?.minVariantPrice?.amount,
					selectedProduct?.priceRange?.maxVariantPrice?.amount,
					currencyCode,
					currencySymbol,
			  )
		: "";

	// Function to construct Shopify admin URL for editing a product
	const getShopifyAdminUrl = (product) => {
		if (!product?.id || !baseUrl) {
			return null;
		}

		// Extract the numeric ID from the Shopify ID (format: gid://shopify/Product/123456789)
		const productId = product.id.split("/").pop();

		// Get the store handle from the base URL
		const storeHandle = baseUrl.replace(".myshopify.com", "");

		return `https://admin.shopify.com/store/${storeHandle}/products/${productId}`;
	};

	const shopifyAdminUrl = getShopifyAdminUrl(selectedProduct);

	// Check for store URL mismatch
	// Show mismatch error if:
	// 1. We have a selected product AND
	// 2. Either:
	//    a) We have a stored selectedStoreUrl that doesn't match current baseUrl, OR
	//    b) We have no stored selectedStoreUrl but have credentials (product was selected without connection)
	const isStoreUrlMismatch =
		selectedProduct &&
		((selectedStoreUrl && selectedStoreUrl !== baseUrl) ||
			(!selectedStoreUrl && baseUrl));

	// Debug logging
	console.log("Product Block Debug:", {
		selectedProduct: !!selectedProduct,
		selectedStoreUrl,
		selectedStoreUrlType: typeof selectedStoreUrl,
		selectedStoreUrlTruthy: !!selectedStoreUrl,
		baseUrl,
		pluginBaseUrl,
		isStoreUrlMismatch,
		mismatchConditions: {
			hasProduct: !!selectedProduct,
			hasStoreUrl: !!selectedStoreUrl,
			urlsDifferent: selectedStoreUrl !== baseUrl,
		},
		windowSettings: window.shopifyPluginSettings,
	});

	const handleReconnect = () => {
		// Clear the selected product to force reselection
		setAttributes({
			selectedProduct: null,
			selectedStoreUrl: "",
		});
	};

	return (
		<div className="wp-block-shopify-product">
			<InspectorControls>
				<PanelBody title={__("Product Search", "shopify")}>
					{error && (
						<Notice
							className="shopify-product-block__error"
							status="error"
							isDismissible={false}
						>
							Unable to load products. Please ensure you’re connected to Shopify.
							<a
								className="wp-connect-button"
								href="/wp-admin/admin.php?page=shopify-settings"
							>
								Settings
							</a>
						</Notice>
					)}
					<ComboboxControl
						label={__("Search Products", "shopify")}
						placeholder={__("Search products...", "shopify")}
						value={selectedProduct?.handle || ""}
						options={options}
						onChange={(value) => {
							console.log("onChange", baseUrl);
							const selectedOption = options.find((opt) => opt.value === value);
							setAttributes({
								selectedProduct: selectedOption?.product || null,
								selectedStoreUrl: baseUrl, // Store the current base URL when product is selected
							});
						}}
						onFilterValueChange={(value) => setSearchTerm(value)}
						onBlur={() => {
							if (!selectedProduct) {
								setSearchTerm("");
								setOptions([
									{
										label: __("Type to search products...", "shopify"),
										value: "",
									},
								]);
							}
						}}
						disabled={isLoading}
					/>
					{isLoading && <Spinner />}
					{shopifyAdminUrl && (
						<div className="shopify-product-block__edit-link">
							<a
								href={shopifyAdminUrl}
								target="_blank"
								rel="noopener noreferrer"
								className="shopify-product-block__edit-link-button"
							>
								{__("Edit product in Shopify", "shopify")}
								<OpenIcon className="shopify-product-block__edit-link-icon" />
							</a>
						</div>
					)}
				</PanelBody>
			</InspectorControls>
			<div className="shopify-product-block__content" {...blockProps}>
				{isStoreUrlMismatch ? (
					<StoreUrlMismatchError
						selectedStoreUrl={selectedStoreUrl}
						currentStoreUrl={baseUrl}
						onReconnect={handleReconnect}
					/>
				) : (
					<ProductCard
						selectedProduct={selectedProduct}
						formattedPrice={formattedPrice}
						mockImage={mockImage}
						baseUrl={baseUrl}
						apiKey={apiKey}
					/>
				)}
			</div>
		</div>
	);
}
