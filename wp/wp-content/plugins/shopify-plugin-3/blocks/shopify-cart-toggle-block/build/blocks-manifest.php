<?php
// This file is generated. Do not modify it manually.
return array(
	'shopify-cart-toggle-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/shopify-cart-toggle-block',
		'version' => '0.1.0',
		'title' => 'Add a Shopify cart toggle',
		'category' => 'shopify',
		'icon' => 'cart',
		'description' => 'Add a Shopify cart toggle to your header.',
		'example' => array(
			
		),
		'parent' => array(
			'core/navigation'
		),
		'allowedBlocks' => array(
			'core/navigation-link',
			'core/navigation-submenu',
			'core/page-list'
		),
		'supports' => array(
			'html' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'blockGap' => true
			),
			'interactivity' => array(
				'clientNavigation' => true
			)
		),
		'attributes' => array(
			'toggleText' => array(
				'type' => 'string',
				'default' => 'View Cart'
			),
			'enableCartIcon' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'textdomain' => 'shopify',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	)
);
