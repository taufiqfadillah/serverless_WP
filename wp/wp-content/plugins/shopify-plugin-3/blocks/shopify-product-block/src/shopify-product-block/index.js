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
import "./editor.scss";

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
					d="M18.3184 3.84106C18.5117 3.84111 18.6976 3.91443 18.835 4.04614L18.9268 4.15356C19.0065 4.26839 19.0498 4.40465 19.0498 4.54517V11.3704C19.0498 11.5574 18.9716 11.7336 18.8369 11.8645L18.8379 11.8655L17.3232 13.6086L17.292 13.6448L17.2539 13.6145L16.1943 12.7717L16.1533 12.7395L16.1875 12.6995L17.5859 11.0803V5.24927H11.5078L5.61426 10.9055C5.48599 11.0294 5.41429 11.1966 5.41406 11.3704L5.42676 11.4983C5.45266 11.6242 5.51659 11.7412 5.6123 11.8342L11.2402 17.1799L11.2754 17.2141L11.2432 17.2502L10.3311 18.2698L10.2969 18.3079L10.2598 18.2727L9.67871 17.7258L9.67773 17.7249L4.57812 12.8313L4.54102 12.7952L4.54297 12.7922C4.16407 12.4086 3.9502 11.901 3.9502 11.3704C3.9502 10.8232 4.17581 10.2988 4.57715 9.91138L10.6895 4.04712L10.8008 3.95923C10.9199 3.88295 11.0602 3.84126 11.2051 3.84106H18.3184Z"
					fill="#1E1E1E"
					stroke="black"
					stroke-width="0.1"
				/>
				<path
					d="M14.6808 8.90541C14.4397 8.90541 14.2084 8.81353 14.0379 8.64997C13.8675 8.48641 13.7717 8.26458 13.7717 8.03327C13.7717 7.80197 13.8675 7.58013 14.0379 7.41658C14.2084 7.25302 14.4397 7.16113 14.6808 7.16113C14.9219 7.16113 15.1531 7.25302 15.3236 7.41658C15.4941 7.58013 15.5898 7.80197 15.5898 8.03327C15.5898 8.26458 15.4941 8.48641 15.3236 8.64997C15.1531 8.81353 14.9219 8.90541 14.6808 8.90541Z"
					fill="#1E1E1E"
				/>
				<path
					d="M16.125 13.991V16.3464H18.5342V17.7097H16.125V20.0144H14.7197V17.7097H12.2598V16.3464H14.7197V13.991H16.125Z"
					fill="#1E1E1E"
					stroke="#1E1E1E"
					stroke-width="0.2"
				/>
			</svg>
		),
	},

	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
	 */
	save: () => null,

	// Attributes for wordpress to save
	attributes: {
		selectedProduct: {
			type: "object",
			default: null,
		},
		cardBehavior: {
			type: "string",
			default: "both",
		},
		selectedStoreUrl: {
			type: "string",
			default: "",
		},
	},
});
