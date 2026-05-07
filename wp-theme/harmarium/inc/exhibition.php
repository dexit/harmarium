<?php
/**
 * Exhibition CPT — a curated set of portfolio items.
 *
 * Each exhibition stores an ordered list of portfolio post IDs in the
 * meta key `_harmarium_exhibition_items`, plus optional layout settings
 * (wall colour, lighting, gallery floor) that drive the front-end view.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function harmarium_register_exhibition() {
	register_post_type( 'exhibition', array(
		'labels'        => array(
			'name'          => __( 'Exhibitions', 'harmarium' ),
			'singular_name' => __( 'Exhibition', 'harmarium' ),
			'add_new_item'  => __( 'Add Exhibition', 'harmarium' ),
		),
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-format-gallery',
		'show_in_rest'  => true,
		'rest_base'     => 'exhibition',
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
		'rewrite'       => array( 'slug' => 'exhibitions', 'with_front' => false ),
	) );

	register_post_meta( 'exhibition', '_harmarium_exhibition_items', array(
		'type'         => 'array',
		'single'       => true,
		'show_in_rest' => array(
			'schema' => array(
				'type'  => 'array',
				'items' => array( 'type' => 'integer' ),
			),
		),
		'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
	) );

	foreach ( array(
		'_harmarium_wall_color'   => array( 'type' => 'string' ),
		'_harmarium_wall_finish'  => array( 'type' => 'string' ),
		'_harmarium_lighting'     => array( 'type' => 'string' ),
		'_harmarium_floor'        => array( 'type' => 'string' ),
		'_harmarium_opens_at'     => array( 'type' => 'string' ),
		'_harmarium_closes_at'    => array( 'type' => 'string' ),
		'_harmarium_curator'      => array( 'type' => 'string' ),
	) as $key => $args ) {
		register_post_meta( 'exhibition', $key, array_merge( $args, array(
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () { return current_user_can( 'edit_posts' ); },
		) ) );
	}
}
add_action( 'init', 'harmarium_register_exhibition', 6 );

/**
 * Admin meta box: pick portfolio items for an exhibition.
 */
