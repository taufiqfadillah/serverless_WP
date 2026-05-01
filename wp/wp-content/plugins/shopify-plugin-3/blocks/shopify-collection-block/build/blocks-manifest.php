<?php
// This file is generated. Do not modify it manually.
return array(
	'shopify-collection-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'shopify-collection/shopify-collection-block',
		'version' => '0.1.0',
		'title' => 'Add a Shopify collection',
		'category' => 'shopify',
		'description' => 'Add a Shopify collection to your page or post.',
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
		'attributes' => array(
			'selectedCollection' => array(
				'type' => 'object',
				'default' => null
			),
			'selectedStoreUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'maxProductsPerRow' => array(
				'type' => 'number',
				'default' => 3
			),
			'maxProductsPerPage' => array(
				'type' => 'number',
				'default' => 12
			),
			'showPagination' => array(
				'type' => 'boolean',
				'default' => true
			)
		)
	)
);
