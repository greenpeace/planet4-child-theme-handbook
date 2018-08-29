<?php

/**
 * Additional code for the child theme goes in here.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99);
add_filter( 'wp_kses_allowed_html', 'set_custom_allowed_attributes_filter_handbook');

/**
 * Set attributes that should be allowed for posts filter
 * Adding style to the allowed tags.
 *
 * @param array $allowedposttags Default allowed tags.
 *
 * @return array
 */
function set_custom_allowed_attributes_filter_handbook( $allowedposttags ) {
	// Allow style so that ideaPush works.
	$allowedposttags['style']=[];
	$allowedposttags['div'] = [
		'class'    => true,
		'data'     => true,
		'align'    => true,
		'style'    => true,
	];

	return $allowedposttags;
}

function enqueue_child_styles() {
	$css_creation = filectime(get_stylesheet_directory() . '/style.min.css');

	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.min.css', [], $css_creation );
}
