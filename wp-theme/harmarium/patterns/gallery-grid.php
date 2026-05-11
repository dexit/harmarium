<?php
/**
 * Title: Gallery Grid
 * Slug: harmarium/gallery-grid
 * Categories: harmarium-gallery
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}}},"backgroundColor":"paper","layout":{"type":"constrained","contentSize":"720px","wideSize":"1440px"}} -->
<div class="wp-block-group alignfull has-paper-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--40)">
  <!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
  <div class="wp-block-group">
    <!-- wp:heading {"level":2} --><h2 class="wp-block-heading">Latest from the studio</h2><!-- /wp:heading -->
    <!-- wp:paragraph --><p><a href="/portfolio">View all artworks →</a></p><!-- /wp:paragraph -->
  </div>
  <!-- /wp:group -->

  <!-- wp:query {"queryId":11,"query":{"perPage":8,"postType":"portfolio","order":"desc","orderBy":"date"},"align":"wide"} -->
  <div class="wp-block-query alignwide" data-harmarium-gallery>
    <!-- wp:post-template {"layout":{"type":"grid","columnCount":4},"className":"harmarium-gallery"} -->
      <!-- wp:group {"className":"harmarium-gallery__item is-style-harmarium-card"} -->
      <div class="wp-block-group harmarium-gallery__item is-style-harmarium-card">
        <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/5","className":"is-style-harmarium-zoom","data-harmarium-zoomable":true} /-->
        <!-- wp:post-title {"isLink":true,"level":4,"style":{"typography":{"fontSize":"var:preset|font-size|sm","fontWeight":"500"}}} /-->
      </div>
      <!-- /wp:group -->
    <!-- /wp:post-template -->
  </div>
  <!-- /wp:query -->
</div>
<!-- /wp:group -->
