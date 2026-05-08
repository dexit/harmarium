<?php
/**
 * Customer email: commission order status change.
 *
 * @var WC_Order $order
 * @var string   $email_heading
 * @var string   $new_status
 * @var WC_Email $email
 */

defined( 'ABSPATH' ) || exit;

$wc_statuses  = wc_get_order_statuses();
$status_label = $wc_statuses[ 'wc-' . $new_status ] ?? ucfirst( str_replace( '-', ' ', $new_status ) );

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php
printf(
	esc_html__( 'Hi %s,', 'harmarium-ext' ),
	esc_html( $order->get_billing_first_name() )
);
?></p>

<p><?php
printf(
	/* translators: 1: order number, 2: status */
	esc_html__( 'Your commission order #%1$s has been updated. The new status is: %2$s.', 'harmarium-ext' ),
	esc_html( $order->get_order_number() ),
	'<strong>' . esc_html( $status_label ) . '</strong>'
);
?></p>

<?php
$messages = [
	'proof-approved' => __( 'Your proof has been marked as approved. We will begin production shortly and keep you updated.', 'harmarium-ext' ),
	'in-production'  => __( 'Your artwork is now in production. We will notify you when it is ready to ship.', 'harmarium-ext' ),
	'ready-ship'     => __( 'Your artwork is ready to ship! You will receive shipping information shortly.', 'harmarium-ext' ),
];

if ( isset( $messages[ $new_status ] ) ) :
?>
<p><?php echo esc_html( $messages[ $new_status ] ); ?></p>
<?php endif; ?>

<p>
	<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
		<?php esc_html_e( 'View your order →', 'harmarium-ext' ); ?>
	</a>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
