<?php
/**
 * Generic order status change email for custom statuses.
 */

defined( 'ABSPATH' ) || exit;

class HM_Email_Status_Change extends WC_Email {

	protected string $new_status = '';

	public function __construct() {
		$this->id             = 'hm_status_change';
		$this->customer_email = true;
		$this->title          = __( 'Commission Status Update', 'harmarium-ext' );
		$this->description    = __( 'Sent to customer when their commission order status changes.', 'harmarium-ext' );
		$this->heading        = __( 'Your order status has been updated', 'harmarium-ext' );
		$this->subject        = __( '[{site_title}] Update on your order #{order_number}', 'harmarium-ext' );
		$this->template_html  = 'emails/harmarium-status-change.php';
		$this->template_plain = 'emails/plain/harmarium-status-change.php';

		$this->placeholders = array_merge(
			$this->placeholders,
			[ '{order_number}' => '', '{order_date}' => '', '{status_label}' => '' ]
		);

		parent::__construct();
	}

	public function trigger( int $order_id, ?\WC_Order $order = null, string $new_status = '' ): void {
		$this->setup_locale();
		$order = $order ?: wc_get_order( $order_id );
		if ( ! $order ) {
			$this->restore_locale();
			return;
		}

		$this->new_status                       = $new_status;
		$this->object                           = $order;
		$this->recipient                        = $order->get_billing_email();
		$this->placeholders['{order_number}']   = $order->get_order_number();
		$this->placeholders['{order_date}']     = wc_format_datetime( $order->get_date_created() );

		$wc_statuses = wc_get_order_statuses();
		$status_key  = 'wc-' . $new_status;
		$this->placeholders['{status_label}'] = $wc_statuses[ $status_key ] ?? $new_status;

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	public function get_new_status(): string {
		return $this->new_status;
	}

	public function get_content_html(): string {
		return wc_get_template_html(
			$this->template_html,
			[ 'order' => $this->object, 'email_heading' => $this->get_heading(), 'new_status' => $this->new_status, 'sent_to_admin' => false, 'plain_text' => false, 'email' => $this ],
			'', HARMARIUM_EXT_DIR . 'templates/'
		);
	}

	public function get_content_plain(): string {
		return wc_get_template_html(
			$this->template_plain,
			[ 'order' => $this->object, 'email_heading' => $this->get_heading(), 'new_status' => $this->new_status, 'sent_to_admin' => false, 'plain_text' => true, 'email' => $this ],
			'', HARMARIUM_EXT_DIR . 'templates/'
		);
	}
}
