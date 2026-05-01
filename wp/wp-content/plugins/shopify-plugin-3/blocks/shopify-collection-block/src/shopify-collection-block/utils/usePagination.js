import { useState, useEffect } from "@wordpress/element";
import { request } from "graphql-request";
import { FETCH_PRODUCTS_PAGE } from "./graphql-queries";

export default function usePagination(selectedCollection, storeUrl, apiKey) {
	const [currentPage, setCurrentPage] = useState(1);
	const [pageInfo, setPageInfo] = useState(null);
	const [pages, setPages] = useState([]);
	const [pageInfos, setPageInfos] = useState([]); // Track pageInfo for each page
	const [isLoading, setIsLoading] = useState(false);
	const [error, setError] = useState(null);

	// Function to fetch previous page of products
	const fetchPreviousPage = async () => {
		if (!selectedCollection || !pageInfo?.hasPreviousPage || currentPage <= 1)
			return;

		setIsLoading(true);
		setError(null);
		try {
			// If we already have the previous page in our pages array, just show it
			if (currentPage > 1) {
				const newCurrentPage = currentPage - 1;
				setCurrentPage(newCurrentPage);
				// Update pageInfo to the correct page's pagination state
				if (pageInfos[newCurrentPage - 1]) {
					setPageInfo(pageInfos[newCurrentPage - 1]);
				}
				setIsLoading(false);
				return;
			}

			const data = await request(
				storeUrl,
				FETCH_PRODUCTS_PAGE,
				{
					handle: selectedCollection.handle,
					before: pageInfo.startCursor,
				},
				{
					"X-Shopify-Storefront-Access-Token": apiKey,
				},
			);

			if (data.collectionByHandle?.products) {
				const newPage = data.collectionByHandle.products.edges;
				const newPageInfo = data.collectionByHandle.products.pageInfo;
				setPages((prev) => [newPage, ...prev]);
				setPageInfos((prev) => [newPageInfo, ...prev]);
				setPageInfo(newPageInfo);
				setCurrentPage((prev) => prev - 1);
			}
		} catch (err) {
			setError(err.message);
		} finally {
			setIsLoading(false);
		}
	};

	// Function to fetch next page of products
	const fetchNextPage = async () => {
		if (!selectedCollection || !pageInfo?.hasNextPage) return;

		setIsLoading(true);
		setError(null);
		try {
			// If we already have the next page in our pages array, just show it
			if (currentPage < pages.length) {
				const newCurrentPage = currentPage + 1;
				setCurrentPage(newCurrentPage);
				// Update pageInfo to the correct page's pagination state
				if (pageInfos[newCurrentPage - 1]) {
					setPageInfo(pageInfos[newCurrentPage - 1]);
				}
				setIsLoading(false);
				return;
			}

			const data = await request(
				storeUrl,
				FETCH_PRODUCTS_PAGE,
				{
					handle: selectedCollection.handle,
					after: pageInfo.endCursor,
				},
				{
					"X-Shopify-Storefront-Access-Token": apiKey,
				},
			);

			if (data.collectionByHandle?.products) {
				const newPage = data.collectionByHandle.products.edges;
				const newPageInfo = data.collectionByHandle.products.pageInfo;
				setPages((prev) => [...prev, newPage]);
				setPageInfos((prev) => [...prev, newPageInfo]);
				setPageInfo(newPageInfo);
				setCurrentPage((prev) => prev + 1);
			}
		} catch (err) {
			setError(err.message);
		} finally {
			setIsLoading(false);
		}
	};

	// Update pageInfo and pages when collection changes
	useEffect(() => {
		if (selectedCollection?.products?.pageInfo) {
			setPageInfo(selectedCollection.products.pageInfo);
			setPages([selectedCollection.products.edges]);
			setPageInfos([selectedCollection.products.pageInfo]);
			setCurrentPage(1);
			setError(null);
		}
	}, [selectedCollection]);

	// Get current page products
	const getCurrentPageProducts = () => {
		return pages[currentPage - 1] || [];
	};

	return {
		currentPage,
		pageInfo,
		isLoading,
		error,
		fetchPreviousPage,
		fetchNextPage,
		getCurrentPageProducts,
	};
}
