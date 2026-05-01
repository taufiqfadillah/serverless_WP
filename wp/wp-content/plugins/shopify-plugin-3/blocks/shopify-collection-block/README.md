# Shopify Collection Block Components

This directory contains the modular components for the Shopify Collection Block, organized for better maintainability and reusability.

## Component Structure

### Core Components

#### `CollectionSearch.js`

Handles the collection search functionality with the combobox control and search logic.

- **Props:**
  - `selectedCollection`: Currently selected collection object
  - `onCollectionSelect`: Callback function when a collection is selected
  - `storeUrl`: Shopify store URL for API calls
  - `apiKey`: Shopify API key for authentication
  - `country`: Country code for localization
  - `language`: Language code for localization

#### `ProductGrid.js`

Displays the products in a grid format with pricing information.

- **Props:**
  - `products`: Array of product objects to display
  - `maxProductsPerRow`: Maximum number of products per row

#### `PaginationControls.js`

Handles pagination controls with previous/next buttons and page information.

- **Props:**
  - `currentPage`: Current page number
  - `pageInfo`: Page information object from Shopify API
  - `isLoading`: Loading state for pagination
  - `onPreviousPage`: Callback for previous page
  - `onNextPage`: Callback for next page

#### `MockProductGrid.js`

Shows placeholder products when no collection is selected.

- **Props:**
  - `maxProductsPerRow`: Maximum number of products per row
  - `maxProductsPerPage`: Maximum number of products per page

### Utilities

The utilities are located in the `../utils/` directory:

#### `graphql-queries.js`

Centralizes all GraphQL queries used in the collection block.

- `SEARCH_COLLECTIONS`: Query to search collections by title
- `FETCH_PRODUCTS_PAGE`: Query for pagination
- `MOCK_PRODUCTS`: Query for mock products (if needed)

#### `usePagination.js`

Custom hook to handle pagination logic and state management.

- **Parameters:**
  - `selectedCollection`: Currently selected collection
  - `storeUrl`: Shopify store URL
  - `apiKey`: Shopify API key
- **Returns:**
  - `currentPage`: Current page number
  - `pageInfo`: Page information
  - `isLoading`: Loading state
  - `error`: Error state
  - `fetchPreviousPage`: Function to fetch previous page
  - `fetchNextPage`: Function to fetch next page
  - `getCurrentPageProducts`: Function to get current page products

#### `index.js`

Exports all components for easy importing.

## Usage

```javascript
import {
	CollectionSearch,
	ProductGrid,
	PaginationControls,
	MockProductGrid,
} from "./components";
import { usePagination } from "../utils";
```

## Benefits of This Structure

1. **Separation of Concerns**: Each component has a single responsibility
2. **Reusability**: Components can be easily reused in other parts of the application
3. **Maintainability**: Easier to debug and modify individual components
4. **Testability**: Each component can be tested independently
5. **Readability**: The main edit.js file is now much cleaner and easier to understand
6. **Scalability**: Easy to add new features or modify existing ones

## File Size Reduction

The original `edit.js` file was **566 lines** and has been reduced to **140 lines** (75% reduction), with functionality distributed across focused, single-purpose components.

## Current File Structure

```
components/
├── CollectionSearch.js (187 lines)
├── ProductGrid.js (58 lines)
├── PaginationControls.js (56 lines)
├── MockProductGrid.js (53 lines)
├── index.js (5 lines)
└── README.md

utils/
├── usePagination.js (132 lines)
├── graphql-queries.js (151 lines)
└── index.js (3 lines)
```
