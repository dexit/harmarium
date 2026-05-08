<?php
/**
 * Elementor Widget: Portfolio Loop.
 * Renders a grid of portfolio items with filterable availability/medium.
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;

class HM_Widget_Portfolio_Loop extends \Elementor\Widget_Base {

	public function get_name(): string { return 'hm_portfolio_loop'; }
	public function get_title(): string { return esc_html__( 'Portfolio Loop', 'harmarium-ext' ); }
	public function get_icon(): string { return 'eicon-gallery-grid'; }
	public function get_categories(): array { return [ 'harmarium' ]; }
	public function get_keywords(): array { return [ 'portfolio', 'loop', 'grid', 'artworks' ]; }

	public function has_widget_inner_wrapper(): bool { return false; }

	protected function register_controls(): void {
		/* ── Query ── */
		$this->start_controls_section( 'section_query', [
			'label' => esc_html__( 'Query', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );
		$this->add_control( 'posts_per_page', [
			'label'   => esc_html__( 'Items per page', 'harmarium-ext' ),
			'type'    => Controls_Manager::NUMBER,
			'default' => 12,
			'min'     => 1,
			'max'     => 48,
		] );
		$this->add_control( 'availability_filter', [
			'label'    => esc_html__( 'Filter by availability', 'harmarium-ext' ),
			'type'     => Controls_Manager::SELECT,
			'options'  => [
				''          => esc_html__( 'All', 'harmarium-ext' ),
				'available' => esc_html__( 'Available only', 'harmarium-ext' ),
				'sold'      => esc_html__( 'Sold', 'harmarium-ext' ),
				'nfs'       => esc_html__( 'Not for Sale', 'harmarium-ext' ),
			],
			'default'  => '',
		] );
		$this->add_control( 'orderby', [
			'label'   => esc_html__( 'Order by', 'harmarium-ext' ),
			'type'    => Controls_Manager::SELECT,
			'options' => [ 'date' => 'Date', 'title' => 'Title', 'menu_order' => 'Menu Order', 'rand' => 'Random' ],
			'default' => 'date',
		] );
		$this->end_controls_section();

		/* ── Layout ── */
		$this->start_controls_section( 'section_layout', [
			'label' => esc_html__( 'Layout', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );
		$this->add_responsive_control( 'columns', [
			'label'          => esc_html__( 'Columns', 'harmarium-ext' ),
			'type'           => Controls_Manager::SELECT,
			'options'        => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ],
			'default'        => '3',
			'tablet_default' => '2',
			'mobile_default' => '1',
			'selectors'      => [ '{{WRAPPER}} .hm-portfolio-loop' => 'grid-template-columns: repeat({{VALUE}}, 1fr);' ],
		] );
		$this->add_responsive_control( 'gap', [
			'label'     => esc_html__( 'Gap', 'harmarium-ext' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 80 ] ],
			'default'   => [ 'size' => 24 ],
			'selectors' => [ '{{WRAPPER}} .hm-portfolio-loop' => 'gap: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Image_Size::get_type(), [
			'name'    => 'thumbnail',
			'default' => 'large',
		] );
		$this->add_control( 'show_title', [
			'label'   => esc_html__( 'Show title', 'harmarium-ext' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
		$this->add_control( 'show_meta', [
			'label'   => esc_html__( 'Show meta (medium / availability)', 'harmarium-ext' ),
			'type'    => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
		$this->end_controls_section();

		/* ── Style: Card ── */
		$this->start_controls_section( 'section_style_card', [
			'label' => esc_html__( 'Card', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_responsive_control( 'border_radius', [
			'label'      => esc_html__( 'Border radius', 'harmarium-ext' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', '%' ],
			'selectors'  => [ '{{WRAPPER}} .hm-portfolio-loop__item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'card_shadow',
			'selector' => '{{WRAPPER}} .hm-portfolio-loop__item',
		] );
		$this->end_controls_section();

		/* ── Style: Title ── */
		$this->start_controls_section( 'section_style_title', [
			'label'     => esc_html__( 'Title', 'harmarium-ext' ),
			'tab'       => Controls_Manager::TAB_STYLE,
			'condition' => [ 'show_title' => 'yes' ],
		] );
		$this->add_control( 'title_color', [
			'label'     => esc_html__( 'Color', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-portfolio-loop__title' => 'color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'title_typography',
			'selector' => '{{WRAPPER}} .hm-portfolio-loop__title',
		] );
		$this->end_controls_section();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();

		$args = [
			'post_type'      => 'portfolio',
			'posts_per_page' => (int) $s['posts_per_page'],
			'orderby'        => sanitize_key( $s['orderby'] ),
			'order'          => 'DESC',
		];

		if ( ! empty( $s['availability_filter'] ) ) {
			$args['meta_query'] = [ [ 'key' => '_hm_availability', 'value' => $s['availability_filter'] ] ];
		}

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			echo '<p class="hm-portfolio-loop--empty">' . esc_html__( 'No artworks found.', 'harmarium-ext' ) . '</p>';
			return;
		}

		$avail_labels = [
			'available' => __( 'Available', 'harmarium-ext' ),
			'sold'      => __( 'Sold', 'harmarium-ext' ),
			'reserved'  => __( 'Reserved', 'harmarium-ext' ),
			'nfs'       => __( 'Not for Sale', 'harmarium-ext' ),
		];

		echo '<div class="hm-portfolio-loop">';
		while ( $query->have_posts() ) :
			$query->the_post();
			$post_id     = get_the_ID();
			$availability = get_post_meta( $post_id, '_hm_availability', true );
			$medium      = get_post_meta( $post_id, '_hm_medium', true );
			$thumb_html  = get_the_post_thumbnail( $post_id, Group_Control_Image_Size::get_attachment_image_size( $s, 'thumbnail' ) ?? 'large', [ 'class' => 'hm-portfolio-loop__image', 'loading' => 'lazy' ] );
			?>
			<article class="hm-portfolio-loop__item hm-availability-<?php echo esc_attr( $availability ?: 'unknown' ); ?>">
				<a href="<?php the_permalink(); ?>" class="hm-portfolio-loop__link" aria-label="<?php the_title_attribute(); ?>">
					<?php echo $thumb_html; // phpcs:ignore ?>
					<?php if ( 'available' === $availability ) : ?>
						<span class="hm-portfolio-loop__badge hm-badge--available"><?php esc_html_e( 'Available', 'harmarium-ext' ); ?></span>
					<?php endif; ?>
				</a>
				<?php if ( 'yes' === $s['show_title'] ) : ?>
					<h3 class="hm-portfolio-loop__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php endif; ?>
				<?php if ( 'yes' === $s['show_meta'] && ( $medium || $availability ) ) : ?>
					<p class="hm-portfolio-loop__meta">
						<?php if ( $medium ) echo esc_html( $medium ); ?>
						<?php if ( $medium && $availability ) echo ' · '; ?>
						<?php if ( $availability ) echo esc_html( $avail_labels[ $availability ] ?? $availability ); ?>
					</p>
				<?php endif; ?>
			</article>
			<?php
		endwhile;
		wp_reset_postdata();
		echo '</div>';
	}
}
