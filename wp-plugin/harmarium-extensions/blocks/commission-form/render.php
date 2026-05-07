<?php
/**
 * render.php — commission-form block
 *
 * @var array $attributes
 */

defined( 'ABSPATH' ) || exit;

$heading      = sanitize_text_field( $attributes['heading']    ?? 'Commission a portrait' );
$subheading   = sanitize_text_field( $attributes['subheading'] ?? '' );
$show_budget  = ! empty( $attributes['showBudget'] );
$show_timeline= ! empty( $attributes['showTimeline'] );

$wrapper_attrs = get_block_wrapper_attributes( [ 'class' => 'hm-commission-form' ] );
?>
<section <?php echo $wrapper_attrs; ?>>
  <?php if ( $heading ) : ?><h2 class="hm-commission-form__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
  <?php if ( $subheading ) : ?><p class="hm-commission-form__sub"><?php echo esc_html( $subheading ); ?></p><?php endif; ?>

  <form class="hm-commission-form__form" novalidate data-hm-commission>
    <div class="hm-commission-form__row hm-commission-form__row--2col">
      <label class="hm-commission-form__field">
        <span><?php esc_html_e( 'Your name', 'harmarium-ext' ); ?> <abbr title="required">*</abbr></span>
        <input type="text" name="name" required autocomplete="name">
      </label>
      <label class="hm-commission-form__field">
        <span><?php esc_html_e( 'Email address', 'harmarium-ext' ); ?> <abbr title="required">*</abbr></span>
        <input type="email" name="email" required autocomplete="email">
      </label>
    </div>

    <label class="hm-commission-form__field">
      <span><?php esc_html_e( 'Subject / artwork idea', 'harmarium-ext' ); ?> <abbr title="required">*</abbr></span>
      <input type="text" name="subject" required placeholder="<?php esc_attr_e( 'e.g. Family portrait, oil on canvas', 'harmarium-ext' ); ?>">
    </label>

    <?php if ( $show_budget || $show_timeline ) : ?>
    <div class="hm-commission-form__row hm-commission-form__row--2col">
      <?php if ( $show_budget ) : ?>
      <label class="hm-commission-form__field">
        <span><?php esc_html_e( 'Approximate budget', 'harmarium-ext' ); ?></span>
        <input type="text" name="budget" placeholder="<?php esc_attr_e( 'e.g. €500–€1000', 'harmarium-ext' ); ?>">
      </label>
      <?php endif; ?>
      <?php if ( $show_timeline ) : ?>
      <label class="hm-commission-form__field">
        <span><?php esc_html_e( 'Desired timeline', 'harmarium-ext' ); ?></span>
        <input type="text" name="timeline" placeholder="<?php esc_attr_e( 'e.g. Ready by December', 'harmarium-ext' ); ?>">
      </label>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <label class="hm-commission-form__field">
      <span><?php esc_html_e( 'Tell me more', 'harmarium-ext' ); ?> <abbr title="required">*</abbr></span>
      <textarea name="message" rows="5" required placeholder="<?php esc_attr_e( 'Size, style references, who it\'s for, any special requests…', 'harmarium-ext' ); ?>"></textarea>
    </label>

    <div class="hm-commission-form__actions">
      <button type="submit" class="wp-element-button">
        <span class="hm-commission-form__label"><?php esc_html_e( 'Send request', 'harmarium-ext' ); ?></span>
        <span class="hm-commission-form__spinner" hidden aria-hidden="true"></span>
      </button>
    </div>

    <div class="hm-commission-form__feedback" role="alert" aria-live="polite"></div>
  </form>
</section>
