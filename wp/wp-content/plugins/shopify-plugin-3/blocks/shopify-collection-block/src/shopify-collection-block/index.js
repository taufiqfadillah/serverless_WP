/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from "@wordpress/blocks";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import "./style.scss";

/**
 * Internal dependencies
 */
import Edit from "./edit";
import metadata from "./block.json";

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType(metadata.name, {
	icon: {
		src: (
			<svg
				width="24"
				height="24"
				viewBox="0 0 24 24"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
			>
				<path
					d="M21.3008 3.9502C21.4941 3.95024 21.68 4.02356 21.8174 4.15527L21.9092 4.2627C21.9889 4.37752 22.0322 4.51378 22.0322 4.6543V11.4795C22.0322 11.6665 21.9541 11.8427 21.8193 11.9736L21.8203 11.9746L20.3057 13.7178L20.2744 13.7539L20.2363 13.7236L19.1768 12.8809L19.1357 12.8486L19.1699 12.8086L20.5684 11.1895V5.3584H14.4902L8.59668 11.0146C8.46841 11.1386 8.39672 11.3058 8.39648 11.4795L8.40918 11.6074C8.43508 11.7333 8.49901 11.8504 8.59473 11.9434L14.2227 17.2891L14.2578 17.3232L14.2256 17.3594L13.3135 18.3789L13.2793 18.417L13.2422 18.3818L12.6611 17.835L12.6602 17.834L7.56055 12.9404L7.52344 12.9043L7.52539 12.9014C7.14649 12.5177 6.93262 12.0101 6.93262 11.4795C6.93262 10.9323 7.15823 10.408 7.55957 10.0205L13.6719 4.15625L13.7832 4.06836C13.9023 3.99208 14.0426 3.95039 14.1875 3.9502H21.3008Z"
					fill="#1E1E1E"
					stroke="black"
					stroke-width="0.1"
				/>
				<path
					d="M17.6632 9.01454C17.4221 9.01454 17.1908 8.92266 17.0204 8.7591C16.8499 8.59554 16.7541 8.37371 16.7541 8.1424C16.7541 7.9111 16.8499 7.68927 17.0204 7.52571C17.1908 7.36215 17.4221 7.27026 17.6632 7.27026C17.9043 7.27026 18.1355 7.36215 18.306 7.52571C18.4765 7.68927 18.5723 7.9111 18.5723 8.1424C18.5723 8.37371 18.4765 8.59554 18.306 8.7591C18.1355 8.92266 17.9043 9.01454 17.6632 9.01454Z"
					fill="#1E1E1E"
				/>
				<path
					d="M19.1074 14.1001V16.4556H21.5166V17.8188H19.1074V20.1235H17.7021V17.8188H15.2422V16.4556H17.7021V14.1001H19.1074Z"
					fill="#1E1E1E"
					stroke="#1E1E1E"
					stroke-width="0.2"
				/>
				<path
					d="M3.525 11.3L9.825 5L8.825 4L2.525 10.3C1.825 11 1.825 12.1 2.525 12.8L8.825 19L9.925 17.9L3.625 11.6C3.425 11.6 3.425 11.4 3.525 11.3Z"
					fill="#1E1E1E"
				/>
			</svg>
		),
	},

	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	save: () => null,
	// Attributes for wordpress to save
	attributes: {
		selectedCollection: {
			type: "object",
			default: null,
		},
		selectedStoreUrl: {
			type: "string",
			default: "",
		},
		maxProductsPerRow: {
			type: "number",
			default: 3,
		},
		maxProductsPerPage: {
			type: "number",
			default: 12,
		},
		showPagination: {
			type: "boolean",
			default: true,
		},
	},
});