function harmarium_exhibition_metaboxes() {
	add_meta_box(
		'harmarium_exhibition_items',
		__( 'Exhibition Layout', 'harmarium' ),
		'harmarium_exhibition_metabox_render',
		'exhibition',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'harmarium_exhibition_metaboxes' );

function harmarium_exhibition_metabox_render( $post ) {
	wp_nonce_field( 'harmarium_exhibition_save', 'harmarium_exhibition_nonce' );
	$items   = (array) get_post_meta( $post->ID, '_harmarium_exhibition_items', true );
	$wall    = get_post_meta( $post->ID, '_harmarium_wall_color', true ) ?: '#f3ede1';
	$finish  = get_post_meta( $post->ID, '_harmarium_wall_finish', true ) ?: 'matte';
	$light   = get_post_meta( $post->ID, '_harmarium_lighting', true ) ?: 'gallery';
	$floor   = get_post_meta( $post->ID, '_harmarium_floor', true ) ?: 'oak';
	$curator = get_post_meta( $post->ID, '_harmarium_curator', true );
	$opens   = get_post_meta( $post->ID, '_harmarium_opens_at', true );
	$closes  = get_post_meta( $post->ID, '_harmarium_closes_at', true );

	$portfolio = get_posts( array(
		'post_type'      => 'portfolio',
		'posts_per_page' => 200,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	echo '<p><strong>' . esc_html__( 'Portfolio items in this exhibition (drag to reorder):', 'harmarium' ) . '</strong></p>';
	echo '<ul class="harmarium-exhibition-picker" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.5rem;list-style:none;padding:0">';
	$selected = array_flip( array_map( 'intval', $items ) );
	foreach ( $portfolio as $p ) {
		$checked = isset( $selected[ $p->ID ] ) ? 'checked' : '';
		$thumb   = get_the_post_thumbnail( $p, 'thumbnail', array( 'style' => 'width:100%;height:auto;display:block' ) );
		echo '<li style="border:1px solid #ccd0d4;padding:.25rem;border-radius:2px"><label style="cursor:pointer;display:block">'
			. '<input type="checkbox" name="harmarium_exhibition_items[]" value="' . (int) $p->ID . '" ' . $checked . ' style="margin-bottom:.25rem">'
			. $thumb
			. '<span style="font-size:11px;line-height:1.2;display:block;margin-top:.25rem">' . esc_html( get_the_title( $p ) ) . '</span>'
			. '</label></li>';
	}
	echo '</ul>';

	echo '<table class="form-table"><tbody>';
	echo '<tr><th><label>' . esc_html__( 'Wall colour (hex)', 'harmarium' ) . '</label></th><td><input type="text" name="harmarium_wall_color" value="' . esc_attr( $wall ) . '" class="regular-text"></td></tr>';
	echo '<tr><th><label>' . esc_html__( 'Wall finish', 'harmarium' ) . '</label></th><td><select name="harmarium_wall_finish">';
	foreach ( array( 'matte' => 'Matte', 'satin' => 'Satin', 'plaster' => 'Plaster', 'concrete' => 'Concrete' ) as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $finish, $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th><label>' . esc_html__( 'Lighting', 'harmarium' ) . '</label></th><td><select name="harmarium_lighting">';
	foreach ( array( 'gallery' => 'Gallery spots', 'daylight' => 'Daylight', 'warm' => 'Warm tungsten', 'dramatic' => 'Dramatic' ) as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $light, $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th><label>' . esc_html__( 'Floor', 'harmarium' ) . '</label></th><td><select name="harmarium_floor">';
	foreach ( array( 'oak' => 'Oak', 'concrete' => 'Concrete', 'marble' => 'Marble', 'dark' => 'Dark wood' ) as $k => $l ) {
		echo '<option value="' . esc_attr( $k ) . '"' . selected( $floor, $k, false ) . '>' . esc_html( $l ) . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><th><label>' . esc_html__( 'Curator', 'harmarium' ) . '</label></th><td><input type="text" name="harmarium_curator" value="' . esc_attr( $curator ) . '" class="regular-text"></td></tr>';
	echo '<tr><th><label>' . esc_html__( 'Opens', 'harmarium' ) . '</label></th><td><input type="date" name="harmarium_opens_at" value="' . esc_attr( $opens ) . '"></td></tr>';
	echo '<tr><th><label>' . esc_html__( 'Closes', 'harmarium' ) . '</label></th><td><input type="date" name="harmarium_closes_at" value="' . esc_attr( $closes ) . '"></td></tr>';
	echo '</tbody></table>';
}

function harmarium_exhibition_save( $post_id, $post ) {
	if ( $post->post_type !== 'exhibition' ) {
		return;
	}
	if ( ! isset( $_POST['harmarium_exhibition_nonce'] ) || ! wp_verify_nonce( $_POST['harmarium_exhibition_nonce'], 'harmarium_exhibition_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$items = isset( $_POST['harmarium_exhibition_items'] ) ? array_map( 'intval', (array) $_POST['harmarium_exhibition_items'] ) : array();
	update_post_meta( $post_id, '_harmarium_exhibition_items', $items );

	$text_keys = array(
		'_harmarium_wall_color'  => 'harmarium_wall_color',
		'_harmarium_wall_finish' => 'harmarium_wall_finish',
		'_harmarium_lighting'    => 'harmarium_lighting',
		'_harmarium_floor'       => 'harmarium_floor',
		'_harmarium_curator'     => 'harmarium_curator',
		'_harmarium_opens_at'    => 'harmarium_opens_at',
		'_harmarium_closes_at'   => 'harmarium_closes_at',
	);
	foreach ( $text_keys as $meta => $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $meta, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'save_post', 'harmarium_exhibition_save', 10, 2 );

/**
 * Expose the resolved item list with thumbnails on the REST response.
 */
add_action( 'rest_api_init', function () {
	register_rest_field( 'exhibition', 'harmarium', array(
		'get_callback' => function ( $object ) {
			$id    = (int) $object['id'];
			$items = (array) get_post_meta( $id, '_harmarium_exhibition_items', true );
			$out   = array();
			foreach ( $items as $pid ) {
				$pid = (int) $pid;
				if ( ! $pid || get_post_status( $pid ) !== 'publish' ) {
					continue;
				}
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ), 'harmarium-square' );
				$out[] = array(
					'id'        => $pid,
					'title'     => get_the_title( $pid ),
					'permalink' => get_permalink( $pid ),
					'thumb'     => $thumb ? $thumb[0] : null,
				);
			}
			return array(
				'items'  => $out,
				'wall'   => array(
					'color'   => get_post_meta( $id, '_harmarium_wall_color', true ),
					'finish'  => get_post_meta( $id, '_harmarium_wall_finish', true ),
					'lighting'=> get_post_meta( $id, '_harmarium_lighting', true ),
					'floor'   => get_post_meta( $id, '_harmarium_floor', true ),
				),
				'curator' => get_post_meta( $id, '_harmarium_curator', true ),
				'opens'   => get_post_meta( $id, '_harmarium_opens_at', true ),
				'closes'  => get_post_meta( $id, '_harmarium_closes_at', true ),
			);
		},
		'schema' => array( 'description' => 'Resolved exhibition layout', 'type' => 'object' ),
	) );
} );
