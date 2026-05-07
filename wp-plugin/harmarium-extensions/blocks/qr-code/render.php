<?php
/**
 * render.php — qr-code block
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$post_id    = $block->context['postId'] ?? get_the_ID();
$custom_url = trim( $attributes['customUrl'] ?? '' );
$url        = $custom_url ?: ( $post_id ? get_permalink( $post_id ) : home_url() );
$size       = max( 80, min( 400, (int) ( $attributes['size'] ?? 160 ) ) );
$label      = sanitize_text_field( $attributes['label'] ?? 'Scan to view' );
$show_label = ! empty( $attributes['showLabel'] );

// Module pixel size: target rendered size ÷ (QR modules + 8 quiet zone)
$px = max( 2, (int) round( $size / 33 ) ); // version-1 = 21 modules + 8 quiet

try {
	$svg = Harmarium_QR::svg( $url, $px );
} catch ( \InvalidArgumentException $e ) {
	echo '<!-- harmarium/qr-code: URL too long -->';
	return;
}

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'hm-qr-code' ] );
echo '<figure ' . $wrapper_attrs . '>';
echo $svg; // SVG is generated server-side, no user input reflected unescaped
if ( $show_label && $label ) {
	echo '<figcaption>' . esc_html( $label ) . '</figcaption>';
}
echo '</figure>';
