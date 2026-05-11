<?php
/**
 * Bidirectional portfolio ↔ WooCommerce product bridge.
 * Lets editors link a portfolio item to a product and vice-versa.
 * Adds admin list columns for linked items.
 */

defined( 'ABSPATH' ) || exit;

/* ── Portfolio: "Linked product" meta box ── */
add_action( 'add_meta_boxes', function (): void {
	add_meta_box( 'hm_linked_product', __( 'Shop Listing', 'harmarium-ext' ), 'harmarium_portfolio_linked_product_box', 'portfolio', 'side', 'default' );
	add_meta_box( 'hm_linked_portfolio', __( 'Portfolio Artwork', 'harmarium-ext' ), 'harmarium_product_linked_portfolio_box', 'product', 'side', 'default' );
} );

function harmarium_portfolio_linked_product_box( WP_Post $post ): void {
	wp_nonce_field( 'hm_linked_product', 'hm_lp_nonce' );
	$linked = (int) get_post_meta( $post->ID, '_hm_linked_product', true );
	$products = get_posts( [ 'post_type' => 'product', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => [ 'publish', 'draft' ] ] );
	echo '<select name="_hm_linked_product" style="width:100%"><option value="0">' . esc_html__( '— none —', 'harmarium-ext' ) . '</option>';
	foreach ( $products as $p ) {
		echo '<option value="' . esc_attr( $p->ID ) . '"' . selected( $linked, $p->ID, false ) . '>' . esc_html( $p->post_title ) . '</option>';
	}
	echo '</select>';
	if ( $linked ) {
		echo '<p style="margin-top:6px"><a href="' . esc_url( get_edit_post_link( $linked ) ) . '" target="_blank">' . esc_html__( 'Edit product →', 'harmarium-ext' ) . '</a></p>';
	}
}

function harmarium_product_linked_portfolio_box( WP_Post $post ): void {
	wp_nonce_field( 'hm_linked_portfolio', 'hm_lpo_nonce' );
	$linked = (int) get_post_meta( $post->ID, '_hm_linked_portfolio', true );
	$items = get_posts( [ 'post_type' => 'portfolio', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => [ 'publish', 'draft' ] ] );
	echo '<select name="_hm_linked_portfolio" style="width:100%"><option value="0">' . esc_html__( '— none —', 'harmarium-ext' ) . '</option>';
	foreach ( $items as $p ) {
		echo '<option value="' . esc_attr( $p->ID ) . '"' . selected( $linked, $p->ID, false ) . '>' . esc_html( $p->post_title ) . '</option>';
	}
	echo '</select>';
	if ( $linked ) {
		echo '<p style="margin-top:6px"><a href="' . esc_url( get_edit_post_link( $linked ) ) . '" target="_blank">' . esc_html__( 'Edit artwork →', 'harmarium-ext' ) . '</a></p>';
	}
}

add_action( 'save_post_portfolio', function ( int $post_id ): void {
	if ( ! isset( $_POST['hm_lp_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hm_lp_nonce'] ), 'hm_linked_product' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$product_id = isset( $_POST['_hm_linked_product'] ) ? absint( $_POST['_hm_linked_product'] ) : 0;
	update_post_meta( $post_id, '_hm_linked_product', $product_id );

	// Keep product pointing back
	if ( $product_id ) {
		update_post_meta( $product_id, '_hm_linked_portfolio', $post_id );
	}
} );

add_action( 'save_post_product', function ( int $post_id ): void {
	if ( ! isset( $_POST['hm_lpo_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hm_lpo_nonce'] ), 'hm_linked_portfolio' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$portfolio_id = isset( $_POST['_hm_linked_portfolio'] ) ? absint( $_POST['_hm_linked_portfolio'] ) : 0;
	update_post_meta( $post_id, '_hm_linked_portfolio', $portfolio_id );

	if ( $portfolio_id ) {
		update_post_meta( $portfolio_id, '_hm_linked_product', $post_id );
	}
} );

/* ── Admin columns ── */
add_filter( 'manage_portfolio_posts_columns', function ( array $cols ): array {
	$cols['hm_linked_product'] = __( 'Shop', 'harmarium-ext' );
	return $cols;
} );
add_action( 'manage_portfolio_posts_custom_column', function ( string $col, int $post_id ): void {
	if ( 'hm_linked_product' !== $col ) return;
	$pid = (int) get_post_meta( $post_id, '_hm_linked_product', true );
	if ( $pid ) {
		echo '<a href="' . esc_url( get_edit_post_link( $pid ) ) . '">' . esc_html( get_the_title( $pid ) ) . '</a>';
	} else {
		echo '<span style="color:#aaa">—</span>';
	}
}, 10, 2 );

add_filter( 'manage_product_posts_columns', function ( array $cols ): array {
	$cols['hm_linked_portfolio'] = __( 'Artwork', 'harmarium-ext' );
	return $cols;
} );
add_action( 'manage_product_posts_custom_column', function ( string $col, int $post_id ): void {
	if ( 'hm_linked_portfolio' !== $col ) return;
	$aid = (int) get_post_meta( $post_id, '_hm_linked_portfolio', true );
	if ( $aid ) {
		echo '<a href="' . esc_url( get_edit_post_link( $aid ) ) . '">' . esc_html( get_the_title( $aid ) ) . '</a>';
	} else {
		echo '<span style="color:#aaa">—</span>';
	}
}, 10, 2 );

/* ── REST: expose linked IDs ── */
add_action( 'rest_api_init', function (): void {
	register_rest_field( 'portfolio', 'linked_product_id', [
		'get_callback' => fn( $obj ) => (int) get_post_meta( $obj['id'], '_hm_linked_product', true ),
		'schema'       => [ 'type' => 'integer' ],
	] );
	register_rest_field( 'product', 'linked_portfolio_id', [
		'get_callback' => fn( $obj ) => (int) get_post_meta( $obj['id'], '_hm_linked_portfolio', true ),
		'schema'       => [ 'type' => 'integer' ],
	] );
} );
