<?php
// This file is generated. Do not modify it manually.
return array(
	'shopify-product-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'shopify-product/shopify-product-block',
		'version' => '0.1.0',
		'title' => 'Add a Shopify product',
		'category' => 'shopify',
		'icon' => 'payment',
		'description' => 'Add a Shopify product to your page or post.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'shopify',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js',
		'isDynamic' => true,
		'attributes' => array(
			'selectedProduct' => array(
				'type' => 'object',
				'default' => null
			),
			'selectedStoreUrl' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	)
);
