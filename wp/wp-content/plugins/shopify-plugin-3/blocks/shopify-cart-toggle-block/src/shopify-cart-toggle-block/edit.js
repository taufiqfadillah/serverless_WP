import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, TextControl, ToggleControl } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import "./editor.scss";

export default function Edit({ attributes, setAttributes }) {
	const { toggleText, enableCartIcon } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__("Cart Toggle Settings", "shopify")}>
					<TextControl
						label={__("Toggle Text", "shopify")}
						value={toggleText}
						onChange={(value) => setAttributes({ toggleText: value })}
						help={__("Enter the text for the cart toggle button.", "shopify")}
					/>
					<ToggleControl
						label={__("Show Cart Icon", "shopify-cart-toggle-block")}
						checked={enableCartIcon}
						onChange={(value) => setAttributes({ enableCartIcon: value })}
						help={__("Toggle to show or hide the cart icon.", "shopify")}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<button
					className={`shopify-cart-toggle ${
						enableCartIcon ? "shopify-cart-toggle--icon" : ""
					}`}
				>
					{toggleText || __("View Cart", "shopify")}

					{enableCartIcon && <span>1</span>}
				</button>
			</div>
		</>
	);
}
