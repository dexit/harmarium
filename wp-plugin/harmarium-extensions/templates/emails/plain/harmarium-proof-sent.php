<?php
defined( 'ABSPATH' ) || exit;

echo "= " . $email_heading . " =\n\n";
printf( __( 'Hi %s,', 'harmarium-ext' ), $order->get_billing_first_name() );
echo "\n\n";
echo __( 'Your artwork proof is ready for review.', 'harmarium-ext' ) . "\n\n";
$proof_url = $order->get_meta( '_hm_proof_url' );
if ( $proof_url ) echo __( 'View proof:', 'harmarium-ext' ) . ' ' . $proof_url . "\n\n";
echo __( 'Approve or request revisions:', 'harmarium-ext' ) . ' ' . wc_get_account_endpoint_url( 'proof-approval' ) . "\n\n";
do_action( 'woocommerce_email_order_details', $order, false, true, $email );
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
