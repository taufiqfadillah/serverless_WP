import { gql } from "graphql-request";

// GraphQL query to search collections by title
export const SEARCH_COLLECTIONS = gql`
	query SearchCollections(
		$query: String!
		$country: CountryCode
		$language: LanguageCode
	) @inContext(country: $country, language: $language) {
		collections(first: 10, query: $query) {
			edges {
				node {
					id
					title
					description
					handle
					image {
						url
					}
					products(first: 9) {
						edges {
							cursor
							node {
								id
								title
								handle
								priceRange {
									minVariantPrice {
										amount
										currencyCode
									}
									maxVariantPrice {
										amount
										currencyCode
									}
								}
								variants(first: 1) {
									edges {
										node {
											price {
												amount
												currencyCode
											}
										}
									}
								}
								images(first: 1) {
									edges {
										node {
											url
										}
									}
								}
							}
						}
						pageInfo {
							hasNextPage
							hasPreviousPage
							startCursor
							endCursor
						}
					}
				}
			}
		}
	}
`;

// Query for pagination
export const FETCH_PRODUCTS_PAGE = gql`
	query FetchProductsPage($handle: String!, $after: String, $before: String) {
		collectionByHandle(handle: $handle) {
			products(first: 8, after: $after, before: $before) {
				edges {
					cursor
					node {
						id
						title
						handle
						priceRange {
							minVariantPrice {
								amount
								currencyCode
							}
							maxVariantPrice {
								amount
								currencyCode
							}
						}
						variants(first: 1) {
							edges {
								node {
									price {
										amount
										currencyCode
									}
								}
							}
						}
						images(first: 1) {
							edges {
								node {
									url
								}
							}
						}
					}
				}
				pageInfo {
					hasNextPage
					hasPreviousPage
					startCursor
					endCursor
				}
			}
		}
	}
`;

export const MOCK_PRODUCTS = gql`
	query MockProducts @inContext(country: $country, language: $language) {
		products(first: 6) {
			edges {
				node {
					id
					title
					description
					handle
					images(first: 1) {
						edges {
							node {
								url
							}
						}
					}
					variants(first: 1) {
						edges {
							node {
								price {
									amount
									currencyCode
								}
							}
						}
					}
				}
			}
		}
	}
`;
