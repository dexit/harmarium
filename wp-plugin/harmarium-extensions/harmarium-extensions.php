<?php
/**
 * Plugin Name:  Harmarium Extensions
 * Plugin URI:   https://harmarium.com
 * Description:  Custom blocks, commission/quote requests, WooCommerce order statuses, Elementor widgets & dynamic tags, email templates, logging, lead tracking, cookie consent, and REST endpoints for Harmarium.
 * Version:      2.0.0
 * Author:       Harmarium
 * Author URI:   https://harmarium.com
 * License:      GPL-2.0-or-later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  harmarium-ext
 * Requires PHP: 8.1
 * Requires at least: 6.4
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 * WC tested up to: 9.9
 */

defined( 'ABSPATH' ) || exit;

define( 'HARMARIUM_EXT_VERSION', '2.0.0' );
define( 'HARMARIUM_EXT_DIR', plugin_dir_path( __FILE__ ) );
define( 'HARMARIUM_EXT_URI', plugin_dir_url( __FILE__ ) );

/* ── HPOS + Cart/Checkout blocks compatibility (declared before WC loads) ── */
add_action( 'before_woocommerce_init', function (): void {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );

/* ── Core libraries (always loaded) ── */
require_once HARMARIUM_EXT_DIR . 'lib/qr-svg.php';

/* ── Logging must load first so hm_log() is available to other modules ── */
require_once HARMARIUM_EXT_DIR . 'inc/logging.php';

/* ── Core modules ── */
require_once HARMARIUM_EXT_DIR . 'inc/meta-fields.php';
require_once HARMARIUM_EXT_DIR . 'inc/commission.php';
require_once HARMARIUM_EXT_DIR . 'inc/bridges.php';
require_once HARMARIUM_EXT_DIR . 'inc/rest-api.php';
require_once HARMARIUM_EXT_DIR . 'inc/blocks.php';
require_once HARMARIUM_EXT_DIR . 'inc/leads.php';
require_once HARMARIUM_EXT_DIR . 'inc/policies.php';
require_once HARMARIUM_EXT_DIR . 'inc/cookie-consent.php';

/* ── WooCommerce modules (load after WC is available) ── */
add_action( 'woocommerce_loaded', function (): void {
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/order-statuses.php';
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/checkout.php';
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/shipping.php';
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/my-account.php';
	require_once HARMARIUM_EXT_DIR . 'inc/woocommerce/emails.php';
} );

/* ── Elementor integration (load after Elementor is available) ── */
add_action( 'elementor/init', function (): void {
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/loader.php';

	// Register Harmarium widget category
	\Elementor\Plugin::$instance->elements_manager->add_category(
		'harmarium',
		[ 'title' => esc_html__( 'Harmarium', 'harmarium-ext' ), 'icon' => 'eicon-paint-brush' ],
		1
	);
} );

/* ── Register dynamic tag group ── */
add_action( 'elementor/dynamic_tags/before_render', function (): void {
	if ( ! did_action( 'elementor/init' ) ) return;
	\Elementor\Plugin::$instance->dynamic_tags->register_group(
		'harmarium',
		[ 'title' => esc_html__( 'Harmarium', 'harmarium-ext' ) ]
	);
} );

/* ── Enqueue frontend assets ── */
add_action( 'wp_enqueue_scripts', function (): void {
	wp_enqueue_style(
		'hm-frontend',
		HARMARIUM_EXT_URI . 'assets/css/frontend.css',
		[],
		HARMARIUM_EXT_VERSION
	);

	wp_register_script(
		'hm-commission-widget',
		HARMARIUM_EXT_URI . 'assets/js/commission-widget.js',
		[],
		HARMARIUM_EXT_VERSION,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);
} );

/* ── Enqueue admin assets ── */
add_action( 'admin_enqueue_scripts', function (): void {
	wp_enqueue_style(
		'hm-admin',
		HARMARIUM_EXT_URI . 'assets/css/admin.css',
		[],
		HARMARIUM_EXT_VERSION
	);
} );

/* ── Activation: create DB tables, seed policy pages ── */
register_activation_hook( __FILE__, 'harmarium_ext_activate' );

function harmarium_ext_activate(): void {
	harmarium_log_create_table();
	harmarium_leads_create_table();

	// Flush rewrite rules after adding My Account endpoints
	flush_rewrite_rules();

	// Policy pages require WP to be mostly booted — schedule for shutdown
	add_action( 'shutdown', function (): void {
		if ( function_exists( 'harmarium_create_policy_pages' ) ) {
			harmarium_create_policy_pages();
		}
	} );
}

/* ── Deactivation ── */
register_deactivation_hook( __FILE__, function (): void {
	flush_rewrite_rules();
} );

/* ── Upgrade: create/update DB tables if version changed ── */
add_action( 'plugins_loaded', function (): void {
	if ( get_option( 'hm_log_table_version' ) !== HARMARIUM_LOG_TABLE_VERSION ) {
		harmarium_log_create_table();
	}
	if ( get_option( 'hm_leads_table_version' ) !== HARMARIUM_LEADS_TABLE_VERSION ) {
		harmarium_leads_create_table();
	}
} );

/* ── Hook lead tracking into commission REST submission ── */
add_filter( 'harmarium_after_commission_insert', function ( int $post_id, array $data, WP_REST_Request $req ): void {
	$extra = apply_filters( 'harmarium_commission_rest_extra', [], $req );
	hm_track_lead( $post_id, $data['email'], $extra );
	hm_log( 'commission_submitted', sprintf( 'Commission #%d submitted by %s', $post_id, $data['email'] ), [
		'commission_id' => $post_id,
		'email_hash'    => hash( 'sha256', strtolower( trim( $data['email'] ) ) ),
	] );
}, 10, 3 );
