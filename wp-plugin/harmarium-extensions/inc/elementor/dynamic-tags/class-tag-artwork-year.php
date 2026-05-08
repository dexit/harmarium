<?php
defined( 'ABSPATH' ) || exit;

class HM_Tag_Artwork_Year extends \Elementor\Core\DynamicTags\Tag {

	public function get_name(): string { return 'hm-artwork-year'; }
	public function get_title(): string { return esc_html__( 'Artwork Year', 'harmarium-ext' ); }
	public function get_group(): string { return 'harmarium'; }
	public function get_categories(): array { return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ]; }

	public function render(): void {
		$year = get_post_meta( get_the_ID(), '_hm_year', true );
		echo esc_html( $year ? (string) $year : '' );
	}
}
