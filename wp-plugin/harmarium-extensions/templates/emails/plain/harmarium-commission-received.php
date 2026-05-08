<?php
defined( 'ABSPATH' ) || exit;

echo "= " . $email_heading . " =\n\n";
printf( __( 'New commission from: %s', 'harmarium-ext' ), $order->get_formatted_billing_full_name() );
echo "\n\n";
do_action( 'woocommerce_email_order_details', $order, true, true, $email );
do_action( 'woocommerce_email_order_meta', $order, true, true, $email );
do_action( 'woocommerce_email_customer_details', $order, true, true, $email );
$ref = $order->get_meta( '_hm_reference_note' );
if ( $ref ) echo "\n" . __( 'Reference notes:', 'harmarium-ext' ) . "\n" . $ref . "\n";
$img = $order->get_meta( '_hm_reference_image_id' );
if ( $img ) echo "\n" . __( 'Reference image:', 'harmarium-ext' ) . ' ' . wp_get_attachment_url( (int) $img ) . "\n";
echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
