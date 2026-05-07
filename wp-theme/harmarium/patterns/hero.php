<?php
/**
 * Title: Harmarium Hero
 * Slug: harmarium/hero
 * Categories: harmarium-hero
 * Block Types: core/post-content
 */
?>
<!-- wp:cover {"isUserOverlayColor":true,"overlayColor":"ink","minHeight":92,"minHeightUnit":"vh","contentPosition":"center center","align":"full","style":{"color":{"duotone":"var:preset|duotone|ink-canvas"}}} -->
<div class="wp-block-cover alignfull" style="min-height:92vh">
  <span aria-hidden="true" class="wp-block-cover__background has-ink-background-color has-background-dim-30 has-background-dim"></span>
  <img class="wp-block-cover__image-background" alt="" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-background.jpg' ); ?>" data-object-fit="cover" />
  <div class="wp-block-cover__inner-container">
    <!-- wp:heading {"textAlign":"center","level":6,"className":"is-style-harmarium-eyebrow","textColor":"ochre"} -->
    <h6 class="wp-block-heading has-text-align-center is-style-harmarium-eyebrow has-ochre-color has-text-color"><?php echo esc_html__( 'Harmarium · Portrait Art', 'harmarium' ); ?></h6>
    <!-- /wp:heading -->

    <!-- wp:heading {"textAlign":"center","level":1,"textColor":"paper","style":{"typography":{"fontSize":"var:preset|font-size|hero","lineHeight":"0.95","letterSpacing":"-0.02em","fontWeight":"300"}}} -->
    <h1 class="wp-block-heading has-text-align-center has-paper-color has-text-color" style="font-size:var(--wp--preset--font-size--hero);font-weight:300;letter-spacing:-0.02em;line-height:0.95">Faces, in pigment<br><em>and light.</em></h1>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center","textColor":"paper","style":{"typography":{"fontSize":"var:preset|font-size|lg","fontFamily":"var:preset|font-family|display","fontStyle":"italic"}}} -->
    <p class="has-text-align-center has-paper-color has-text-color" style="font-family:var(--wp--preset--font-family--display);font-size:var(--wp--preset--font-size--lg);font-style:italic">Captivating portrait works in acrylic, digital, oil pastel and watercolour. Originals, prints and bespoke commissions from the Harmarium studio.</p>
    <!-- /wp:paragraph -->

    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
    <div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)">
      <!-- wp:button {"backgroundColor":"paper","textColor":"ink"} -->
      <div class="wp-block-button"><a class="wp-block-button__link has-ink-color has-paper-background-color has-text-color has-background wp-element-button" href="/portfolio">Enter the gallery</a></div>
      <!-- /wp:button -->
      <!-- wp:button {"className":"is-style-harmarium-ghost","textColor":"paper"} -->
      <div class="wp-block-button is-style-harmarium-ghost"><a class="wp-block-button__link has-paper-color has-text-color wp-element-button" href="/shop">Shop originals</a></div>
      <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
  </div>
</div>
<!-- /wp:cover -->
