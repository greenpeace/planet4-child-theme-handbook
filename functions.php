<?php

/**
 * Additional code for the child theme goes in here.
 */

add_action( 'wp_enqueue_scripts', 'enqueue_child_styles', 99);
add_filter( 'wp_kses_allowed_html', 'set_custom_allowed_attributes_filter_handbook');
add_action( 'admin_bar_menu', 'customize_visit_site', 80 );

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

const BLOCK_ALLOWLIST = [
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

const BLOCK_DENYLIST = [
	'post' => [
		// E.g.: 'planet4-blocks/gallery' or 'core/quote'.
	],
	'page' => [],
];

/**
 * Allowed block types based on post type
 *
 * @param array|bool $allowed_block_types array of allowed block types.
 * @param object $context Current editor context.
 *
 * @return array Array with allowed types.
 */
function set_child_theme_allowed_block_types( $allowed_block_types, $context ) {
	$post_type = $context->post ? $context->post->post_type : null;
	if ( ! $post_type ) {
		return array_merge( $allowed_block_types, BLOCK_ALLOWLIST['page'] );
	}

	if ( ! empty( BLOCK_ALLOWLIST[ $post_type ] )) {
		$allowed_block_types = array_merge( $allowed_block_types, BLOCK_ALLOWLIST[$post_type] );
	}

	if ( ! empty( BLOCK_DENYLIST[ $post_type ] )) {
		$allowed_block_types = array_filter( $allowed_block_types, function ( $element ) use ( $post_type ) {
			return !in_array( $element, BLOCK_DENYLIST[ $post_type ] );
		} );
	}

	// array_values is required as array_filter removes indexes from the array.
	return array_values($allowed_block_types);
}

function customize_visit_site( $wp_admin_bar ) {

    // Get a reference to the view-site node to modify.
    $node = $wp_admin_bar->get_node('view-site');
    if (!$node) {
        return;
    }

	// Update "visit site" link.
	$node->href = home_url() . '/handbook/';
    $wp_admin_bar->add_node($node);
}

add_filter( 'allowed_block_types_all', 'set_child_theme_allowed_block_types', 15, 2 );

//Add Idea push signup/login banner on idea details page for non logged in visitors.
function idea_push_add_login_banner_after_title( $content ) {
	if ( is_single() && 'idea' === get_post_type() && ! is_user_logged_in() ) {
		$custom_content  = '<p class="ideapush-has-yellow-background-color has-text-color has-background notice-banner">';
		$custom_content .= 'Please <a href="https://planet4.greenpeace.org/wp-admin/" class="share-btn">Log in</a> to the handbook to vote or submit new ideas';
		$custom_content .= '<br />';
		$custom_content .= 'Don\'t have a Handbook account? Just send us an <a href="mailto:?to=planet4-pm-group@greenpeace.org" target="_blank">email</a>';
		$custom_content .= '</p>';

		$custom_content .= $content;

		return $custom_content;
	}

	return $content;
}

add_filter( 'the_content', 'idea_push_add_login_banner_after_title', 9, 1);

add_action( 'admin_menu', 'create_admin_menu' );

/**
 * Create menu entry.
 */
function create_admin_menu() {
	$current_user = wp_get_current_user();

	if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {
		add_menu_page(
			'Add an announcement',
			'Announcements',
			'manage_options',
			'p4announcements',
			'planet4_render_announcements_page',
			'dashicons-megaphone'
		);
	}
}

/**
 * Render Announcements admin page
 */
function planet4_render_announcements_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Save on submit
	if ( isset($_POST['planet4_announcements_nonce']) &&
		wp_verify_nonce($_POST['planet4_announcements_nonce'], 'save_announcements') ) {

		update_option(
			'planet4_announcements_content',
			wp_kses_post( $_POST['planet4_announcements_content'] )
		);

		echo '<div class="updated"><p>Announcements updated.</p></div>';
	}

	$content = get_option( 'planet4_announcements_content', '' );
	?>

	<div class="wrap">
		<h1>Announcements</h1>

		<form method="post">
			<?php wp_nonce_field( 'save_announcements', 'planet4_announcements_nonce' ); ?>

			<?php
			wp_editor(
				$content,
				'planet4_announcements_content',
				[
					'textarea_name' => 'planet4_announcements_content',
					'media_buttons' => false,
					'teeny'         => false,
					'textarea_rows' => 10,
				]
			);
			?>

			<p>
				<input type="submit" class="button button-primary" value="Save Announcements">
			</p>
		</form>
	</div>
	<?php
}

/**
 * Register Announcements REST API endpoint
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'planet4/v1', '/announcements', [
		'methods'             => 'GET',
		'callback'            => 'planet4_get_announcements',
		'permission_callback' => '__return_true', // public endpoint
	]);
});

/**
 * Return announcements content
 */
function planet4_get_announcements() {
	return [
		'content' => apply_filters(
			'the_content',
			get_option( 'planet4_announcements_content', '' )
		),
		'last_updated' => get_option( 'planet4_announcements_last_updated' ),
	];
}
