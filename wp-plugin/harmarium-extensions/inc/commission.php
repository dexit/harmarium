<?php
/**
 * Commission / quote request CPT with statuses, admin list columns,
 * REST endpoint, and email notifications.
 */

defined( 'ABSPATH' ) || exit;

/* ── Register CPT ── */
add_action( 'init', function (): void {
	register_post_type( 'hm_commission', [
		'labels' => [
			'name'               => __( 'Commission Requests', 'harmarium-ext' ),
			'singular_name'      => __( 'Commission Request', 'harmarium-ext' ),
			'menu_name'          => __( 'Commissions', 'harmarium-ext' ),
			'all_items'          => __( 'All Requests', 'harmarium-ext' ),
			'add_new'            => __( 'Add Request', 'harmarium-ext' ),
			'add_new_item'       => __( 'Add New Request', 'harmarium-ext' ),
			'edit_item'          => __( 'View Request', 'harmarium-ext' ),
		],
		'public'        => false,
		'show_ui'       => true,
		'show_in_menu'  => true,
		'menu_icon'     => 'dashicons-art',
		'menu_position' => 26,
		'supports'      => [ 'title', 'custom-fields' ],
		'show_in_rest'  => true,
		'rest_base'     => 'hm-commission',
		'capability_type' => 'post',
		'capabilities'  => [ 'create_posts' => 'do_not_allow' ],
		'map_meta_cap'  => true,
	] );

	register_post_status( 'hm_new',        [ 'label' => _x( 'New',        'commission status', 'harmarium-ext' ), 'public' => false, 'show_in_admin_status_list' => true, 'label_count' => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>', 'harmarium-ext' ) ] );
	register_post_status( 'hm_reviewing',  [ 'label' => _x( 'Reviewing',  'commission status', 'harmarium-ext' ), 'public' => false, 'show_in_admin_status_list' => true, 'label_count' => _n_noop( 'Reviewing <span class="count">(%s)</span>', 'Reviewing <span class="count">(%s)</span>', 'harmarium-ext' ) ] );
	register_post_status( 'hm_quoted',     [ 'label' => _x( 'Quoted',     'commission status', 'harmarium-ext' ), 'public' => false, 'show_in_admin_status_list' => true, 'label_count' => _n_noop( 'Quoted <span class="count">(%s)</span>', 'Quoted <span class="count">(%s)</span>', 'harmarium-ext' ) ] );
	register_post_status( 'hm_accepted',   [ 'label' => _x( 'Accepted',   'commission status', 'harmarium-ext' ), 'public' => false, 'show_in_admin_status_list' => true, 'label_count' => _n_noop( 'Accepted <span class="count">(%s)</span>', 'Accepted <span class="count">(%s)</span>', 'harmarium-ext' ) ] );
	register_post_status( 'hm_completed',  [ 'label' => _x( 'Completed',  'commission status', 'harmarium-ext' ), 'public' => false, 'show_in_admin_status_list' => true, 'label_count' => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'harmarium-ext' ) ] );
	register_post_status( 'hm_declined',   [ 'label' => _x( 'Declined',   'commission status', 'harmarium-ext' ), 'public' => false, 'show_in_admin_status_list' => true, 'label_count' => _n_noop( 'Declined <span class="count">(%s)</span>', 'Declined <span class="count">(%s)</span>', 'harmarium-ext' ) ] );
} );

/* ── Admin list columns ── */
add_filter( 'manage_hm_commission_posts_columns', function ( array $cols ): array {
	return [
		'cb'           => $cols['cb'],
		'title'        => __( 'Name', 'harmarium-ext' ),
		'hm_email'     => __( 'Email', 'harmarium-ext' ),
		'hm_subject'   => __( 'Subject', 'harmarium-ext' ),
		'hm_budget'    => __( 'Budget', 'harmarium-ext' ),
		'hm_status'    => __( 'Status', 'harmarium-ext' ),
		'date'         => __( 'Date', 'harmarium-ext' ),
	];
} );

add_action( 'manage_hm_commission_posts_custom_column', function ( string $col, int $post_id ): void {
	switch ( $col ) {
		case 'hm_email':   echo esc_html( get_post_meta( $post_id, '_hm_email',   true ) ); break;
		case 'hm_subject': echo esc_html( get_post_meta( $post_id, '_hm_subject', true ) ); break;
		case 'hm_budget':  echo esc_html( get_post_meta( $post_id, '_hm_budget',  true ) ); break;
		case 'hm_status':
			$post = get_post( $post_id );
			echo '<span class="hm-status-pill hm-status-' . esc_attr( $post->post_status ) . '">' . esc_html( get_post_status_object( $post->post_status )->label ?? $post->post_status ) . '</span>';
			break;
	}
}, 10, 2 );

/* ── Admin status quick-change ── */
add_action( 'post_submitbox_misc_actions', function (): void {
	global $post;
	if ( 'hm_commission' !== $post->post_type ) return;
	$statuses = [ 'hm_new', 'hm_reviewing', 'hm_quoted', 'hm_accepted', 'hm_completed', 'hm_declined' ];
	echo '<div class="misc-pub-section"><label for="hm_status_select">' . esc_html__( 'Commission Status:', 'harmarium-ext' ) . '</label> ';
	echo '<select id="hm_status_select" name="hm_commission_status">';
	foreach ( $statuses as $s ) {
		$obj = get_post_status_object( $s );
		echo '<option value="' . esc_attr( $s ) . '"' . selected( $post->post_status, $s, false ) . '>' . esc_html( $obj ? $obj->label : $s ) . '</option>';
	}
	echo '</select></div>';
} );

add_action( 'save_post_hm_commission', function ( int $post_id ): void {
	if ( ! isset( $_POST['hm_commission_status'] ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$allowed = [ 'hm_new', 'hm_reviewing', 'hm_quoted', 'hm_accepted', 'hm_completed', 'hm_declined' ];
	$new_status = sanitize_key( $_POST['hm_commission_status'] );
	if ( in_array( $new_status, $allowed, true ) ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, [ 'post_status' => $new_status ], [ 'ID' => $post_id ] );
		clean_post_cache( $post_id );
	}
} );

/* ── Admin CSS for status pills ── */
add_action( 'admin_head', function (): void {
	$screen = get_current_screen();
	if ( ! $screen || 'hm_commission' !== $screen->post_type ) return;
	?>
	<style>
	.hm-status-pill { display:inline-block; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.05em; }
	.hm-status-hm_new       { background:#e0f2fe; color:#0369a1; }
	.hm-status-hm_reviewing { background:#fef9c3; color:#854d0e; }
	.hm-status-hm_quoted    { background:#fae8ff; color:#7e22ce; }
	.hm-status-hm_accepted  { background:#dcfce7; color:#166534; }
	.hm-status-hm_completed { background:#d1fae5; color:#065f46; }
	.hm-status-hm_declined  { background:#fee2e2; color:#991b1b; }
	</style>
	<?php
} );

/* ── Email notification ── */
function harmarium_commission_notify( int $post_id, array $data ): void {
	$to      = get_option( 'admin_email' );
	$subject = sprintf( __( '[Harmarium] New commission request from %s', 'harmarium-ext' ), $data['name'] );
	$body    = sprintf(
		"Name: %s\nEmail: %s\nSubject: %s\nBudget: %s\nTimeline: %s\n\nMessage:\n%s",
		$data['name'],
		$data['email'],
		$data['subject'],
		$data['budget'] ?? '',
		$data['timeline'] ?? '',
		$data['message']
	);
	wp_mail( $to, $subject, $body );

	// Confirmation to sender
	wp_mail(
		$data['email'],
		__( 'Your commission request has been received — Harmarium', 'harmarium-ext' ),
		sprintf( "Thank you %s,\n\nYour request has been received. I'll be in touch within 2–3 working days.\n\nWarm regards,\nHarmarium", $data['name'] )
	);
}
