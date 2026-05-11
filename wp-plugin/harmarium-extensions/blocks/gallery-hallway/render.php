<?php
/**
 * render.php — gallery-hallway block
 *
 * Renders a tall scroll container; the JS view script handles all 3D rendering.
 *
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$source_url = esc_url_raw( $attributes['sourceUrl'] ?? '' );
if ( ! $source_url ) {
	// Default to local portfolio REST endpoint
	$source_url = rest_url( 'wp/v2/portfolio?per_page=' . absint( $attributes['perPage'] ?? 20 ) . '&_fields=id,title,link,harmarium' );
}

$heading    = sanitize_text_field( $attributes['heading']   ?? 'Walk the gallery' );
$wall_color = sanitize_hex_color( $attributes['wallColor']  ?? '#f0ebe3' ) ?: '#f0ebe3';
$floor_color= sanitize_hex_color( $attributes['floorColor'] ?? '#d4c9b8' ) ?: '#d4c9b8';
$light_color= sanitize_hex_color( $attributes['lightColor'] ?? '#fff8f0' ) ?: '#fff8f0';

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'hm-gallery-hallway' ] );

$config = wp_json_encode( [
	'sourceUrl'  => $source_url,
	'wallColor'  => $wall_color,
	'floorColor' => $floor_color,
	'lightColor' => $light_color,
] );
?>
<div <?php echo $wrapper_attrs; ?>
     data-hm-hallway
     data-hm-hallway-config="<?php echo esc_attr( $config ); ?>">
  <?php if ( $heading ) : ?>
  <p class="hm-gallery-hallway__eyebrow is-style-harmarium-eyebrow"><?php echo esc_html( $heading ); ?></p>
  <?php endif; ?>
  <div class="hm-gallery-hallway__viewport" aria-label="<?php esc_attr_e( 'Virtual gallery hallway', 'harmarium-ext' ); ?>">
    <canvas class="hm-gallery-hallway__canvas"></canvas>
    <div class="hm-gallery-hallway__overlay">
      <div class="hm-gallery-hallway__loading" aria-live="polite">
        <span class="hm-gallery-hallway__spinner"></span>
        <span><?php esc_html_e( 'Loading gallery…', 'harmarium-ext' ); ?></span>
      </div>
    </div>
    <div class="hm-gallery-hallway__tooltip" aria-hidden="true"></div>
  </div>
  <p class="hm-gallery-hallway__hint"><?php esc_html_e( 'Scroll to walk · Click an artwork to view', 'harmarium-ext' ); ?></p>
</div>
