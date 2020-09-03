<?php
/**
 * Template Name: Sitemap Page
 * The template for displaying the Sitemap page.
 *
 * @package P4MT
 */

use P4\MasterTheme\Sitemap;
use P4\MasterTheme\Post;
use Timber\Timber;

$context        = Timber::get_context();
$post           = new Post(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$sitemap        = new Sitemap();
$page_meta_data = get_post_meta( $post->ID );

$context['post']                = $post;
$context['header_title']        = is_front_page() ? '' : ( $page_meta_data['p4_title'][0] ?? $post->title );
$context['background_image']    = wp_get_attachment_url( get_post_meta( get_the_ID(), 'background_image_id', 1 ) );
$context['custom_body_classes'] = 'white-bg';

$context['actions_title']    = __( 'Actions', 'planet4-child-theme-handbook' );
$context['issues_title']     = __( 'Implement', 'planet4-child-theme-handbook' );
$context['evergreen_title']  = __( 'About Greenpeace', 'planet4-child-theme-handbook' );

$context['actions']         = $sitemap->get_actions();
$context['issues']          = $sitemap->get_issues();
$context['evergreen_pages'] = $sitemap->get_evergreen_pages();
$context['page_category']   = 'Sitemap Page';

Timber::render( [ 'sitemap.twig' ], $context );