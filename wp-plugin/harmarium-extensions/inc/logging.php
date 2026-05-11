<?php
/**
 * Activity logging: custom DB table + admin list table.
 *
 * Table: {prefix}hm_log
 * Columns: id, event_type, message, context (JSON), user_id, created_at
 */

defined( 'ABSPATH' ) || exit;

define( 'HARMARIUM_LOG_TABLE_VERSION', '1' );

/* ── Create table on activation ── */
function harmarium_log_create_table(): void {
	global $wpdb;
	$table   = $wpdb->prefix . 'hm_log';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		event_type VARCHAR(64)     NOT NULL DEFAULT '',
		message    TEXT            NOT NULL,
		context    LONGTEXT,
		user_id    BIGINT UNSIGNED NOT NULL DEFAULT 0,
		created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY event_type (event_type),
		KEY user_id (user_id),
		KEY created_at (created_at)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'hm_log_table_version', HARMARIUM_LOG_TABLE_VERSION );
}

/* ── Core log function ── */
function hm_log( string $event_type, string $message, array $context = [] ): void {
	global $wpdb;
	$wpdb->insert(
		$wpdb->prefix . 'hm_log',
		[
			'event_type' => sanitize_key( $event_type ),
			'message'    => wp_kses_post( $message ),
			'context'    => wp_json_encode( $context ),
			'user_id'    => (int) get_current_user_id(),
			'created_at' => current_time( 'mysql' ),
		],
		[ '%s', '%s', '%s', '%d', '%s' ]
	);
}

/* ── Admin menu page ── */
add_action( 'admin_menu', function (): void {
	add_submenu_page(
		'woocommerce',
		__( 'Harmarium Activity Log', 'harmarium-ext' ),
		__( 'Activity Log', 'harmarium-ext' ),
		'manage_woocommerce',
		'hm-activity-log',
		'harmarium_log_admin_page'
	);
} );

function harmarium_log_admin_page(): void {
	global $wpdb;
	$table = $wpdb->prefix . 'hm_log';

	$per_page    = 50;
	$current     = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	$offset      = ( $current - 1 ) * $per_page;
	$filter_type = sanitize_key( $_GET['event_type'] ?? '' );

	$where = $filter_type ? $wpdb->prepare( 'WHERE event_type = %s', $filter_type ) : '';

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$logs  = $wpdb->get_results( $wpdb->prepare(
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		"SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$per_page, $offset
	) );

	$event_types = $wpdb->get_col( "SELECT DISTINCT event_type FROM {$table} ORDER BY event_type" ); // phpcs:ignore

	echo '<div class="wrap">';
	echo '<h1>' . esc_html__( 'Activity Log', 'harmarium-ext' ) . '</h1>';

	// Filter
	echo '<form method="get" style="margin:1em 0">';
	echo '<input type="hidden" name="page" value="hm-activity-log">';
	echo '<select name="event_type"><option value="">' . esc_html__( 'All event types', 'harmarium-ext' ) . '</option>';
	foreach ( $event_types as $type ) {
		echo '<option value="' . esc_attr( $type ) . '"' . selected( $filter_type, $type, false ) . '>' . esc_html( $type ) . '</option>';
	}
	echo '</select> ';
	submit_button( __( 'Filter', 'harmarium-ext' ), 'secondary', '', false );
	echo '</form>';

	echo '<table class="widefat striped">';
	echo '<thead><tr>';
	echo '<th>' . esc_html__( 'ID', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'Type', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'Message', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'User', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'Date', 'harmarium-ext' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( (array) $logs as $row ) {
		$user_label = $row->user_id ? get_userdata( (int) $row->user_id )->user_login ?? '#' . $row->user_id : '—';
		echo '<tr>';
		echo '<td>' . esc_html( (string) $row->id ) . '</td>';
		echo '<td><code>' . esc_html( $row->event_type ) . '</code></td>';
		echo '<td>' . esc_html( $row->message ) . '</td>';
		echo '<td>' . esc_html( $user_label ) . '</td>';
		echo '<td>' . esc_html( $row->created_at ) . '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';

	// Pagination
	$total_pages = (int) ceil( $total / $per_page );
	if ( $total_pages > 1 ) {
		echo paginate_links( [
			'base'      => add_query_arg( 'paged', '%#%' ),
			'format'    => '',
			'current'   => $current,
			'total'     => $total_pages,
		] );
	}

	echo '</div>';
}
