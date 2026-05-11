<?php
defined( 'ABSPATH' ) || exit;

class HM_Tag_Artwork_Price extends \Elementor\Core\DynamicTags\Tag {

	public function get_name(): string { return 'hm-artwork-price'; }
	public function get_title(): string { return esc_html__( 'Artwork Price', 'harmarium-ext' ); }
	public function get_group(): string { return 'harmarium'; }
	public function get_categories(): array { return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ]; }

	public function render(): void {
		echo esc_html( get_post_meta( get_the_ID(), '_hm_price_display', true ) );
	}
}
