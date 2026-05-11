<?php
defined( 'ABSPATH' ) || exit;

$wc_statuses  = wc_get_order_statuses();
$status_label = $wc_statuses[ 'wc-' . $new_status ] ?? ucfirst( str_replace( '-', ' ', $new_status ) );

echo "= " . $email_heading . " =\n\n";
printf( __( 'Hi %s,', 'harmarium-ext' ), $order->get_billing_first_name() );
echo "\n\n";
printf(
	__( 'Your order #%1$s status: %2$s', 'harmarium-ext' ),
	$order->get_order_number(),
	$status_label
);
echo "\n\n";
echo __( 'View order:', 'harmarium-ext' ) . ' ' . $order->get_view_order_url() . "\n\n";
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
