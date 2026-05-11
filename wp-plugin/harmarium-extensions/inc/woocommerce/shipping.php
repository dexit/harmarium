<?php
/**
 * Custom shipping methods for commission artwork delivery.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'woocommerce_shipping_init', 'harmarium_register_shipping_methods' );
add_filter( 'woocommerce_shipping_methods', 'harmarium_add_shipping_methods' );

function harmarium_register_shipping_methods(): void {
	if ( ! class_exists( 'HM_Shipping_Studio_Collect' ) ) {
		require_once __DIR__ . '/class-shipping-studio-collect.php';
	}
	if ( ! class_exists( 'HM_Shipping_White_Glove' ) ) {
		require_once __DIR__ . '/class-shipping-white-glove.php';
	}
}

function harmarium_add_shipping_methods( array $methods ): array {
	$methods['hm_studio_collect'] = 'HM_Shipping_Studio_Collect';
	$methods['hm_white_glove']    = 'HM_Shipping_White_Glove';
	return $methods;
}
