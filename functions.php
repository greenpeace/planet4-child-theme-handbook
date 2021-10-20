<?php

/**
 * Additional code for the child theme goes in here.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99);
add_filter( 'wp_kses_allowed_html', 'set_custom_allowed_attributes_filter_handbook');

// From the FAQ of IdeaPush's Support Tab under IdeaPush Settings (inside WP Admin)
add_filter( 'idea_push_change_author_link', 'idea_push_change_author_link_callback', 10, 1 );

/**
 * Change Author links for IdeaPush pages.
 *
 * @param int $userId The user id.
 *
 * @return string
 */
function idea_push_change_author_link_callback( $userId ) {
	return get_author_posts_url($userId);
}

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

	// Allow below tags for carousel slider.
	$allowedposttags['div']['data-render']     = true;
	$allowedposttags['div']['data-attributes'] = true;

	return $allowedposttags;
}

function enqueue_child_styles() {
	$css_creation = filectime(get_stylesheet_directory() . '/style.css');

	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', [], $css_creation );
}

const BLOCK_WHITELIST = [
	'post' => [
		// E.g.: 'gpnl/blockquote'.
		'core/code',
		'core/preformatted',
	],
	'page' => [
		'core/code',
		'core/preformatted',
	],
];

const BLOCK_BLACKLIST = [
	'post' => [
		// E.g.: 'planet4-blocks/gallery' or 'core/quote'.
	],
	'page' => [],
];

function set_child_theme_allowed_block_types( $allowed_block_types, $post ) {
	if ( ! empty( BLOCK_WHITELIST[ $post->post_type ] )) {
		$allowed_block_types = array_merge( $allowed_block_types, BLOCK_WHITELIST[$post->post_type] );
	}

	if ( ! empty( BLOCK_BLACKLIST[ $post->post_type ] )) {
		$allowed_block_types = array_filter( $allowed_block_types, function ( $element ) use ( $post ) {
			return !in_array( $element, BLOCK_BLACKLIST[ $post->post_type ] );
		} );
	}

	// array_values is required as array_filter removes indexes from the array.
	return array_values($allowed_block_types);
}

add_filter( 'allowed_block_types', 'set_child_theme_allowed_block_types', 15, 2 );

/**
 * Notice banner for production updates on each admin page.
 *
 * @return string
 */
function general_admin_notice(){
	echo '<div class="notice notice-warning is-dismissible">
		<p>The P4 team is currently running some changes to the site. Please be aware that you might be redirected to some other sources on the world wide web or you will find the content you are looking for in another format. If you notice anything missing, please let us know in the <b><a href="https://app.slack.com/client/T013PTEK4AV/C014UMRC4AJ" target="_blank">p4-general</a></b> Slack channel. Thank you!</p>
	</div>';
}

add_action('admin_notices', 'general_admin_notice');
