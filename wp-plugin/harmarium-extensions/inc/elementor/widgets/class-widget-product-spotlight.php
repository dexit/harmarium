<?php
/**
 * Elementor Widget: Product Spotlight.
 * Displays a single WooCommerce product with image, title, price, and CTA.
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;

class HM_Widget_Product_Spotlight extends \Elementor\Widget_Base {

	public function get_name(): string { return 'hm_product_spotlight'; }
	public function get_title(): string { return esc_html__( 'Product Spotlight', 'harmarium-ext' ); }
	public function get_icon(): string { return 'eicon-featured-image'; }
	public function get_categories(): array { return [ 'harmarium' ]; }
	public function get_keywords(): array { return [ 'product', 'spotlight', 'woocommerce' ]; }

	public function has_widget_inner_wrapper(): bool { return false; }

	protected function register_controls(): void {
		/* ── Content ── */
		$this->start_controls_section( 'section_content', [
			'label' => esc_html__( 'Product', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );
		$this->add_control( 'product_id', [
			'label'       => esc_html__( 'Product', 'harmarium-ext' ),
			'type'        => Controls_Manager::NUMBER,
			'description' => esc_html__( 'Enter the WooCommerce product ID.', 'harmarium-ext' ),
		] );
		$this->add_control( 'show_price', [
			'label'   => esc_html__( 'Show price', 'harmarium-ext' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
		$this->add_control( 'show_excerpt', [
			'label'   => esc_html__( 'Show short description', 'harmarium-ext' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
		$this->add_control( 'cta_label', [
			'label'   => esc_html__( 'Button label', 'harmarium-ext' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'View Artwork', 'harmarium-ext' ),
		] );
		$this->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'    => 'thumbnail',
			'default' => 'woocommerce_single',
		] );
		$this->end_controls_section();

		/* ── Style: Title ── */
		$this->start_controls_section( 'section_style_title', [
			'label' => esc_html__( 'Title', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_control( 'title_color', [
			'label'     => esc_html__( 'Color', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-product-spotlight__title' => 'color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .hm-product-spotlight__title',
		] );
		$this->end_controls_section();

		/* ── Style: Button ── */
		$this->start_controls_section( 'section_style_button', [
			'label' => esc_html__( 'Button', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_control( 'btn_color', [
			'label'     => esc_html__( 'Text', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-product-spotlight__cta' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'btn_bg', [
			'label'     => esc_html__( 'Background', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-product-spotlight__cta' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'btn_border',
			'selector' => '{{WRAPPER}} .hm-product-spotlight__cta',
		] );
		$this->add_responsive_control( 'btn_padding', [
			'label'      => esc_html__( 'Padding', 'harmarium-ext' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [ '{{WRAPPER}} .hm-product-spotlight__cta' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->end_controls_section();
	}

	protected function render(): void {
		$s          = $this->get_settings_for_display();
		$product_id = (int) $s['product_id'];

		if ( ! $product_id ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="elementor-alert">' . esc_html__( 'Enter a product ID in the widget settings.', 'harmarium-ext' ) . '</p>';
			}
			return;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) return;

		$img_html = get_the_post_thumbnail(
			$product_id,
			Group_Control_Image_Size::get_attachment_image_size( $s, 'thumbnail' ) ?? 'woocommerce_single',
			[ 'class' => 'hm-product-spotlight__image', 'loading' => 'lazy' ]
		);
		?>
		<div class="hm-product-spotlight">
			<?php if ( $img_html ) : ?>
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="hm-product-spotlight__img-wrap">
					<?php echo $img_html; // phpcs:ignore ?>
				</a>
			<?php endif; ?>
			<div class="hm-product-spotlight__body">
				<h3 class="hm-product-spotlight__title">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
				</h3>
				<?php if ( 'yes' === $s['show_price'] ) : ?>
					<div class="hm-product-spotlight__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				<?php endif; ?>
				<?php if ( 'yes' === $s['show_excerpt'] && $product->get_short_description() ) : ?>
					<div class="hm-product-spotlight__excerpt"><?php echo wp_kses_post( $product->get_short_description() ); ?></div>
				<?php endif; ?>
				<?php if ( $s['cta_label'] ) : ?>
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="hm-product-spotlight__cta">
						<?php echo esc_html( $s['cta_label'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
