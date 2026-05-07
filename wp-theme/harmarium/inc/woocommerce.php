<?php
/**
 * WooCommerce integration for the gallery shop.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Declare Woo support and HPOS / cart-checkout-blocks compatibility.
 */
function harmarium_woocommerce_setup() {
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 600,
		'single_image_width'    => 1400,
		'product_grid'          => array(
			'default_rows'    => 3,
			'min_rows'        => 1,
			'default_columns' => 3,
			'min_columns'     => 1,
			'max_columns'     => 6,
		),
	) );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'harmarium_woocommerce_setup' );

/**
 * Declare HPOS + Cart/Checkout block compatibility.
 */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );

/**
 * Strip the default Woo wrappers so block templates own the layout.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content',  'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar',             'woocommerce_get_sidebar', 10 );

add_action( 'woocommerce_before_main_content', function () { echo '<div class="harmarium-woo-content">'; }, 10 );
add_action( 'woocommerce_after_main_content',  function () { echo '</div>'; }, 10 );

/**
 * Gallery-style product loop columns.
 */
add_filter( 'loop_shop_columns', function () { return 3; } );
add_filter( 'loop_shop_per_page', function () { return 12; } );

/**
 * Replace "Add to cart" copy with gallery-friendly verbs.
 */
add_filter( 'woocommerce_product_single_add_to_cart_text', function ( $text, $product = null ) {
	if ( $product && $product->get_price() > 0 ) {
		return __( 'Acquire this piece', 'harmarium' );
	}
	return __( 'Inquire', 'harmarium' );
}, 10, 2 );

add_filter( 'woocommerce_product_add_to_cart_text', function ( $text, $product ) {
	if ( $product->is_type( 'simple' ) && $product->get_price() > 0 ) {
		return __( 'Acquire', 'harmarium' );
	}
	return $text;
}, 10, 2 );

/**
 * Show medium / dimensions / edition meta on the single product page if present.
 */
add_action( 'woocommerce_single_product_summary', function () {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}
	$rows = array(
		'medium'    => __( 'Medium', 'harmarium' ),
		'dimensions'=> __( 'Dimensions', 'harmarium' ),
		'year'      => __( 'Year', 'harmarium' ),
		'edition'   => __( 'Edition', 'harmarium' ),
	);
	$out = '';
	foreach ( $rows as $key => $label ) {
		$value = get_field( $key );
		if ( $value ) {
			$out .= '<div class="harmarium-meta__row"><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $value ) . '</dd></div>';
		}
	}
	if ( $out ) {
		echo '<dl class="harmarium-meta">' . $out . '</dl>';
	}
}, 24 );
