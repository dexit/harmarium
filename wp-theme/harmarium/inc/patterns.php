<?php
/**
 * Pattern categories.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function harmarium_register_pattern_categories() {
	register_block_pattern_category( 'harmarium-hero',       array( 'label' => __( 'Harmarium · Hero',       'harmarium' ) ) );
	register_block_pattern_category( 'harmarium-gallery',    array( 'label' => __( 'Harmarium · Gallery',    'harmarium' ) ) );
	register_block_pattern_category( 'harmarium-exhibition', array( 'label' => __( 'Harmarium · Exhibition', 'harmarium' ) ) );
	register_block_pattern_category( 'harmarium-shop',       array( 'label' => __( 'Harmarium · Shop',       'harmarium' ) ) );
	register_block_pattern_category( 'harmarium-cta',        array( 'label' => __( 'Harmarium · CTA',        'harmarium' ) ) );
}
add_action( 'init', 'harmarium_register_pattern_categories' );
