<?php
/**
 * Custom WooCommerce order statuses for the commission-to-delivery workflow.
 *
 * Statuses: awaiting-proof → proof-sent → proof-approved → in-production → ready-ship
 */

defined( 'ABSPATH' ) || exit;

/* ── Register statuses ── */
add_action( 'init', function (): void {
	$statuses = [
		'wc-awaiting-proof'  => _x( 'Awaiting Proof',  'Order status', 'harmarium-ext' ),
		'wc-proof-sent'      => _x( 'Proof Sent',       'Order status', 'harmarium-ext' ),
		'wc-proof-approved'  => _x( 'Proof Approved',   'Order status', 'harmarium-ext' ),
		'wc-in-production'   => _x( 'In Production',    'Order status', 'harmarium-ext' ),
		'wc-ready-ship'      => _x( 'Ready to Ship',    'Order status', 'harmarium-ext' ),
	];

	foreach ( $statuses as $slug => $label ) {
		register_post_status( $slug, [
			'label'                     => $label,
			'public'                    => false,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop(
				$label . ' <span class="count">(%s)</span>',
				$label . ' <span class="count">(%s)</span>',
				'harmarium-ext'
			),
		] );
	}
} );

/* ── Add to WooCommerce status list ── */
add_filter( 'wc_order_statuses', function ( array $statuses ): array {
	$custom = [
		'wc-awaiting-proof' => _x( 'Awaiting Proof', 'Order status', 'harmarium-ext' ),
		'wc-proof-sent'     => _x( 'Proof Sent',      'Order status', 'harmarium-ext' ),
		'wc-proof-approved' => _x( 'Proof Approved',  'Order status', 'harmarium-ext' ),
		'wc-in-production'  => _x( 'In Production',   'Order status', 'harmarium-ext' ),
		'wc-ready-ship'     => _x( 'Ready to Ship',   'Order status', 'harmarium-ext' ),
	];

	// Insert after 'wc-processing'
	$out = [];
	foreach ( $statuses as $key => $label ) {
		$out[ $key ] = $label;
		if ( 'wc-processing' === $key ) {
			foreach ( $custom as $ck => $cl ) {
				$out[ $ck ] = $cl;
			}
		}
	}
	return $out;
} );

/* ── Allow payment for custom statuses ── */
add_filter( 'woocommerce_valid_order_statuses_for_payment', function ( array $statuses ): array {
	return array_merge( $statuses, [ 'awaiting-proof', 'proof-sent', 'proof-approved', 'in-production', 'ready-ship' ] );
} );

/* ── Bulk actions ── */
add_filter( 'bulk_actions-woocommerce_page_wc-orders', 'harmarium_wc_bulk_order_status_actions' );
add_filter( 'bulk_actions-edit-shop_order', 'harmarium_wc_bulk_order_status_actions' );

function harmarium_wc_bulk_order_status_actions( array $actions ): array {
	$actions['mark_awaiting-proof']  = __( 'Mark: Awaiting Proof', 'harmarium-ext' );
	$actions['mark_proof-sent']      = __( 'Mark: Proof Sent',      'harmarium-ext' );
	$actions['mark_proof-approved']  = __( 'Mark: Proof Approved',  'harmarium-ext' );
	$actions['mark_in-production']   = __( 'Mark: In Production',   'harmarium-ext' );
	$actions['mark_ready-ship']      = __( 'Mark: Ready to Ship',   'harmarium-ext' );
	return $actions;
}

/* ── Status colours in admin ── */
add_action( 'admin_head', function (): void {
	$screen = get_current_screen();
	if ( ! $screen ) return;
	if ( ! in_array( $screen->id, [ 'woocommerce_page_wc-orders', 'edit-shop_order' ], true ) ) return;
	?>
	<style>
	.order-status.status-awaiting-proof { background:#e0f2fe; color:#0369a1; }
	.order-status.status-proof-sent     { background:#fef9c3; color:#854d0e; }
	.order-status.status-proof-approved { background:#dcfce7; color:#166534; }
	.order-status.status-in-production  { background:#f3e8ff; color:#7e22ce; }
	.order-status.status-ready-ship     { background:#d1fae5; color:#065f46; }
	</style>
	<?php
} );

/* ── Trigger status-change emails ── */
add_action( 'woocommerce_order_status_changed', 'harmarium_wc_status_change_email', 10, 4 );

function harmarium_wc_status_change_email( int $order_id, string $old_status, string $new_status, \WC_Order $order ): void {
	$custom_statuses = [ 'awaiting-proof', 'proof-sent', 'proof-approved', 'in-production', 'ready-ship' ];
	if ( ! in_array( $new_status, $custom_statuses, true ) ) return;

	$mailer = WC()->mailer();
	$emails = $mailer->get_emails();

	$email_map = [
		'proof-sent'    => 'HM_Email_Proof_Sent',
		'proof-approved'=> 'HM_Email_Status_Change',
		'in-production' => 'HM_Email_Status_Change',
		'ready-ship'    => 'HM_Email_Status_Change',
	];

	$class = $email_map[ $new_status ] ?? 'HM_Email_Status_Change';
	foreach ( $emails as $email ) {
		if ( $email instanceof $class ) {
			$email->trigger( $order_id, $order, $new_status );
			break;
		}
	}
}

/* ── Log status changes ── */
add_action( 'woocommerce_order_status_changed', function ( int $order_id, string $old, string $new ): void {
	if ( function_exists( 'hm_log' ) ) {
		hm_log( 'order_status', sprintf(
			'Order #%d: %s → %s',
			$order_id, $old, $new
		), [ 'order_id' => $order_id, 'old_status' => $old, 'new_status' => $new ] );
	}
}, 10, 3 );
