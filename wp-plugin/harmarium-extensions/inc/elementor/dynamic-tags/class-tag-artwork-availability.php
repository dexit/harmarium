<?php
defined( 'ABSPATH' ) || exit;

class HM_Tag_Artwork_Availability extends \Elementor\Core\DynamicTags\Tag {

	public function get_name(): string { return 'hm-artwork-availability'; }
	public function get_title(): string { return esc_html__( 'Artwork Availability', 'harmarium-ext' ); }
	public function get_group(): string { return 'harmarium'; }
	public function get_categories(): array { return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ]; }

	protected function register_controls(): void {
		$this->add_control( 'show_label', [
			'label'   => esc_html__( 'Show human label', 'harmarium-ext' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );
	}

	public function render(): void {
		$raw    = get_post_meta( get_the_ID(), '_hm_availability', true );
		$labels = [
			'available' => __( 'Available', 'harmarium-ext' ),
			'sold'      => __( 'Sold', 'harmarium-ext' ),
			'reserved'  => __( 'Reserved', 'harmarium-ext' ),
			'nfs'       => __( 'Not for Sale', 'harmarium-ext' ),
		];
		$output = 'yes' === $this->get_settings( 'show_label' )
			? ( $labels[ $raw ] ?? ucfirst( $raw ) )
			: $raw;
		echo esc_html( $output );
	}
}
