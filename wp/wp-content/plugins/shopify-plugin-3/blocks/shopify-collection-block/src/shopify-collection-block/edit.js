import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import {
	PanelBody,
	__experimentalNumberControl as NumberControl,
	ToggleControl,
	SelectControl,
} from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import "./editor.scss";
import MockProductGrid from "./components/MockProductGrid";

import {
	CollectionSearch,
	ProductGrid,
	PaginationControls,
	StoreUrlMismatchError,
} from "./components";
import { usePagination } from "./utils";

export default function Edit({ attributes, setAttributes }) {
	const blockProps = useBlockProps();
	const {
		selectedCollection,
		selectedStoreUrl,
		maxProductsPerRow,
		maxProductsPerPage,
		showPagination,
	} = attributes;

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

	// Use the pagination hook
	const {
		currentPage,
		pageInfo,
		isLoading: paginationLoading,
		error: paginationError,
		fetchPreviousPage,
		fetchNextPage,
		getCurrentPageProducts,
	} = usePagination(selectedCollection, storeUrl, apiKey, 8);

	const handleCollectionSelect = (collection) => {
		setAttributes({
			selectedCollection: collection,
			selectedStoreUrl: baseUrl, // Store the current base URL when collection is selected
			maxProductsPerRow: maxProductsPerRow || 3,
			maxProductsPerPage: maxProductsPerPage || 12,
		});
	};

	// Check for store URL mismatch
	// Show mismatch error if:
	// 1. We have a selected collection
	// 2. Either:
	//    a) We have a stored selectedStoreUrl that doesn't match current baseUrl, OR
	//    b) We have a collection but no stored selectedStoreUrl AND no credentials available
	const isStoreUrlMismatch =
		selectedCollection &&
		((selectedStoreUrl && selectedStoreUrl !== baseUrl) ||
			(!selectedStoreUrl && !baseUrl));

	console.log("Collection Block Debug:", {
		selectedCollection: !!selectedCollection,
		selectedStoreUrl,
		baseUrl,
		pluginBaseUrl,
		isStoreUrlMismatch,
		windowSettings: window.shopifyPluginSettings,
	});

	const handleReconnect = () => {
		setAttributes({
			selectedCollection: null,
			selectedStoreUrl: "",
		});
	};

	return (
		<div className="wp-block-shopify-collection-shopify-collection-block">
			<InspectorControls>
				<PanelBody title={__("Collection Search", "shopify")}>
					<div className="wp-block-shopify-collection__search-container">
						<CollectionSearch
							selectedCollection={selectedCollection}
							onCollectionSelect={handleCollectionSelect}
							storeUrl={storeUrl}
							apiKey={apiKey}
							maxProductsPerPage={8}
							country={country}
							language={language}
						/>
					</div>
					<NumberControl
						label={__("Max Products Per Row (max 6)", "shopify")}
						value={maxProductsPerRow > 6 ? 6 : maxProductsPerRow || 3}
						onChange={(value) => {
							if (value > 6) {
								return;
							}
							setAttributes({
								maxProductsPerRow: parseFloat(value),
							});
						}}
						maximum={6}
					/>
					<NumberControl
						label={__("Max Products Per Page", "shopify")}
						value={maxProductsPerPage || 12}
						minimum={1}
						onChange={(value) => {
							if (value < 0) {
								setAttributes({
									maxProductsPerPage: 0,
								});
								return false;
							}
							setAttributes({
								maxProductsPerPage: parseFloat(value),
							});
						}}
					/>
					<p className="components-base-control__help">
						{__(
							"You can view the full products display in page preview state",
							"shopify"
						)}
					</p>
				</PanelBody>
			</InspectorControls>
			<div className="shopify-collection-preview" {...blockProps}>
				{isStoreUrlMismatch ? (
					<StoreUrlMismatchError
						selectedStoreUrl={selectedStoreUrl}
						currentStoreUrl={baseUrl}
						onReconnect={handleReconnect}
					/>
				) : selectedCollection ? (
					<>
						<div>
							<div
								className={`wp-shopify-collection-grid max-products-per-row-${
									maxProductsPerRow || 3
								} max-products-per-page-${maxProductsPerPage || 12}`}
							>
								<ProductGrid
									products={getCurrentPageProducts()}
									maxProductsPerRow={maxProductsPerRow}
								/>
								<PaginationControls
									currentPage={currentPage}
									pageInfo={pageInfo}
									isLoading={paginationLoading}
									onPreviousPage={fetchPreviousPage}
									onNextPage={fetchNextPage}
								/>
							</div>
						</div>
					</>
				) : (
					<MockProductGrid
						maxProductsPerRow={maxProductsPerRow}
						maxProductsPerPage={maxProductsPerPage}
					/>
				)}
			</div>
		</div>
	);
}
