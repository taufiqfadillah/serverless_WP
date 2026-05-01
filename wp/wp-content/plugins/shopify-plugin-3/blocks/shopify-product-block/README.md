# Shopify Product Block Components

This directory contains the modular components for the Shopify Product Block, organized for better maintainability and reusability.

## Component Structure

### Core Components

#### `ProductCard.js`

Displays a single product with image, title, and formatted price information.

- **Props:**
  - `selectedProduct`: Currently selected product object
  - `formattedPrice`: Pre-formatted price string
  - `mockImage`: Mock image for placeholder state

### Utilities

The utilities are located in the `../utils/` directory:

#### `graphql-queries.js`

Centralizes all GraphQL queries used in the product block.

- `SEARCH_PRODUCTS`: Query to search products by title
- `INITIAL_PRODUCTS`: Query for initial product list

## Current Implementation

The product block currently has a simpler structure compared to the collection block, with most of the logic contained within the main `edit.js` file. The block includes:

- Product search functionality with combobox control
- Product display with image, title, and price
- Mock placeholder when no product is selected
- Error handling for API connections
- Price formatting utilities

## Usage

```javascript
import ProductCard from "./components/ProductCard";
import { INITIAL_PRODUCTS, SEARCH_PRODUCTS } from "../utils/graphql-queries";
```

## Current File Structure

```
components/
├── ProductCard.js (41 lines)
└── README.md

utils/
├── graphql-queries.js (93 lines)

edit.js (263 lines)
```

## Opportunities for Refactoring

The current `edit.js` file is **263 lines** and could benefit from further modularization similar to the collection block. Potential improvements include:

1. **Extract Product Search Component**: Move the search logic and combobox control to a separate component
2. **Create Price Formatting Hook**: Extract price formatting logic into a custom hook
3. **Separate Mock Image Logic**: Create a utility for mock image handling
4. **Error Handling Component**: Extract error display logic

## Benefits of Current Structure

1. **Separation of Concerns**: ProductCard component handles display logic separately
2. **Reusability**: ProductCard can be easily reused in other parts of the application
3. **Maintainability**: GraphQL queries are centralized in utils
4. **Readability**: Clear separation between display and data fetching logic

## Future Enhancements

Consider implementing the following to match the collection block's modular structure:

1. **ProductSearch Component**: Handle search functionality and API calls
2. **useProductSearch Hook**: Manage search state and API interactions
3. **PriceFormatter Component**: Handle price display and formatting
4. **MockProductCard Component**: Separate placeholder display logic

This would reduce the main `edit.js` file size and improve maintainability while following the same patterns established in the collection block.
