<?php
/**
 * Editor compatibility: Gutenberg + Elementor.
 *
 * - Provides Elementor theme-locations support so Elementor Pro can override
 *   header/footer/single/archive while still falling back to the FSE templates.
 * - Disables theme.json layout fights with Elementor when an Elementor-built
 *   page is being rendered (Elementor handles its own container widths).
 * - Adds the `harmarium-content` class wrapper used by both editors.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---------- Gutenberg ---------- */

// Add a body class to scope editor-specific CSS, and respect theme.json typography in classic editor textarea.
add_filter( 'admin_body_class', function ( $classes ) {
	if ( get_current_screen() && get_current_screen()->is_block_editor() ) {
		$classes .= ' harmarium-block-editor';
	}
	return $classes;
} );

// Allow contained block themes to use full-width inside Elementor sections.
add_theme_support( 'experimental-link-color' );

/* ---------- Elementor ---------- */

add_action( 'elementor/theme/register_locations', function ( $manager ) {
	if ( ! is_object( $manager ) ) {
		return;
	}
	$manager->register_all_core_location();
} );

/**
 * When Elementor is editing or rendering a page built with Elementor, drop our
 * theme.json root padding so Elementor sections can go edge-to-edge.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	$post_id = get_queried_object_id();
	if ( $post_id && class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents->get( $post_id ) && \Elementor\Plugin::$instance->documents->get( $post_id )->is_built_with_elementor() ) {
		wp_add_inline_style( 'harmarium-main', '.is-elementor-page :where(body){--wp--style--root--padding-left:0;--wp--style--root--padding-right:0}' );
		add_filter( 'body_class', function ( $c ) { $c[] = 'is-elementor-page'; return $c; } );
	}
}, 30 );

/**
 * Provide Elementor with theme color & typography presets sourced from theme.json.
 */
add_filter( 'elementor/frontend/print_google_fonts', function ( $print ) {
	// Theme already provides Inter + Cormorant locally; let Elementor skip its Google Fonts duplication.
	return false;
} );

/**
 * Elementor canvas/full-width compatibility — hide site header/footer for those page templates,
 * which Elementor's loader expects.
 */
add_filter( 'theme_page_templates', function ( $templates ) {
	$templates['elementor_canvas']      = __( 'Elementor Canvas', 'harmarium' );
	$templates['elementor_header_footer'] = __( 'Elementor Full Width', 'harmarium' );
	return $templates;
} );

/**
 * Allow Gutenberg + Elementor on the same page: don't fight the editor swap.
 */
add_filter( 'use_block_editor_for_post_type', function ( $use, $post_type ) {
	return $use; // pass through; Elementor's own filter decides per-post.
}, 10, 2 );

/**
 * Classic Editor / TinyMCE inside Elementor widgets — pull our editor stylesheet so
 * typography & colours match the front end.
 */
add_filter( 'tiny_mce_before_init', function ( $settings ) {
	$settings['content_css'] = HARMARIUM_URI . '/assets/css/editor.css';
	return $settings;
} );

/**
 * Make sure Elementor archive/single template overrides still receive our
 * structured data, body classes and Woo mockup hooks.
 */
add_action( 'elementor/theme/before_do_single', function () {
	do_action( 'harmarium_before_single' );
} );
