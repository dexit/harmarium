<?php
/**
 * LD+JSON structured data.
 *
 * Emits Schema.org graphs in <head>:
 *  - Organization + WebSite (sitewide)
 *  - VisualArtwork for portfolio singles
 *  - Product / Offer for WooCommerce singles (richer than core defaults,
 *    falls back when Yoast / Rank Math aren't present)
 *  - CollectionPage + ItemList for archives & exhibitions
 *  - SearchAction for the search page
 *  - BreadcrumbList sitewide
 *
 * If Yoast SEO or Rank Math is active, our extra graphs still emit but are
 * scoped (no duplicate WebSite / Organization) — they augment rather than
 * conflict. Toggle off via the `harmarium_emit_ldjson` filter if needed.
 *
 * @package Harmarium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function harmarium_emit_ldjson() {
	if ( ! apply_filters( 'harmarium_emit_ldjson', true ) ) {
		return;
	}

	$has_seo_plugin = defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' );
	$graph          = array();
	$site_url       = home_url( '/' );

	if ( ! $has_seo_plugin ) {
		$graph[] = array(
			'@type' => 'Organization',
			'@id'   => $site_url . '#org',
			'name'  => get_bloginfo( 'name' ),
			'url'   => $site_url,
			'logo'  => has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : null,
		);
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => $site_url . '#site',
			'url'             => $site_url,
			'name'            => get_bloginfo( 'name' ),
			'description'     => get_bloginfo( 'description' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => $site_url . '?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	// Breadcrumbs.
	$crumbs = harmarium_breadcrumbs();
	if ( $crumbs ) {
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array_map( function ( $c, $i ) {
				return array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $c['name'],
					'item'     => $c['url'],
				);
			}, $crumbs, array_keys( $crumbs ) ),
		);
	}

	if ( is_singular( 'portfolio' ) ) {
		$post  = get_queried_object();
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'full' );
		$mediums = wp_get_post_terms( $post->ID, 'artwork_type', array( 'fields' => 'names' ) );
		$graph[] = array_filter( array(
			'@type'        => 'VisualArtwork',
			'@id'          => get_permalink( $post ) . '#artwork',
			'name'         => get_the_title( $post ),
			'description'  => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'image'        => $thumb ? $thumb[0] : null,
			'url'          => get_permalink( $post ),
			'artMedium'    => $mediums ? implode( ', ', $mediums ) : null,
			'artform'      => 'Portrait',
			'creator'      => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
			),
			'dateCreated'  => get_the_date( 'c', $post ),
		) );
	} elseif ( is_singular( 'product' ) && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$graph[] = array_filter( array(
				'@type'       => 'Product',
				'@id'         => get_permalink( $product->get_id() ) . '#product',
				'name'        => $product->get_name(),
				'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
				'sku'         => $product->get_sku() ?: null,
				'image'       => wp_get_attachment_image_url( $product->get_image_id(), 'full' ),
				'url'         => get_permalink( $product->get_id() ),
				'brand'       => array( '@type' => 'Brand', 'name' => get_bloginfo( 'name' ) ),
				'offers'      => array_filter( array(
					'@type'         => 'Offer',
					'price'         => $product->get_price() !== '' ? (float) $product->get_price() : null,
					'priceCurrency' => get_woocommerce_currency(),
					'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
					'url'           => get_permalink( $product->get_id() ),
				) ),
			) );
		}
	} elseif ( is_singular( 'exhibition' ) ) {
		$post  = get_queried_object();
		$items = (array) get_post_meta( $post->ID, '_harmarium_exhibition_items', true );
		$opens = get_post_meta( $post->ID, '_harmarium_opens_at', true );
		$close = get_post_meta( $post->ID, '_harmarium_closes_at', true );
		$graph[] = array_filter( array(
			'@type'       => 'ExhibitionEvent',
			'@id'         => get_permalink( $post ) . '#exhibition',
			'name'        => get_the_title( $post ),
			'description' => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'url'         => get_permalink( $post ),
			'startDate'   => $opens ?: null,
			'endDate'     => $close ?: null,
			'organizer'   => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ) ),
			'workFeatured'=> array_values( array_filter( array_map( function ( $pid ) {
				$pid = (int) $pid;
				if ( ! $pid || get_post_status( $pid ) !== 'publish' ) {
					return null;
				}
				return array(
					'@type' => 'VisualArtwork',
					'name'  => get_the_title( $pid ),
					'url'   => get_permalink( $pid ),
					'image' => wp_get_attachment_image_url( get_post_thumbnail_id( $pid ), 'full' ),
				);
			}, $items ) ) ),
		) );
	} elseif ( is_post_type_archive( 'portfolio' ) || is_tax( array( 'artwork_type', 'portfolio_category', 'portfolio_tag' ) ) || ( function_exists( 'is_shop' ) && is_shop() ) || is_post_type_archive( 'exhibition' ) ) {
		global $wp_query;
		$items = array();
		$pos   = 0;
		foreach ( (array) $wp_query->posts as $p ) {
			$pos++;
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos,
				'url'      => get_permalink( $p ),
				'name'     => get_the_title( $p ),
				'image'    => wp_get_attachment_image_url( get_post_thumbnail_id( $p ), 'harmarium-square' ),
			);
		}
		$graph[] = array(
			'@type'           => 'CollectionPage',
			'@id'             => home_url( add_query_arg( null, null ) ) . '#collection',
			'name'            => wp_get_document_title(),
			'url'             => home_url( add_query_arg( null, null ) ),
			'mainEntity'      => array(
				'@type'           => 'ItemList',
				'numberOfItems'   => count( $items ),
				'itemListElement' => $items,
			),
		);
	} elseif ( is_search() ) {
		$graph[] = array(
			'@type' => 'SearchResultsPage',
			'name'  => sprintf( __( 'Search results for "%s"', 'harmarium' ), get_search_query() ),
			'url'   => home_url( add_query_arg( null, null ) ),
		);
	}

	$graph = array_values( array_filter( $graph ) );
	if ( ! $graph ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}
add_action( 'wp_head', 'harmarium_emit_ldjson', 99 );

/**
 * Build a simple breadcrumb trail.
 */
function harmarium_breadcrumbs() {
	$crumbs = array( array( 'name' => __( 'Home', 'harmarium' ), 'url' => home_url( '/' ) ) );

	if ( is_singular() ) {
		$post = get_queried_object();
		$pt   = get_post_type_object( $post->post_type );
		if ( $pt && ! empty( $pt->has_archive ) ) {
			$crumbs[] = array( 'name' => $pt->labels->name, 'url' => get_post_type_archive_link( $post->post_type ) );
		}
		$crumbs[] = array( 'name' => get_the_title( $post ), 'url' => get_permalink( $post ) );
	} elseif ( is_post_type_archive() ) {
		$pt = get_queried_object();
		$crumbs[] = array( 'name' => $pt->labels->name, 'url' => get_post_type_archive_link( $pt->name ) );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		$crumbs[] = array( 'name' => $term->name, 'url' => get_term_link( $term ) );
	} elseif ( is_search() ) {
		$crumbs[] = array( 'name' => __( 'Search', 'harmarium' ), 'url' => home_url( '/?s=' . urlencode( get_search_query() ) ) );
	} elseif ( is_404() ) {
		$crumbs[] = array( 'name' => __( '404', 'harmarium' ), 'url' => home_url( add_query_arg( null, null ) ) );
	}
	return count( $crumbs ) > 1 ? $crumbs : array();
}
