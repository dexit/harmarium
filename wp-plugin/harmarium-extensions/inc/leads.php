<?php
/**
 * Lead / submission tracking.
 *
 * Table: {prefix}hm_leads
 * Captures UTM params, referrer, IP hash, and user agent on every commission submission.
 */

defined( 'ABSPATH' ) || exit;

define( 'HARMARIUM_LEADS_TABLE_VERSION', '1' );

/* ── Create table on activation ── */
function harmarium_leads_create_table(): void {
	global $wpdb;
	$table   = $wpdb->prefix . 'hm_leads';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		commission_id   BIGINT UNSIGNED NOT NULL DEFAULT 0,
		email_hash      VARCHAR(64)     NOT NULL DEFAULT '',
		utm_source      VARCHAR(128)    NOT NULL DEFAULT '',
		utm_medium      VARCHAR(128)    NOT NULL DEFAULT '',
		utm_campaign    VARCHAR(128)    NOT NULL DEFAULT '',
		utm_content     VARCHAR(128)    NOT NULL DEFAULT '',
		utm_term        VARCHAR(128)    NOT NULL DEFAULT '',
		referrer        VARCHAR(512)    NOT NULL DEFAULT '',
		landing_page    VARCHAR(512)    NOT NULL DEFAULT '',
		ip_hash         VARCHAR(64)     NOT NULL DEFAULT '',
		user_agent      VARCHAR(512)    NOT NULL DEFAULT '',
		created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY commission_id (commission_id),
		KEY utm_source (utm_source),
		KEY created_at (created_at)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'hm_leads_table_version', HARMARIUM_LEADS_TABLE_VERSION );
}

/* ── Track a lead on commission submission ── */
function hm_track_lead( int $commission_id, string $email, array $extra = [] ): void {
	global $wpdb;

	$server = $_SERVER ?? [];

	$ip_raw   = (string) ( $server['HTTP_X_FORWARDED_FOR'] ?? $server['REMOTE_ADDR'] ?? '' );
	$ip_first = trim( explode( ',', $ip_raw )[0] );
	$ip_hash  = hash( 'sha256', $ip_first . wp_salt( 'auth' ) );

	$wpdb->insert(
		$wpdb->prefix . 'hm_leads',
		[
			'commission_id' => $commission_id,
			'email_hash'    => hash( 'sha256', strtolower( trim( $email ) ) . wp_salt( 'auth' ) ),
			'utm_source'    => sanitize_text_field( $extra['utm_source']   ?? '' ),
			'utm_medium'    => sanitize_text_field( $extra['utm_medium']   ?? '' ),
			'utm_campaign'  => sanitize_text_field( $extra['utm_campaign'] ?? '' ),
			'utm_content'   => sanitize_text_field( $extra['utm_content']  ?? '' ),
			'utm_term'      => sanitize_text_field( $extra['utm_term']     ?? '' ),
			'referrer'      => esc_url_raw( $extra['referrer']      ?? '' ),
			'landing_page'  => esc_url_raw( $extra['landing_page']  ?? '' ),
			'ip_hash'       => $ip_hash,
			'user_agent'    => substr( sanitize_text_field( wp_unslash( $server['HTTP_USER_AGENT'] ?? '' ) ), 0, 512 ),
			'created_at'    => current_time( 'mysql' ),
		],
		[ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
	);
}

/* ── Expose UTM params in commission REST endpoint ── */
add_filter( 'harmarium_commission_rest_extra', function ( array $extra, WP_REST_Request $req ): array {
	$utm_fields = [ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'referrer', 'landing_page' ];
	foreach ( $utm_fields as $field ) {
		$extra[ $field ] = sanitize_text_field( (string) $req->get_param( $field ) );
	}
	return $extra;
}, 10, 2 );

/* ── Admin submenu page ── */
add_action( 'admin_menu', function (): void {
	add_submenu_page(
		'woocommerce',
		__( 'Commission Leads', 'harmarium-ext' ),
		__( 'Leads', 'harmarium-ext' ),
		'manage_woocommerce',
		'hm-leads',
		'harmarium_leads_admin_page'
	);
} );

function harmarium_leads_admin_page(): void {
	global $wpdb;
	$table  = $wpdb->prefix . 'hm_leads';
	$per    = 50;
	$paged  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
	$offset = ( $paged - 1 ) * $per;

	$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore
	$rows  = $wpdb->get_results( $wpdb->prepare(
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
		$per, $offset
	) );

	echo '<div class="wrap"><h1>' . esc_html__( 'Commission Leads', 'harmarium-ext' ) . '</h1>';
	echo '<table class="widefat striped"><thead><tr>';
	foreach ( [ 'ID', 'Commission', 'UTM Source', 'UTM Medium', 'Campaign', 'Referrer', 'Date' ] as $col ) {
		echo '<th>' . esc_html( $col ) . '</th>';
	}
	echo '</tr></thead><tbody>';

	foreach ( (array) $rows as $row ) {
		$commission_link = $row->commission_id
			? '<a href="' . esc_url( get_edit_post_link( (int) $row->commission_id ) ) . '">#' . esc_html( (string) $row->commission_id ) . '</a>'
			: '—';
		echo '<tr>';
		echo '<td>' . esc_html( (string) $row->id ) . '</td>';
		echo '<td>' . $commission_link . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput
		echo '<td>' . esc_html( $row->utm_source ) . '</td>';
		echo '<td>' . esc_html( $row->utm_medium ) . '</td>';
		echo '<td>' . esc_html( $row->utm_campaign ) . '</td>';
		echo '<td>' . esc_html( $row->referrer ) . '</td>';
		echo '<td>' . esc_html( $row->created_at ) . '</td>';
		echo '</tr>';
	}
	echo '</tbody></table>';

	$total_pages = (int) ceil( $total / $per );
	if ( $total_pages > 1 ) {
		echo paginate_links( [ 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => $paged, 'total' => $total_pages ] );
	}
	echo '</div>';
}
