<?php
/**
 * Email sent to customer when a proof is ready for review.
 */

defined( 'ABSPATH' ) || exit;

class HM_Email_Proof_Sent extends WC_Email {

	public function __construct() {
		$this->id             = 'hm_proof_sent';
		$this->customer_email = true;
		$this->title          = __( 'Proof Ready for Review', 'harmarium-ext' );
		$this->description    = __( 'Sent to customer when their artwork proof is ready to review.', 'harmarium-ext' );
		$this->heading        = __( 'Your Proof is Ready', 'harmarium-ext' );
		$this->subject        = __( '[{site_title}] Your proof is ready — Order #{order_number}', 'harmarium-ext' );
		$this->template_html  = 'emails/harmarium-proof-sent.php';
		$this->template_plain = 'emails/plain/harmarium-proof-sent.php';

		$this->placeholders = array_merge(
			$this->placeholders,
			[ '{order_number}' => '', '{order_date}' => '', '{customer_name}' => '' ]
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

		$this->object                          = $order;
		$this->recipient                       = $order->get_billing_email();
		$this->placeholders['{order_number}']  = $order->get_order_number();
		$this->placeholders['{order_date}']    = wc_format_datetime( $order->get_date_created() );
		$this->placeholders['{customer_name}'] = $order->get_billing_first_name();

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	public function get_content_html(): string {
		return wc_get_template_html(
			$this->template_html,
			[ 'order' => $this->object, 'email_heading' => $this->get_heading(), 'sent_to_admin' => false, 'plain_text' => false, 'email' => $this ],
			'', HARMARIUM_EXT_DIR . 'templates/'
		);
	}

	public function get_content_plain(): string {
		return wc_get_template_html(
			$this->template_plain,
			[ 'order' => $this->object, 'email_heading' => $this->get_heading(), 'sent_to_admin' => false, 'plain_text' => true, 'email' => $this ],
			'', HARMARIUM_EXT_DIR . 'templates/'
		);
	}
}
