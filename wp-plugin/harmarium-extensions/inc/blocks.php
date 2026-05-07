<?php
/**
 * Register all Harmarium Extension blocks and shared front-end assets.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function (): void {
	$blocks_dir = HARMARIUM_EXT_DIR . 'blocks/';
	foreach ( [ 'artwork-meta', 'commission-form', 'qr-code', 'artwork-spotlight', 'gallery-hallway' ] as $block ) {
		register_block_type( $blocks_dir . $block );
	}
} );

/* ── Shared block stylesheet ── */
add_action( 'enqueue_block_assets', function (): void {
	wp_enqueue_style(
		'harmarium-ext-blocks',
		HARMARIUM_EXT_URI . 'assets/css/blocks.css',
		[],
		HARMARIUM_EXT_VERSION
	);
} );

/* ── Admin assets (QR preview, commission columns) ── */
add_action( 'admin_enqueue_scripts', function ( string $hook ): void {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php', 'edit.php' ], true ) ) return;
	wp_enqueue_style(  'harmarium-ext-admin', HARMARIUM_EXT_URI . 'assets/css/admin.css', [], HARMARIUM_EXT_VERSION );
	wp_enqueue_script( 'harmarium-ext-admin', HARMARIUM_EXT_URI . 'assets/js/admin.js',  [ 'jquery' ], HARMARIUM_EXT_VERSION, true );
	wp_localize_script( 'harmarium-ext-admin', 'HarmariumExt', [
		'restUrl' => esc_url_raw( rest_url( 'harmarium/v1/' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
	] );
} );

/* ── Commission form: localise nonce for front-end ── */
add_action( 'wp_enqueue_scripts', function (): void {
	if ( ! wp_script_is( 'harmarium-ext-commission-view', 'registered' ) ) return;
	wp_localize_script( 'harmarium-ext-commission-view', 'HarmariumCommission', [
		'restUrl' => esc_url_raw( rest_url( 'harmarium/v1/commission' ) ),
		'nonce'   => wp_create_nonce( 'harmarium_commission' ),
	] );
} );
