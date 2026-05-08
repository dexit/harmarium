<?php
/**
 * Admin email: new commission order received.
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
	/* translators: %s: customer name */
	esc_html__( 'A new commission order has been received from %s. Order details are shown below:', 'harmarium-ext' ),
	esc_html( $order->get_formatted_billing_full_name() )
);
?></p>

<?php do_action( 'woocommerce_email_order_details', $order, true, false, $email ); ?>
<?php do_action( 'woocommerce_email_order_meta', $order, true, false, $email ); ?>
<?php do_action( 'woocommerce_email_customer_details', $order, true, false, $email ); ?>

<?php
$ref_note = $order->get_meta( '_hm_reference_note' );
$ref_img  = $order->get_meta( '_hm_reference_image_id' );
if ( $ref_note || $ref_img ) :
?>
<h2><?php esc_html_e( 'Commission Reference', 'harmarium-ext' ); ?></h2>
<?php if ( $ref_note ) : ?>
<p><?php echo esc_html( $ref_note ); ?></p>
<?php endif; ?>
<?php if ( $ref_img ) : ?>
<p>
	<a href="<?php echo esc_url( (string) wp_get_attachment_url( (int) $ref_img ) ); ?>">
		<?php esc_html_e( 'View reference image →', 'harmarium-ext' ); ?>
	</a>
</p>
<?php endif; ?>
<?php endif; ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
