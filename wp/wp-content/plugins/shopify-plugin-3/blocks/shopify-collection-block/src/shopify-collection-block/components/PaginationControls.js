import { __ } from "@wordpress/i18n";

export default function PaginationControls({
	currentPage,
	pageInfo,
	isLoading,
	onPreviousPage,
	onNextPage,
}) {
	return (
		<div className="wp-shopify-pagination-controls">
			<button
				className="pagination-button pagination-button--previous"
				onClick={onPreviousPage}
				disabled={!pageInfo?.hasPreviousPage || currentPage <= 1 || isLoading}
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					strokeWidth="2"
					strokeLinecap="round"
					strokeLinejoin="round"
					className="feather feather-chevron-left"
				>
					<polyline points="15 18 9 12 15 6"></polyline>
				</svg>
			</button>

			<button
				className="pagination-button pagination-button--next"
				onClick={onNextPage}
				disabled={!pageInfo?.hasNextPage || isLoading}
			>
				<svg
					xmlns="http://www.w3.org/2000/svg"
					width="24"
					height="24"
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					strokeWidth="2"
					strokeLinecap="round"
					strokeLinejoin="round"
					className="feather feather-chevron-right"
				>
					<polyline points="9 18 15 12 9 6"></polyline>
				</svg>
			</button>
		</div>
	);
}
