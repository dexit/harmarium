<?php
/**
 * Extended meta for portfolio items and WooCommerce products.
 */

defined( 'ABSPATH' ) || exit;

/* ── Portfolio meta ── */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'harmarium_portfolio_meta',
		__( 'Artwork Details', 'harmarium-ext' ),
		'harmarium_ext_portfolio_meta_box',
		'portfolio',
		'normal',
		'high'
	);
} );

function harmarium_ext_portfolio_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'harmarium_portfolio_meta', 'harmarium_portfolio_nonce' );
	$fields = [
		'_hm_medium'          => [ 'label' => 'Medium',           'type' => 'text' ],
		'_hm_dimensions'      => [ 'label' => 'Dimensions',        'type' => 'text', 'placeholder' => 'e.g. 60 × 80 cm' ],
		'_hm_year'            => [ 'label' => 'Year',              'type' => 'number' ],
		'_hm_edition'         => [ 'label' => 'Edition',           'type' => 'text', 'placeholder' => 'e.g. 1/10 or Original' ],
		'_hm_certificate_nr'  => [ 'label' => 'Certificate Nr.',   'type' => 'text' ],
		'_hm_availability'    => [ 'label' => 'Availability',      'type' => 'select',
			'options' => [ 'available' => 'Available', 'sold' => 'Sold', 'reserved' => 'Reserved', 'nfs' => 'Not for Sale' ] ],
		'_hm_price_display'   => [ 'label' => 'Price (display)',   'type' => 'text', 'placeholder' => 'e.g. €2,400' ],
		'_hm_wall_x'          => [ 'label' => 'Wall X (0–1)',      'type' => 'number', 'step' => '0.01' ],
		'_hm_wall_y'          => [ 'label' => 'Wall Y (0–1)',      'type' => 'number', 'step' => '0.01' ],
		'_hm_wall_scale'      => [ 'label' => 'Wall Scale (0–1)',  'type' => 'number', 'step' => '0.01' ],
	];
	echo '<table class="form-table" style="width:100%">';
	foreach ( $fields as $key => $f ) {
		$val = esc_attr( (string) get_post_meta( $post->ID, $key, true ) );
		echo '<tr><th style="width:160px"><label for="' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';
		if ( $f['type'] === 'select' ) {
			echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
			foreach ( $f['options'] as $opt_val => $opt_label ) {
				echo '<option value="' . esc_attr( $opt_val ) . '"' . selected( $val, $opt_val, false ) . '>' . esc_html( $opt_label ) . '</option>';
			}
			echo '</select>';
		} else {
			$placeholder = isset( $f['placeholder'] ) ? ' placeholder="' . esc_attr( $f['placeholder'] ) . '"' : '';
			$step = isset( $f['step'] ) ? ' step="' . esc_attr( $f['step'] ) . '"' : '';
			echo '<input type="' . esc_attr( $f['type'] ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . $val . '"' . $placeholder . $step . ' class="regular-text">';
		}
		echo '</td></tr>';
	}
	echo '</table>';
}

add_action( 'save_post_portfolio', function ( int $post_id ): void {
	if ( ! isset( $_POST['harmarium_portfolio_nonce'] ) ) return;
	if ( ! wp_verify_nonce( sanitize_key( $_POST['harmarium_portfolio_nonce'] ), 'harmarium_portfolio_meta' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$text_keys   = [ '_hm_medium', '_hm_dimensions', '_hm_edition', '_hm_certificate_nr', '_hm_availability', '_hm_price_display' ];
	$number_keys = [ '_hm_year' ];
	$float_keys  = [ '_hm_wall_x', '_hm_wall_y', '_hm_wall_scale' ];

	foreach ( $text_keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
	}
	foreach ( $number_keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, absint( $_POST[ $k ] ) );
	}
	foreach ( $float_keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) update_post_meta( $post_id, $k, (float) $_POST[ $k ] );
	}
} );

/* ── WooCommerce product extra meta ── */
add_action( 'woocommerce_product_options_general_product_data', function (): void {
	echo '<div class="options_group harmarium-product-meta">';
	echo '<h4 style="padding:0 12px;margin:12px 0 4px">' . esc_html__( 'Artwork Info', 'harmarium-ext' ) . '</h4>';
	woocommerce_wp_text_input( [ 'id' => '_hm_print_type',  'label' => __( 'Print type', 'harmarium-ext' ),  'placeholder' => 'e.g. Giclée on fine-art paper' ] );
	woocommerce_wp_text_input( [ 'id' => '_hm_paper',       'label' => __( 'Paper / substrate', 'harmarium-ext' ) ] );
	woocommerce_wp_text_input( [ 'id' => '_hm_mounting',    'label' => __( 'Mounting', 'harmarium-ext' ),    'placeholder' => 'e.g. Dibond' ] );
	woocommerce_wp_text_input( [ 'id' => '_hm_woo_edition', 'label' => __( 'Edition', 'harmarium-ext' ),     'placeholder' => 'e.g. 3/25' ] );
	echo '</div>';
} );

add_action( 'woocommerce_process_product_meta', function ( int $post_id ): void {
	$keys = [ '_hm_print_type', '_hm_paper', '_hm_mounting', '_hm_woo_edition' ];
	foreach ( $keys as $k ) {
		if ( isset( $_POST[ $k ] ) ) {
			update_post_meta( $post_id, $k, sanitize_text_field( wp_unslash( $_POST[ $k ] ) ) );
		}
	}
} );

/* ── Expose extended meta in portfolio REST response ── */
add_action( 'rest_api_init', function (): void {
	$extra = [ '_hm_medium', '_hm_dimensions', '_hm_year', '_hm_edition', '_hm_certificate_nr', '_hm_availability', '_hm_price_display' ];
	foreach ( $extra as $key ) {
		register_rest_field( 'portfolio', ltrim( $key, '_' ), [
			'get_callback'    => fn( $obj ) => get_post_meta( $obj['id'], $key, true ),
			'update_callback' => null,
			'schema'          => [ 'type' => 'string' ],
		] );
	}
} );
