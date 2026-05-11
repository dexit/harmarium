<?php
/**
 * render.php — artwork-spotlight block
 *
 * @var array    $attributes
 * @var WP_Block $block
 */

defined( 'ABSPATH' ) || exit;

$post_id = (int) ( $attributes['postId'] ?? $block->context['postId'] ?? 0 );
if ( ! $post_id ) return;

$post = get_post( $post_id );
if ( ! $post || 'portfolio' !== $post->post_type ) return;

$title     = get_the_title( $post );
$permalink = get_permalink( $post );
$thumb_id  = get_post_thumbnail_id( $post_id );
$thumb     = $thumb_id ? wp_get_attachment_image( $thumb_id, 'large', false, [ 'class' => 'hm-spotlight__img', 'loading' => 'lazy' ] ) : '';

$medium     = get_post_meta( $post_id, '_hm_medium',     true );
$dimensions = get_post_meta( $post_id, '_hm_dimensions', true );
$year       = get_post_meta( $post_id, '_hm_year',       true );
$avail      = get_post_meta( $post_id, '_hm_availability', true );
$excerpt    = get_the_excerpt( $post );

$cta_text = sanitize_text_field( $attributes['ctaText'] ?? 'View artwork' );
$cta_url  = esc_url( $attributes['ctaUrl'] ?? $permalink );
$reversed = ! empty( $attributes['reversed'] ) ? ' hm-spotlight--reversed' : '';

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'hm-artwork-spotlight' . $reversed ] );
?>
<div <?php echo $wrapper_attrs; ?>>
  <?php if ( $thumb ) : ?>
  <div class="hm-spotlight__media" data-harmarium-zoomable>
    <a href="<?php echo esc_url( $permalink ); ?>"><?php echo $thumb; ?></a>
  </div>
  <?php endif; ?>
  <div class="hm-spotlight__body">
    <?php
    $type_terms = get_the_terms( $post_id, 'artwork_type' );
    if ( $type_terms && ! is_wp_error( $type_terms ) ) :
    ?>
    <p class="hm-spotlight__type is-style-harmarium-eyebrow"><?php echo esc_html( $type_terms[0]->name ); ?></p>
    <?php endif; ?>
    <h2 class="hm-spotlight__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h2>
    <?php if ( $medium || $dimensions || $year ) : ?>
    <p class="hm-spotlight__meta">
      <?php
      $meta_parts = array_filter( [ $medium, $dimensions, $year ? (string) $year : '' ] );
      echo esc_html( implode( ' · ', $meta_parts ) );
      ?>
    </p>
    <?php endif; ?>
    <?php if ( $excerpt ) : ?>
    <p class="hm-spotlight__excerpt"><?php echo esc_html( $excerpt ); ?></p>
    <?php endif; ?>
    <a href="<?php echo esc_url( $cta_url ); ?>" class="wp-element-button hm-spotlight__cta"><?php echo esc_html( $cta_text ); ?></a>
  </div>
</div>
