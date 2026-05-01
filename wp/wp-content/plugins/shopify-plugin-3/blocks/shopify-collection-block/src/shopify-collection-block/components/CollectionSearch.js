import { ComboboxControl, Spinner, Notice } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "@wordpress/element";
import { request } from "graphql-request";
import { SEARCH_COLLECTIONS } from "../utils/graphql-queries";
import OpenIcon from "./OpenIcon";

export default function CollectionSearch({
	selectedCollection,
	onCollectionSelect,
	storeUrl,
	apiKey,
	country,
	language,
}) {
	const [searchTerm, setSearchTerm] = useState(
		selectedCollection ? selectedCollection?.title : "",
	);
	const [options, setOptions] = useState(
		selectedCollection
			? [
					{
						label: selectedCollection.title,
						value: selectedCollection.handle,
						collection: selectedCollection,
					},
			  ]
			: [
					{
						label: __("Type to search collections...", "shopify"),
						value: "",
					},
			  ],
	);
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);

	// Function to construct Shopify admin URL for editing a collection
	const getShopifyAdminUrl = (collection) => {
		if (!collection?.id || !storeUrl) {
			return null;
		}

		// Extract the numeric ID from the Shopify ID (format: gid://shopify/Collection/123456789)
		const collectionId = collection.id.split("/").pop();

		// Get the store handle from the store URL
		const urlParts = storeUrl.split("/");
		const storeHandle = urlParts[2]?.replace(".myshopify.com", "");

		if (!storeHandle) {
			return null;
		}

		return `https://admin.shopify.com/store/${storeHandle}/collections/${collectionId}`;
	};

	const shopifyAdminUrl = getShopifyAdminUrl(selectedCollection);

	// Fetch collections on component mount
	useEffect(() => {
		const fetchInitialCollections = async () => {
			setIsLoading(true);
			setError(null);
			try {
				const data = await request(
					storeUrl,
					SEARCH_COLLECTIONS,
					{
						query: "",
						country: country,
						language: language,
					},
					{
						"X-Shopify-Storefront-Access-Token": apiKey,
					},
				);

				const newOptions = data.collections.edges.length
					? data.collections.edges.map(({ node }) => ({
							label: node.title,
							value: node.handle,
							collection: node,
					  }))
					: [
							{
								label: __("No collections found", "shopify"),
								value: "",
							},
					  ];
				setOptions(newOptions);
			} catch (err) {
				setError(err.message);
				setOptions([
					{
						label: __("Error fetching collections", "shopify"),
						value: "",
					},
				]);
			} finally {
				setIsLoading(false);
			}
		};

		fetchInitialCollections();
	}, [storeUrl, apiKey]);

	// Fetch collections when search term changes
	useEffect(() => {
		if (!searchTerm) {
			setIsLoading(false);
			setError(null);
			return;
		}

		const fetchCollections = async () => {
			setIsLoading(true);
			setError(null);

			try {
				const data = await request(
					storeUrl,
					SEARCH_COLLECTIONS,
					{
						query: `title:${searchTerm}*`,
						country: country,
						language: language,
					},
					{
						"X-Shopify-Storefront-Access-Token": apiKey,
					},
				);

				const newOptions = data.collections.edges.length
					? data.collections.edges.map(({ node }) => ({
							label: node.title,
							value: node.handle,
							collection: node,
					  }))
					: [
							{
								label: __("No collections found", "shopify"),
								value: "",
							},
					  ];
				setOptions(newOptions);
			} catch (err) {
				setError(err.message);
				setOptions([
					{
						label: __("Error fetching collections", "shopify"),
						value: "",
					},
				]);
			} finally {
				setIsLoading(false);
			}
		};

		// Debounce to avoid excessive API calls
		const timeout = setTimeout(fetchCollections, 300);
		return () => clearTimeout(timeout);
	}, [searchTerm, storeUrl, apiKey]);

	const handleCollectionChange = (value) => {
		const selectedOption = options.find((opt) => opt.value === value);
		onCollectionSelect(selectedOption?.collection || null);
	};

	return (
		<>
			{error && (
				<Notice
					className="shopify-collection-block__error"
					status="error"
					isDismissible={false}
				>
					{__(
						"Unable to load collections. Please ensure you’re connected to Shopify.", 
						"shopify",
					)}

					<a
						className="wp-connect-button"
						href="/wp-admin/admin.php?page=shopify-settings"
					>
						{__("Settings", "shopify")}
					</a>
				</Notice>
			)}
			<ComboboxControl
				label={__("Search Collections", "shopify")}
				value={selectedCollection?.handle || ""}
				options={options}
				onChange={handleCollectionChange}
				onFilterValueChange={(value) => setSearchTerm(value)}
				disabled={isLoading}
				placeholder={__("Search collections...", "shopify")}
			/>

			{shopifyAdminUrl && (
				<div className="shopify-collection-block__edit-link">
					<a
						href={shopifyAdminUrl}
						target="_blank"
						rel="noopener noreferrer"
						className="shopify-collection-block__edit-link-button"
					>
						{__("Edit collection in Shopify", "shopify")}
						<OpenIcon className="shopify-collection-block__edit-link-icon" />
					</a>
				</div>
			)}

			{isLoading && <Spinner />}
		</>
	);
}
