<?php
/**
 * render.php — artwork-meta block
 *
 * @var array    $attributes Block attributes
 * @var string   $content    Inner block content
 * @var WP_Block $block      Block instance
 */

defined( 'ABSPATH' ) || exit;

$post_id = $block->context['postId'] ?? get_the_ID();
if ( ! $post_id ) return;

$fields = [
	'medium'     => [ 'label' => 'Medium',     'meta' => '_hm_medium' ],
	'dimensions' => [ 'label' => 'Dimensions', 'meta' => '_hm_dimensions' ],
	'year'       => [ 'label' => 'Year',        'meta' => '_hm_year' ],
	'edition'    => [ 'label' => 'Edition',     'meta' => '_hm_edition' ],
];

if ( ! empty( $attributes['showCertificate'] ) ) {
	$fields['certificate_nr'] = [ 'label' => 'Certificate Nr.', 'meta' => '_hm_certificate_nr' ];
}

$rows = '';
foreach ( $fields as $slug => $f ) {
	$val = get_post_meta( $post_id, $f['meta'], true );
	if ( ! $val ) continue;
	$rows .= '<dt>' . esc_html( $f['label'] ) . '</dt><dd>' . esc_html( $val ) . '</dd>';
}

if ( ! empty( $attributes['showAvailability'] ) ) {
	$avail = get_post_meta( $post_id, '_hm_availability', true );
	if ( $avail ) {
		$label_map = [
			'available' => 'Available',
			'sold'      => 'Sold',
			'reserved'  => 'Reserved',
			'nfs'       => 'Not for Sale',
		];
		$label = $label_map[ $avail ] ?? ucfirst( $avail );
		$rows .= '<dt>Availability</dt><dd><span class="hm-avail-badge hm-avail-' . esc_attr( $avail ) . '">' . esc_html( $label ) . '</span></dd>';
	}
}

if ( ! $rows ) return;

$price = get_post_meta( $post_id, '_hm_price_display', true );
if ( $price ) {
	$rows = '<dt>Price</dt><dd class="hm-artwork-price">' . esc_html( $price ) . '</dd>' . $rows;
}

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'hm-artwork-meta' ] );
echo '<dl ' . $wrapper_attrs . '>' . $rows . '</dl>';
