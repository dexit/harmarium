<?php
/**
 * Register custom WooCommerce email classes.
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_email_classes', function ( array $emails ): array {
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/class-email-commission-received.php';
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/class-email-proof-sent.php';
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/class-email-status-change.php';

	$emails['HM_Email_Commission_Received'] = new HM_Email_Commission_Received();
	$emails['HM_Email_Proof_Sent']          = new HM_Email_Proof_Sent();
	$emails['HM_Email_Status_Change']       = new HM_Email_Status_Change();

	return $emails;
} );

/* ── Add Harmarium email template directory ── */
add_filter( 'woocommerce_template_directory', function ( string $template_dir, string $template ): string {
	if ( str_starts_with( $template, 'emails/harmarium-' ) ) {
		return HARMARIUM_EXT_DIR . 'templates';
	}
	return $template_dir;
}, 10, 2 );

add_filter( 'woocommerce_locate_template', function ( string $template, string $template_name ): string {
	if ( str_starts_with( $template_name, 'emails/harmarium-' ) ) {
		$local = HARMARIUM_EXT_DIR . 'templates/' . $template_name;
		if ( file_exists( $local ) ) {
			return $local;
		}
	}
	return $template;
}, 10, 2 );
