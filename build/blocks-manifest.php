<?php
// This file is generated. Do not modify it manually.
return array(
	'dynamic-hero-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/dynamic-hero-block',
		'version' => '0.1.0',
		'title' => 'Dynamic Hero Block',
		'category' => 'design',
		'icon' => 'smiley',
		'description' => 'Displays a page hero section with the background based on the type of page being renderer. For posts, it will show the featured image. For regular pages, it will diplay the background image set on the individual page. Recommended inner blocks are Dynamic Hero Title and Dynamic Hero Text.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'spacing' => array(
				'padding' => false,
				'margin' => true
			)
		),
		'textdomain' => 'dynamic-page-hero-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'style' => array(
				'type' => 'object',
				'default' => array(
					'dimensions' => array(
						'minHeight' => '400px'
					)
				)
			),
			'widthNum' => array(
				'type' => 'number',
				'default' => 1250
			),
			'widthUnit' => array(
				'type' => 'string',
				'default' => 'px'
			),
			'gradient' => array(
				'type' => 'string',
				'default' => 'linear-gradient(270deg,rgba(237,237,237,0.31) 0%,rgb(255,255,255) 100%)'
			),
			'mediaId' => array(
				'type' => 'number',
				'default' => 0
			),
			'mediaUrl' => array(
				'type' => 'string',
				'default' => ''
			)
		)
	),
	'dynamic-hero-text-el' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/dynamic-hero-text-el',
		'version' => '0.1.0',
		'title' => 'Dynamic Hero Text Element',
		'category' => 'widgets',
		'icon' => 'smiley',
		'description' => 'Displays the page subtext, post excerpt, or term name based on the type of page being rendered.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'dimensions' => array(
				'height' => false,
				'margin' => true
			),
			'color' => array(
				'text' => true,
				'background' => false,
				'gradients' => false,
				'link' => true
			),
			'spacing' => array(
				'margin' => false,
				'padding' => false,
				'blockGap' => false
			),
			'typography' => array(
				'fontFamily' => true,
				'fontSize' => true,
				'lineHeight' => true,
				'letterSpacing' => true,
				'fontWeight' => true,
				'fontStyle' => true,
				'textTransform' => true,
				'textDecoration' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontStyle' => true,
				'__experimentalFontWeight' => true,
				'__experimentalLetterSpacing' => true,
				'__experimentalTextTransform' => true
			)
		),
		'textdomain' => 'dynamic-page-hero-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'alignment' => array(
				'type' => 'string',
				'default' => 'left'
			)
		)
	),
	'dynamic-hero-title-el' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'create-block/dynamic-hero-title-el',
		'version' => '0.1.0',
		'title' => 'Dynamic Hero Title Element',
		'category' => 'widgets',
		'icon' => 'smiley',
		'description' => 'Displays the page title, post title, term name, or post type name,  based on the type of page being rendered.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'dimensions' => array(
				'height' => false,
				'margin' => true
			),
			'color' => array(
				'text' => true,
				'background' => false,
				'gradients' => false,
				'link' => true
			),
			'spacing' => array(
				'margin' => false,
				'padding' => false,
				'blockGap' => false
			),
			'typography' => array(
				'fontFamily' => true,
				'fontSize' => true,
				'lineHeight' => true,
				'letterSpacing' => true,
				'fontWeight' => true,
				'fontStyle' => true,
				'textTransform' => true,
				'textDecoration' => true,
				'__experimentalFontFamily' => true,
				'__experimentalFontStyle' => true,
				'__experimentalFontWeight' => true,
				'__experimentalLetterSpacing' => true,
				'__experimentalTextTransform' => true
			)
		),
		'textdomain' => 'dynamic-page-hero-block',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js',
		'attributes' => array(
			'alignment' => array(
				'type' => 'string',
				'default' => 'left'
			)
		)
	)
);
