<?php
/**
 * Elementor Widget: Artwork Meta Table.
 * Displays medium, dimensions, year, edition, availability, and price for a portfolio item.
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

class HM_Widget_Artwork_Meta extends \Elementor\Widget_Base {

	public function get_name(): string { return 'hm_artwork_meta'; }
	public function get_title(): string { return esc_html__( 'Artwork Meta', 'harmarium-ext' ); }
	public function get_icon(): string { return 'eicon-table'; }
	public function get_categories(): array { return [ 'harmarium' ]; }
	public function get_keywords(): array { return [ 'artwork', 'meta', 'portfolio', 'medium', 'dimensions' ]; }

	public function has_widget_inner_wrapper(): bool { return false; }

	protected function register_controls(): void {
		/* ── Content ── */
		$this->start_controls_section( 'section_content', [
			'label' => esc_html__( 'Fields', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );

		$this->add_control( 'show_medium',       [ 'label' => esc_html__( 'Medium', 'harmarium-ext' ),       'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_dimensions',   [ 'label' => esc_html__( 'Dimensions', 'harmarium-ext' ),   'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_year',         [ 'label' => esc_html__( 'Year', 'harmarium-ext' ),         'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_edition',      [ 'label' => esc_html__( 'Edition', 'harmarium-ext' ),      'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_availability', [ 'label' => esc_html__( 'Availability', 'harmarium-ext' ), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ] );
		$this->add_control( 'show_price',        [ 'label' => esc_html__( 'Price', 'harmarium-ext' ),        'type' => Controls_Manager::SWITCHER, 'default' => 'yes' ] );

		$this->end_controls_section();

		/* ── Style: Labels ── */
		$this->start_controls_section( 'section_style_label', [
			'label' => esc_html__( 'Label Style', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_control( 'label_color', [
			'label'     => esc_html__( 'Color', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-artwork-meta__label' => 'color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'label_typography',
			'selector' => '{{WRAPPER}} .hm-artwork-meta__label',
		] );
		$this->end_controls_section();

		/* ── Style: Values ── */
		$this->start_controls_section( 'section_style_value', [
			'label' => esc_html__( 'Value Style', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_control( 'value_color', [
			'label'     => esc_html__( 'Color', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-artwork-meta__value' => 'color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'value_typography',
			'selector' => '{{WRAPPER}} .hm-artwork-meta__value',
		] );
		$this->end_controls_section();

		/* ── Style: Row ── */
		$this->start_controls_section( 'section_style_row', [
			'label' => esc_html__( 'Row Style', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_responsive_control( 'row_gap', [
			'label'     => esc_html__( 'Row gap', 'harmarium-ext' ),
			'type'      => Controls_Manager::SLIDER,
			'range'     => [ 'px' => [ 'min' => 0, 'max' => 48 ] ],
			'selectors' => [ '{{WRAPPER}} .hm-artwork-meta' => 'gap: {{SIZE}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'row_border',
			'selector' => '{{WRAPPER}} .hm-artwork-meta__row',
		] );
		$this->end_controls_section();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		$id = get_the_ID();

		$availability_labels = [
			'available' => __( 'Available', 'harmarium-ext' ),
			'sold'      => __( 'Sold', 'harmarium-ext' ),
			'reserved'  => __( 'Reserved', 'harmarium-ext' ),
			'nfs'       => __( 'Not for Sale', 'harmarium-ext' ),
		];

		$fields = [];
		if ( 'yes' === $s['show_medium']       ) $fields['_hm_medium']       = __( 'Medium', 'harmarium-ext' );
		if ( 'yes' === $s['show_dimensions']   ) $fields['_hm_dimensions']   = __( 'Dimensions', 'harmarium-ext' );
		if ( 'yes' === $s['show_year']         ) $fields['_hm_year']         = __( 'Year', 'harmarium-ext' );
		if ( 'yes' === $s['show_edition']      ) $fields['_hm_edition']      = __( 'Edition', 'harmarium-ext' );
		if ( 'yes' === $s['show_availability'] ) $fields['_hm_availability'] = __( 'Availability', 'harmarium-ext' );
		if ( 'yes' === $s['show_price']        ) $fields['_hm_price_display'] = __( 'Price', 'harmarium-ext' );

		$rows = '';
		foreach ( $fields as $meta_key => $label ) {
			$value = get_post_meta( $id, $meta_key, true );
			if ( ! $value ) continue;

			if ( '_hm_availability' === $meta_key ) {
				$value = $availability_labels[ $value ] ?? ucfirst( $value );
			}

			$rows .= '<div class="hm-artwork-meta__row">'
				. '<dt class="hm-artwork-meta__label">' . esc_html( $label ) . '</dt>'
				. '<dd class="hm-artwork-meta__value">' . esc_html( (string) $value ) . '</dd>'
				. '</div>';
		}

		if ( ! $rows ) return;

		echo '<dl class="hm-artwork-meta">' . $rows . '</dl>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
