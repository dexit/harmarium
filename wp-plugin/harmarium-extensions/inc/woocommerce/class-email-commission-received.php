<?php
/**
 * Email sent to the admin when a new commission request is submitted via WooCommerce order.
 */

defined( 'ABSPATH' ) || exit;

class HM_Email_Commission_Received extends WC_Email {

	public function __construct() {
		$this->id             = 'hm_commission_received';
		$this->title          = __( 'Commission Request Received', 'harmarium-ext' );
		$this->description    = __( 'Sent to the admin when a commission order is placed.', 'harmarium-ext' );
		$this->heading        = __( 'New Commission Request', 'harmarium-ext' );
		$this->subject        = __( '[{site_title}] New commission request — Order #{order_number}', 'harmarium-ext' );
		$this->template_html  = 'emails/harmarium-commission-received.php';
		$this->template_plain = 'emails/plain/harmarium-commission-received.php';
		$this->recipient      = $this->get_option( 'recipient', get_option( 'admin_email' ) );

		$this->placeholders = array_merge(
			$this->placeholders,
			[ '{order_number}' => '', '{order_date}' => '', '{customer_name}' => '' ]
		);

		add_action( 'woocommerce_order_status_processing', [ $this, 'trigger' ], 10, 2 );

		parent::__construct();
	}

	public function trigger( int $order_id, ?\WC_Order $order = null ): void {
		$this->setup_locale();
		$order = $order ?: wc_get_order( $order_id );
		if ( ! $order ) {
			$this->restore_locale();
			return;
		}

		if ( ! harmarium_order_has_commission_product( $order ) ) {
			$this->restore_locale();
			return;
		}

		$this->object                           = $order;
		$this->placeholders['{order_number}']   = $order->get_order_number();
		$this->placeholders['{order_date}']     = wc_format_datetime( $order->get_date_created() );
		$this->placeholders['{customer_name}']  = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	public function get_content_html(): string {
		return wc_get_template_html(
			$this->template_html,
			[ 'order' => $this->object, 'email_heading' => $this->get_heading(), 'sent_to_admin' => true, 'plain_text' => false, 'email' => $this ],
			'', HARMARIUM_EXT_DIR . 'templates/'
		);
	}

	public function get_content_plain(): string {
		return wc_get_template_html(
			$this->template_plain,
			[ 'order' => $this->object, 'email_heading' => $this->get_heading(), 'sent_to_admin' => true, 'plain_text' => true, 'email' => $this ],
			'', HARMARIUM_EXT_DIR . 'templates/'
		);
	}
}

function harmarium_order_has_commission_product( \WC_Order $order ): bool {
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( ! $product ) continue;
		$cats = wp_get_post_terms( $product->get_id(), 'product_cat', [ 'fields' => 'slugs' ] );
		if ( in_array( 'commission', (array) $cats, true ) || in_array( 'artwork', (array) $cats, true ) ) {
			return true;
		}
	}
	return false;
}
