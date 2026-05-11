<?php
/**
 * Auto-create policy pages on plugin activation:
 *  - Privacy Policy
 *  - Terms & Conditions
 *  - Cookie Policy
 *  - Shipping & Returns
 */

defined( 'ABSPATH' ) || exit;

function harmarium_create_policy_pages(): void {
	$pages = [
		[
			'slug'    => 'privacy-policy',
			'title'   => __( 'Privacy Policy', 'harmarium-ext' ),
			'content' => harmarium_privacy_policy_content(),
		],
		[
			'slug'    => 'terms-conditions',
			'title'   => __( 'Terms & Conditions', 'harmarium-ext' ),
			'content' => harmarium_terms_content(),
		],
		[
			'slug'    => 'cookie-policy',
			'title'   => __( 'Cookie Policy', 'harmarium-ext' ),
			'content' => harmarium_cookie_policy_content(),
		],
		[
			'slug'    => 'shipping-returns',
			'title'   => __( 'Shipping & Returns', 'harmarium-ext' ),
			'content' => harmarium_shipping_returns_content(),
		],
	];

	foreach ( $pages as $page ) {
		$existing = get_page_by_path( $page['slug'] );
		if ( $existing ) {
			continue;
		}
		$id = wp_insert_post( [
			'post_title'   => $page['title'],
			'post_name'    => $page['slug'],
			'post_content' => $page['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
		] );
		if ( ! is_wp_error( $id ) ) {
			hm_log( 'policy_page_created', sprintf( 'Created policy page: %s (ID: %d)', $page['slug'], $id ) );
		}
	}

	// Wire up WP privacy page
	$privacy = get_page_by_path( 'privacy-policy' );
	if ( $privacy && ! get_option( 'wp_page_for_privacy_policy' ) ) {
		update_option( 'wp_page_for_privacy_policy', $privacy->ID );
	}

	// Wire up WooCommerce pages
	$wc_map = [
		'woocommerce_terms_page_id'   => 'terms-conditions',
		'woocommerce_privacy_page_id' => 'privacy-policy',
	];
	foreach ( $wc_map as $option => $slug ) {
		if ( ! get_option( $option ) ) {
			$page = get_page_by_path( $slug );
			if ( $page ) update_option( $option, $page->ID );
		}
	}
}

function harmarium_privacy_policy_content(): string {
	return '<!-- wp:heading --><h2>Privacy Policy</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>This Privacy Policy explains how Harmarium collects, uses, and protects your personal information when you use our website and services.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Information We Collect</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>We collect information you provide directly to us, such as when you submit a commission request, place an order, or contact us. This may include your name, email address, postal address, and payment information.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>How We Use Your Information</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>We use the information we collect to process your orders, fulfil commission requests, send order updates and notifications, and improve our services.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Data Retention</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>We retain your personal data for as long as necessary to fulfil the purposes for which it was collected, including for legal, accounting, or reporting requirements.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Your Rights</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>You have the right to access, correct, or delete your personal data at any time. Please contact us at [your email] to exercise these rights.</p><!-- /wp:paragraph -->';
}

function harmarium_terms_content(): string {
	return '<!-- wp:heading --><h2>Terms &amp; Conditions</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>By accessing this website and placing orders, you agree to be bound by these Terms and Conditions.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Commission Orders</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>All commission orders are subject to a proof approval stage. Work begins only after written approval of the proof. Changes after approval may incur additional charges.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Payment</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Full payment is required at the time of ordering. All prices are inclusive of applicable taxes unless otherwise stated.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Intellectual Property</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>All artwork remains the intellectual property of Harmarium. Purchase grants the buyer a licence for personal display only. Commercial use requires separate written agreement.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Limitation of Liability</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Harmarium shall not be liable for any indirect, incidental, or consequential damages arising from the purchase or use of our products.</p><!-- /wp:paragraph -->';
}

function harmarium_cookie_policy_content(): string {
	return '<!-- wp:heading --><h2>Cookie Policy</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>This website uses cookies to improve your experience. By continuing to browse the site, you agree to our use of cookies.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>What Are Cookies</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Cookies are small text files stored on your device when you visit a website. They help the site remember your preferences and improve your experience.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Types of Cookies We Use</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>Essential cookies:</strong> Required for the site to function, such as shopping cart and login session cookies.</li><li><strong>Analytics cookies:</strong> Help us understand how visitors use the site (only with your consent).</li><li><strong>Preference cookies:</strong> Remember your settings and preferences.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Managing Cookies</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>You can control cookies through our cookie consent banner or through your browser settings. Note that disabling some cookies may affect site functionality.</p><!-- /wp:paragraph -->';
}

function harmarium_shipping_returns_content(): string {
	return '<!-- wp:heading --><h2>Shipping &amp; Returns</h2><!-- /wp:heading -->
<!-- wp:heading {"level":3} --><h3>Shipping</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>All artworks are carefully packaged to ensure safe delivery. We offer the following shipping options:</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li><strong>Collect from Studio:</strong> Free, by appointment. We will contact you to arrange a collection time.</li><li><strong>White Glove Delivery:</strong> Insured, climate-controlled delivery with optional installation. Price on enquiry.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Commission Delivery</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Commission pieces are delivered upon completion and after proof approval. Estimated timelines are provided at the quotation stage and are indicative only.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Returns</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Due to the bespoke nature of commissioned artwork, we are unable to accept returns on commission pieces unless the work is materially different from the approved proof.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>For prints and other non-commission items, please contact us within 14 days of receipt if you wish to return your purchase.</p><!-- /wp:paragraph -->';
}
