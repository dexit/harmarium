<?php
/**
 * Template helpers and body classes.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function harmarium_body_class( $classes ) {
	if ( is_singular( 'portfolio' ) ) {
		$classes[] = 'is-artwork';
	}
	if ( is_post_type_archive( 'portfolio' ) || is_tax( array( 'artwork_type', 'portfolio_category', 'portfolio_tag' ) ) ) {
		$classes[] = 'is-gallery';
	}
	if ( function_exists( 'is_woocommerce' ) && ( is_shop() || is_product() || is_product_taxonomy() ) ) {
		$classes[] = 'is-shop';
	}
	return $classes;
}
add_filter( 'body_class', 'harmarium_body_class' );

/**
 * Pingback header on singular pages.
 */
function harmarium_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'harmarium_pingback_header' );
