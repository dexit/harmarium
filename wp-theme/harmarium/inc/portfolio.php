<?php
/**
 * Portfolio CPT support.
 *
 * The live Harmarium site already exposes /wp/v2/portfolio, so we register the
 * post type only as a fallback (idempotent) and extend the REST response with
 * the gallery-specific ACF fields needed by the exhibition wall and filters.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the portfolio CPT and its taxonomies if no other plugin has.
 */
function harmarium_register_portfolio() {
	if ( ! post_type_exists( 'portfolio' ) ) {
		register_post_type( 'portfolio', array(
			'labels'        => array(
				'name'          => __( 'Portfolio', 'harmarium' ),
				'singular_name' => __( 'Artwork', 'harmarium' ),
				'add_new_item'  => __( 'Add Artwork', 'harmarium' ),
				'edit_item'     => __( 'Edit Artwork', 'harmarium' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-art',
			'show_in_rest'  => true,
			'rest_base'     => 'portfolio',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			'rewrite'       => array( 'slug' => 'portfolio', 'with_front' => false ),
		) );
	}

	$shared = array(
		'public'       => true,
		'show_in_rest' => true,
		'hierarchical' => false,
	);

	if ( ! taxonomy_exists( 'artwork_type' ) ) {
		register_taxonomy( 'artwork_type', array( 'portfolio' ), array_merge( $shared, array(
			'label'   => __( 'Artwork Type', 'harmarium' ),
			'rewrite' => array( 'slug' => 'artwork-type' ),
		) ) );
	}
	if ( ! taxonomy_exists( 'portfolio_category' ) ) {
		register_taxonomy( 'portfolio_category', array( 'portfolio' ), array_merge( $shared, array(
			'hierarchical' => true,
			'label'        => __( 'Portfolio Category', 'harmarium' ),
			'rewrite'      => array( 'slug' => 'portfolio-category' ),
		) ) );
	}
	if ( ! taxonomy_exists( 'portfolio_tag' ) ) {
		register_taxonomy( 'portfolio_tag', array( 'portfolio' ), array_merge( $shared, array(
			'label'   => __( 'Portfolio Tag', 'harmarium' ),
			'rewrite' => array( 'slug' => 'portfolio-tag' ),
		) ) );
	}
}
add_action( 'init', 'harmarium_register_portfolio', 5 );

/**
 * Expose a thin, view-friendly summary on portfolio REST responses.
 */
function harmarium_register_portfolio_rest_fields() {
	register_rest_field( 'portfolio', 'harmarium', array(
		'get_callback' => function ( $object ) {
			$id          = (int) $object['id'];
			$thumb       = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'harmarium-square' );
			$full        = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'full' );
			$mediums     = wp_get_post_terms( $id, 'artwork_type', array( 'fields' => 'names' ) );
			$categories  = wp_get_post_terms( $id, 'portfolio_category', array( 'fields' => 'names' ) );
			$wall_x      = function_exists( 'get_field' ) ? get_field( 'exhibition_wall_x_axis', $id )    : get_post_meta( $id, 'exhibition_wall_x_axis', true );
			$wall_y      = function_exists( 'get_field' ) ? get_field( 'exhibition_wall_y_axis', $id )    : get_post_meta( $id, 'exhibition_wall_y_axis', true );
			$wall_nr     = function_exists( 'get_field' ) ? get_field( 'exhibition_wall_nr', $id )        : get_post_meta( $id, 'exhibition_wall_nr', true );

			return array(
				'thumb'      => $thumb ? array( 'url' => $thumb[0], 'w' => $thumb[1], 'h' => $thumb[2] ) : null,
				'full'       => $full  ? array( 'url' => $full[0],  'w' => $full[1],  'h' => $full[2] )  : null,
				'mediums'    => is_array( $mediums ) ? $mediums : array(),
				'categories' => is_array( $categories ) ? $categories : array(),
				'wall'       => array(
					'nr' => $wall_nr ? (string) $wall_nr : '',
					'x'  => is_numeric( $wall_x ) ? (float) $wall_x : null,
					'y'  => is_numeric( $wall_y ) ? (float) $wall_y : null,
				),
				'permalink'  => get_permalink( $id ),
			);
		},
		'schema' => array(
			'description' => __( 'Harmarium gallery summary.', 'harmarium' ),
			'type'        => 'object',
			'context'     => array( 'view', 'edit' ),
		),
	) );
}
add_action( 'rest_api_init', 'harmarium_register_portfolio_rest_fields' );

/**
 * Allow taxonomy filtering directly on the portfolio collection endpoint.
 */
function harmarium_portfolio_rest_query( $args, $request ) {
	$tax_map = array(
		'artwork_type'       => 'artwork_type',
		'portfolio_category' => 'portfolio_category',
		'portfolio_tag'      => 'portfolio_tag',
	);
	$tax_query = array();
	foreach ( $tax_map as $param => $taxonomy ) {
		$value = $request->get_param( $param );
		if ( ! empty( $value ) ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => is_numeric( $value ) ? 'term_id' : 'slug',
				'terms'    => is_array( $value ) ? $value : array_map( 'trim', explode( ',', (string) $value ) ),
			);
		}
	}
	if ( $tax_query ) {
		$args['tax_query'] = count( $tax_query ) > 1 ? array_merge( array( 'relation' => 'AND' ), $tax_query ) : $tax_query;
	}
	return $args;
}
add_filter( 'rest_portfolio_query', 'harmarium_portfolio_rest_query', 10, 2 );
