<?php
/**
 * Custom block styles.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function harmarium_register_block_styles() {
	register_block_style( 'core/image', array(
		'name'  => 'harmarium-frame',
		'label' => __( 'Mat & Frame', 'harmarium' ),
	) );
	register_block_style( 'core/image', array(
		'name'  => 'harmarium-zoom',
		'label' => __( 'Zoom on hover', 'harmarium' ),
	) );
	register_block_style( 'core/image', array(
		'name'  => 'harmarium-tilt',
		'label' => __( 'Subtle tilt', 'harmarium' ),
	) );
	register_block_style( 'core/cover', array(
		'name'  => 'harmarium-grain',
		'label' => __( 'Canvas grain', 'harmarium' ),
	) );
	register_block_style( 'core/heading', array(
		'name'  => 'harmarium-eyebrow',
		'label' => __( 'Eyebrow', 'harmarium' ),
	) );
	register_block_style( 'core/group', array(
		'name'  => 'harmarium-card',
		'label' => __( 'Card', 'harmarium' ),
	) );
	register_block_style( 'core/button', array(
		'name'  => 'harmarium-ghost',
		'label' => __( 'Ghost', 'harmarium' ),
	) );
	register_block_style( 'core/separator', array(
		'name'  => 'harmarium-brushstroke',
		'label' => __( 'Brushstroke', 'harmarium' ),
	) );
	register_block_style( 'core/post-template', array(
		'name'  => 'harmarium-masonry',
		'label' => __( 'Masonry', 'harmarium' ),
	) );
}
add_action( 'init', 'harmarium_register_block_styles' );
