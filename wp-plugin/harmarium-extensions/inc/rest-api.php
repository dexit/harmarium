<?php
/**
 * Custom REST endpoints:
 *  GET  harmarium/v1/qr?url=<url>&size=<px>        — returns SVG QR code
 *  POST harmarium/v1/commission                     — submit a commission request
 */

defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function (): void {

	/* ── QR endpoint ── */
	register_rest_route( 'harmarium/v1', '/qr', [
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'harmarium_rest_qr',
		'permission_callback' => '__return_true',
		'args' => [
			'url'  => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'esc_url_raw' ],
			'size' => [ 'type' => 'integer', 'default' => 4, 'minimum' => 2, 'maximum' => 12 ],
			'fg'   => [ 'type' => 'string',  'default' => '#000000' ],
			'bg'   => [ 'type' => 'string',  'default' => '#ffffff' ],
		],
	] );

	/* ── Commission endpoint ── */
	register_rest_route( 'harmarium/v1', '/commission', [
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'harmarium_rest_commission',
		'permission_callback' => '__return_true',
		'args' => [
			'name'     => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			'email'    => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_email', 'validate_callback' => fn( $v ) => is_email( $v ) ],
			'subject'  => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
			'message'  => [ 'type' => 'string', 'required' => true, 'sanitize_callback' => 'sanitize_textarea_field' ],
			'budget'   => [ 'type' => 'string', 'default' => '',    'sanitize_callback' => 'sanitize_text_field' ],
			'timeline' => [ 'type' => 'string', 'default' => '',    'sanitize_callback' => 'sanitize_text_field' ],
			'nonce'    => [ 'type' => 'string', 'required' => true ],
		],
	] );
} );

function harmarium_rest_qr( WP_REST_Request $req ): WP_REST_Response|WP_Error {
	$url  = $req->get_param( 'url' );
	$size = (int) $req->get_param( 'size' );
	$fg   = $req->get_param( 'fg' );
	$bg   = $req->get_param( 'bg' );

	// Basic hex colour validation
	if ( ! preg_match( '/^#[0-9a-fA-F]{3,6}$/', $fg ) ) $fg = '#000000';
	if ( ! preg_match( '/^#[0-9a-fA-F]{3,6}$/', $bg ) ) $bg = '#ffffff';

	try {
		$svg = Harmarium_QR::svg( $url, $size, $fg, $bg );
	} catch ( \InvalidArgumentException $e ) {
		return new WP_Error( 'qr_too_long', $e->getMessage(), [ 'status' => 400 ] );
	}

	return new WP_REST_Response( [ 'svg' => $svg, 'data_uri' => 'data:image/svg+xml;base64,' . base64_encode( $svg ) ] );
}

function harmarium_rest_commission( WP_REST_Request $req ): WP_REST_Response|WP_Error {
	// Verify nonce
	if ( ! wp_verify_nonce( $req->get_param( 'nonce' ), 'harmarium_commission' ) ) {
		return new WP_Error( 'bad_nonce', __( 'Security check failed.', 'harmarium-ext' ), [ 'status' => 403 ] );
	}

	$data = [
		'name'     => $req->get_param( 'name' ),
		'email'    => $req->get_param( 'email' ),
		'subject'  => $req->get_param( 'subject' ),
		'message'  => $req->get_param( 'message' ),
		'budget'   => $req->get_param( 'budget' ),
		'timeline' => $req->get_param( 'timeline' ),
	];

	$post_id = wp_insert_post( [
		'post_title'   => sanitize_text_field( $data['name'] . ' — ' . $data['subject'] ),
		'post_type'    => 'hm_commission',
		'post_status'  => 'hm_new',
		'post_content' => '',
		'meta_input'   => [
			'_hm_email'    => $data['email'],
			'_hm_subject'  => $data['subject'],
			'_hm_message'  => $data['message'],
			'_hm_budget'   => $data['budget'],
			'_hm_timeline' => $data['timeline'],
		],
	], true );

	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'insert_failed', __( 'Could not save request.', 'harmarium-ext' ), [ 'status' => 500 ] );
	}

	harmarium_commission_notify( $post_id, $data );

	return new WP_REST_Response( [ 'id' => $post_id, 'message' => __( 'Thank you — your request has been received.', 'harmarium-ext' ) ], 201 );
}
