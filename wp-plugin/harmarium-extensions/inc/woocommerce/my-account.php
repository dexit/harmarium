<?php
/**
 * Custom My Account endpoints: commissions, proof approval.
 */

defined( 'ABSPATH' ) || exit;

/* ── Register rewrite endpoints ── */
add_action( 'init', function (): void {
	add_rewrite_endpoint( 'commissions',    EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'proof-approval', EP_ROOT | EP_PAGES );
} );

/* ── Add to menu ── */
add_filter( 'woocommerce_account_menu_items', function ( array $items ): array {
	$new = [];
	foreach ( $items as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'orders' === $key ) {
			$new['commissions']    = __( 'Commission Requests', 'harmarium-ext' );
			$new['proof-approval'] = __( 'Proof Approval',      'harmarium-ext' );
		}
	}
	return $new;
} );

/* ── Commission Requests page ── */
add_action( 'woocommerce_account_commissions_endpoint', function (): void {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		echo '<p>' . esc_html__( 'You must be logged in to view your commission requests.', 'harmarium-ext' ) . '</p>';
		return;
	}

	$commissions = get_posts( [
		'post_type'      => 'hm_commission',
		'post_status'    => [ 'hm_new', 'hm_reviewing', 'hm_quoted', 'hm_accepted', 'hm_completed', 'hm_declined' ],
		'meta_query'     => [
			[
				'key'   => '_hm_user_id',
				'value' => $user_id,
			],
		],
		'posts_per_page' => 20,
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );

	echo '<h2>' . esc_html__( 'Your Commission Requests', 'harmarium-ext' ) . '</h2>';

	if ( empty( $commissions ) ) {
		echo '<div class="woocommerce-message woocommerce-message--info">';
		echo '<p>' . esc_html__( 'No commission requests found.', 'harmarium-ext' ) . ' ';
		echo '<a href="' . esc_url( get_permalink( get_page_by_path( 'commission' ) ) ?: home_url( '/commission/' ) ) . '">';
		echo esc_html__( 'Submit a new request', 'harmarium-ext' );
		echo '</a></p></div>';
		return;
	}

	echo '<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive">';
	echo '<thead><tr>';
	echo '<th>' . esc_html__( 'Request', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'Subject', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'Status', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'Date', 'harmarium-ext' ) . '</th>';
	echo '<th>' . esc_html__( 'Budget', 'harmarium-ext' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( $commissions as $commission ) {
		$status_obj = get_post_status_object( $commission->post_status );
		$status_label = $status_obj ? $status_obj->label : $commission->post_status;
		echo '<tr class="woocommerce-orders-table__row">';
		echo '<td>#' . esc_html( (string) $commission->ID ) . '</td>';
		echo '<td>' . esc_html( get_post_meta( $commission->ID, '_hm_subject', true ) ) . '</td>';
		echo '<td><span class="hm-status-pill hm-status-' . esc_attr( $commission->post_status ) . '">' . esc_html( $status_label ) . '</span></td>';
		echo '<td>' . esc_html( get_the_date( get_option( 'date_format' ), $commission ) ) . '</td>';
		echo '<td>' . esc_html( get_post_meta( $commission->ID, '_hm_budget', true ) ?: '—' ) . '</td>';
		echo '</tr>';
	}

	echo '</tbody></table>';
} );

/* ── Proof Approval page ── */
add_action( 'woocommerce_account_proof-approval_endpoint', function (): void {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		echo '<p>' . esc_html__( 'You must be logged in.', 'harmarium-ext' ) . '</p>';
		return;
	}

	// Handle approval/rejection form POST
	if ( isset( $_POST['hm_proof_action'], $_POST['hm_order_id'], $_POST['_wpnonce'] ) ) {
		$order_id = absint( $_POST['hm_order_id'] );
		if ( wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'hm_proof_' . $order_id ) ) {
			$order = wc_get_order( $order_id );
			if ( $order && (int) $order->get_customer_id() === $user_id ) {
				$action = sanitize_key( $_POST['hm_proof_action'] );
				if ( 'approve' === $action ) {
					$order->update_status( 'wc-proof-approved', __( 'Proof approved by customer.', 'harmarium-ext' ) );
					$order->update_meta_data( '_hm_proof_approved_at', current_time( 'mysql' ) );
					$order->save();
					wc_add_notice( __( 'Proof approved! We will begin production shortly.', 'harmarium-ext' ), 'success' );
				} elseif ( 'reject' === $action ) {
					$notes = sanitize_textarea_field( wp_unslash( $_POST['hm_proof_notes'] ?? '' ) );
					$order->add_order_note( __( 'Customer requested proof revision: ', 'harmarium-ext' ) . $notes, true );
					$order->update_status( 'wc-awaiting-proof', __( 'Customer requested proof revision.', 'harmarium-ext' ) );
					$order->save();
					wc_add_notice( __( 'Revision request sent. We will be in touch.', 'harmarium-ext' ), 'notice' );
				}
			}
		}
	}

	// Find orders with proof sent
	$orders = wc_get_orders( [
		'customer_id' => $user_id,
		'status'      => [ 'wc-proof-sent' ],
		'limit'       => 20,
	] );

	echo '<h2>' . esc_html__( 'Proof Approval', 'harmarium-ext' ) . '</h2>';

	if ( empty( $orders ) ) {
		echo '<p>' . esc_html__( 'No proofs awaiting your approval at this time.', 'harmarium-ext' ) . '</p>';
		return;
	}

	foreach ( $orders as $order ) {
		$order_id  = $order->get_id();
		$proof_url = $order->get_meta( '_hm_proof_url' );
		$proof_img = $order->get_meta( '_hm_proof_attachment_id' );
		?>
		<div class="hm-proof-card">
			<h3><?php printf( esc_html__( 'Order #%s', 'harmarium-ext' ), esc_html( (string) $order->get_order_number() ) ); ?></h3>
			<?php if ( $proof_img ) : ?>
				<div class="hm-proof-preview">
					<?php echo wp_get_attachment_image( (int) $proof_img, 'large' ); ?>
				</div>
			<?php elseif ( $proof_url ) : ?>
				<p><a href="<?php echo esc_url( $proof_url ); ?>" target="_blank" rel="noopener">
					<?php esc_html_e( 'View proof', 'harmarium-ext' ); ?>
				</a></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Proof file attached — please contact us to view.', 'harmarium-ext' ); ?></p>
			<?php endif; ?>
			<form method="post" class="hm-proof-form">
				<?php wp_nonce_field( 'hm_proof_' . $order_id ); ?>
				<input type="hidden" name="hm_order_id" value="<?php echo esc_attr( (string) $order_id ); ?>">
				<div class="hm-proof-actions">
					<button type="submit" name="hm_proof_action" value="approve" class="button alt">
						<?php esc_html_e( 'Approve Proof', 'harmarium-ext' ); ?>
					</button>
					<button type="submit" name="hm_proof_action" value="reject" class="button">
						<?php esc_html_e( 'Request Revision', 'harmarium-ext' ); ?>
					</button>
				</div>
				<div class="hm-proof-notes-wrap" style="margin-top:1em">
					<label for="hm_proof_notes_<?php echo esc_attr( (string) $order_id ); ?>">
						<?php esc_html_e( 'Revision notes (if requesting changes):', 'harmarium-ext' ); ?>
					</label>
					<textarea name="hm_proof_notes"
						id="hm_proof_notes_<?php echo esc_attr( (string) $order_id ); ?>"
						rows="4" class="input-text"></textarea>
				</div>
			</form>
		</div>
		<?php
	}
} );

/* ── Endpoint titles ── */
add_filter( 'the_title', function ( string $title ): string {
	global $wp_query;
	if ( ! isset( $wp_query ) || ! is_account_page() ) return $title;
	if ( isset( $wp_query->query_vars['commissions'] ) ) {
		return __( 'Commission Requests', 'harmarium-ext' );
	}
	if ( isset( $wp_query->query_vars['proof-approval'] ) ) {
		return __( 'Proof Approval', 'harmarium-ext' );
	}
	return $title;
} );
