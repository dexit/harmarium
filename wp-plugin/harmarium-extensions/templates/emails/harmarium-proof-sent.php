<?php
/**
 * Customer email: proof is ready for review.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php
printf(
	/* translators: %s: customer first name */
	esc_html__( 'Hi %s,', 'harmarium-ext' ),
	esc_html( $order->get_billing_first_name() )
);
?></p>

<p><?php esc_html_e( 'Your artwork proof for the commission below is ready for review. Please log in to your account to approve the proof or request revisions.', 'harmarium-ext' ); ?></p>

<?php
$proof_url = $order->get_meta( '_hm_proof_url' );
if ( $proof_url ) :
?>
<p>
	<a href="<?php echo esc_url( $proof_url ); ?>" style="display:inline-block;padding:12px 24px;background:#1a1a1a;color:#fff;text-decoration:none;border-radius:4px;">
		<?php esc_html_e( 'View your proof', 'harmarium-ext' ); ?>
	</a>
</p>
<?php endif; ?>

<p>
	<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'proof-approval' ) ); ?>">
		<?php esc_html_e( 'Go to your proof approval page →', 'harmarium-ext' ); ?>
	</a>
</p>

<?php do_action( 'woocommerce_email_order_details', $order, false, false, $email ); ?>

<p><?php esc_html_e( 'Once you approve, we will begin production. If you have any questions, simply reply to this email.', 'harmarium-ext' ); ?></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
