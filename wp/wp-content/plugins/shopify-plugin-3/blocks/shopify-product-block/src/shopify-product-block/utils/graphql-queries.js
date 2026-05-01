import { gql } from "graphql-request";

// GraphQL query to search products by title
export const SEARCH_PRODUCTS = gql`
	query SearchProducts(
		$query: String!
		$language: LanguageCode
		$country: CountryCode
	) @inContext(language: $language, country: $country) {
		products(first: 10, query: $query) {
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
				}
			}
		}
	}
`;

// Query for initial products
export const INITIAL_PRODUCTS = gql`
	query InitialProducts($language: LanguageCode, $country: CountryCode)
	@inContext(language: $language, country: $country) {
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
				}
			}
		}
	}
`;
