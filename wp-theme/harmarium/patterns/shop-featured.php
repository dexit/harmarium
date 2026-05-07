<?php
/**
 * Title: Featured Shop Pieces
 * Slug: harmarium/shop-featured
 * Categories: harmarium-shop
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"canvas","layout":{"type":"constrained","contentSize":"720px","wideSize":"1440px"}} -->
<div class="wp-block-group alignfull has-canvas-background-color has-background" style="padding:var(--wp--preset--spacing--70) var(--wp--preset--spacing--40)">
  <!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
  <div class="wp-block-group">
    <!-- wp:group -->
    <div class="wp-block-group">
      <!-- wp:heading {"level":6,"className":"is-style-harmarium-eyebrow"} --><h6 class="wp-block-heading is-style-harmarium-eyebrow">Acquire</h6><!-- /wp:heading -->
      <!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Featured originals</h2><!-- /wp:heading -->
    </div>
    <!-- /wp:group -->
    <!-- wp:paragraph --><p><a href="/shop">Visit gallery shop →</a></p><!-- /wp:paragraph -->
  </div>
  <!-- /wp:group -->

  <!-- wp:woocommerce/featured-products {"columns":4,"rows":1,"contentVisibility":{"image":true,"title":true,"price":true,"rating":false,"button":true}} /-->
</div>
<!-- /wp:group -->
