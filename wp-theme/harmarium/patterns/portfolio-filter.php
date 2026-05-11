<?php
/**
 * Title: Portfolio Filter Bar
 * Slug: harmarium/portfolio-filter
 * Categories: harmarium-gallery
 */

$mediums = get_terms( array( 'taxonomy' => 'artwork_type', 'hide_empty' => true ) );
?>
<!-- wp:group {"align":"wide","className":"harmarium-filter","style":{"spacing":{"margin":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|50"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
<div class="wp-block-group alignwide harmarium-filter" data-harmarium-filter style="margin-top:var(--wp--preset--spacing--40);margin-bottom:var(--wp--preset--spacing--50)">
  <button type="button" class="harmarium-filter__pill is-active" data-filter="*">All</button>
  <?php if ( ! is_wp_error( $mediums ) ) : foreach ( $mediums as $term ) : ?>
    <button type="button" class="harmarium-filter__pill" data-filter="<?php echo esc_attr( $term->slug ); ?>"><?php echo esc_html( $term->name ); ?></button>
  <?php endforeach; endif; ?>
</div>
<!-- /wp:group -->
