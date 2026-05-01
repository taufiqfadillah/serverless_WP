# Shopify Cart Toggle Block

This directory contains the Shopify Cart Toggle Block, a simple and focused block for adding cart functionality to WordPress navigation.

## Block Overview

The cart toggle block provides a button that opens the Shopify cart modal when clicked. It's designed to be used within WordPress navigation blocks and integrates with Shopify's cart system.

## File Structure

```
shopify-cart-toggle-block/
├── edit.js (50 lines)           # Editor component
├── view.js (24 lines)           # Frontend JavaScript
├── render.php (25 lines)        # Server-side rendering
├── block.json (45 lines)        # Block configuration
├── index.js (39 lines)          # Block registration
├── style.scss (42 lines)        # Frontend styles
├── editor.scss (22 lines)       # Editor styles
└── README.md                    # This file
```

## Core Components

### `edit.js`

The editor component that provides the block interface in the WordPress editor.

- **Features:**

  - Inspector controls for customizing the cart toggle
  - Text control for customizing toggle text
  - Toggle control for showing/hiding cart icon
  - Live preview of the cart toggle button

- **Attributes:**
  - `toggleText`: Custom text for the cart toggle button
  - `enableCartIcon`: Boolean to show/hide cart quantity icon

### `render.php`

Server-side rendering component that generates the HTML for the frontend.

- **Features:**

  - Renders the cart toggle button with proper attributes
  - Integrates with Shopify cart context and data queries
  - Handles cart quantity display
  - Provides click handler to open cart modal

- **Shopify Integration:**
  - Uses `<shopify-context type="cart">` for cart data
  - Uses `<shopify-data query="cart.totalQuantity">` for quantity display
  - Triggers `shopify-cart.showModal()` on click

### `block.json`

Block configuration and metadata.

- **Key Features:**
  - Parent block: `core/navigation` (can only be used in navigation)
  - Supports spacing controls (margin, padding, block gap)
  - Supports client navigation interactivity
  - Default attributes for toggle text and cart icon

## Block Attributes

| Attribute        | Type    | Default     | Description                            |
| ---------------- | ------- | ----------- | -------------------------------------- |
| `toggleText`     | string  | "View Cart" | Custom text for the cart toggle button |
| `enableCartIcon` | boolean | true        | Whether to show the cart quantity icon |

## Usage

### In WordPress Editor

1. Add the block to a navigation menu
2. Customize the toggle text in the block settings
3. Toggle the cart icon on/off as needed

### Frontend Behavior

- Clicking the cart toggle opens the Shopify cart modal
- Cart quantity is displayed when icon is enabled
- Integrates seamlessly with Shopify's cart system

## Styling

### CSS Classes

- `.shopify-cart-toggle`: Main cart toggle button
- `.shopify-cart-toggle--icon`: Applied when cart icon is enabled
- `.navigation-cart-block`: Container wrapper
- `.navigation__view-cart`: Cart toggle content wrapper
- `.navigation__view-cart__quantity`: Cart quantity display
- `.no-icon`: Applied when cart icon is disabled

### Style Files

- `style.scss`: Frontend styles for the cart toggle
- `editor.scss`: Editor-specific styles

## Integration Points

### Shopify Integration

- **Cart Context**: Uses Shopify's cart context for real-time data
- **Cart Modal**: Integrates with Shopify's cart modal system
- **Quantity Display**: Shows live cart quantity updates

### WordPress Integration

- **Navigation Block**: Designed as a child of navigation blocks
- **Block Editor**: Full integration with WordPress block editor
- **Server-Side Rendering**: PHP-based rendering for performance

## Benefits

1. **Simplicity**: Focused, single-purpose block with minimal complexity
2. **Integration**: Seamless integration with both WordPress and Shopify
3. **Customization**: Easy to customize text and icon display
4. **Performance**: Server-side rendering for optimal performance
5. **Accessibility**: Proper semantic HTML and ARIA attributes

## Future Enhancements

Potential improvements for the cart toggle block:

1. **Cart Badge Styling**: More customization options for the cart quantity badge
2. **Animation Support**: Smooth transitions and hover effects
3. **Mobile Optimization**: Enhanced mobile cart toggle experience
4. **Cart Preview**: Mini cart preview on hover
5. **Custom Icons**: Support for custom cart icons

## Development Notes

- The block uses `save: () => null` for dynamic rendering
- All rendering is handled server-side via `render.php`
- Frontend JavaScript (`view.js`) is minimal and focused
- Block is restricted to navigation contexts only
