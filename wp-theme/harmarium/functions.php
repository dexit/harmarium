<?php
/**
 * Harmarium theme bootstrap.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HARMARIUM_VERSION', '1.0.0' );
define( 'HARMARIUM_DIR', get_template_directory() );
define( 'HARMARIUM_URI', get_template_directory_uri() );

/**
 * Theme setup.
 */
function harmarium_setup() {
	load_theme_textdomain( 'harmarium', HARMARIUM_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form', 'script', 'style', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 96,
		'width'       => 320,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	add_editor_style( array( 'assets/css/editor.css' ) );

	// Image sizes tuned for gallery layouts.
	add_image_size( 'harmarium-square',   1200, 1200, true );
	add_image_size( 'harmarium-portrait',  900, 1200, true );
	add_image_size( 'harmarium-wide',     1600,  900, true );
	add_image_size( 'harmarium-thumb',     480,  600, true );

	register_nav_menus( array(
		'primary' => __( 'Primary', 'harmarium' ),
		'footer'  => __( 'Footer', 'harmarium' ),
		'social'  => __( 'Social', 'harmarium' ),
	) );
}
add_action( 'after_setup_theme', 'harmarium_setup' );

/**
 * Front-end assets.
 */
function harmarium_enqueue_assets() {
	wp_enqueue_style(
		'harmarium-main',
		HARMARIUM_URI . '/assets/css/main.css',
		array(),
		HARMARIUM_VERSION
	);

	wp_enqueue_script(
		'harmarium-interactive',
		HARMARIUM_URI . '/assets/js/interactive.js',
		array(),
		HARMARIUM_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => true )
	);

	wp_localize_script( 'harmarium-interactive', 'HarmariumData', array(
		'restUrl'    => esc_url_raw( rest_url( 'wp/v2/' ) ),
		'scenesBase' => esc_url_raw( HARMARIUM_URI . '/assets/images/scenes/' ),
		'nonce'      => wp_create_nonce( 'wp_rest' ),
		'i18n'    => array(
			'close'    => __( 'Close', 'harmarium' ),
			'next'     => __( 'Next', 'harmarium' ),
			'previous' => __( 'Previous', 'harmarium' ),
			'loading'  => __( 'Loading…', 'harmarium' ),
			'all'      => __( 'All', 'harmarium' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'harmarium_enqueue_assets' );

/**
 * Editor assets.
 */
function harmarium_editor_assets() {
	wp_enqueue_script(
		'harmarium-editor',
		HARMARIUM_URI . '/assets/js/editor.js',
		array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' ),
		HARMARIUM_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'harmarium_editor_assets' );

require HARMARIUM_DIR . '/inc/portfolio.php';
require HARMARIUM_DIR . '/inc/exhibition.php';
require HARMARIUM_DIR . '/inc/patterns.php';
require HARMARIUM_DIR . '/inc/block-styles.php';
require HARMARIUM_DIR . '/inc/template-functions.php';
require HARMARIUM_DIR . '/inc/structured-data.php';
require HARMARIUM_DIR . '/inc/compat.php';

if ( class_exists( 'WooCommerce' ) ) {
	require HARMARIUM_DIR . '/inc/woocommerce.php';
	require HARMARIUM_DIR . '/inc/product-mockup.php';
}
