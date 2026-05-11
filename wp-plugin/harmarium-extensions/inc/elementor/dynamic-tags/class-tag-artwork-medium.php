<?php
/**
 * Dynamic Tag: Artwork Medium (from portfolio post meta _hm_medium).
 */

defined( 'ABSPATH' ) || exit;

class HM_Tag_Artwork_Medium extends \Elementor\Core\DynamicTags\Tag {

	public function get_name(): string { return 'hm-artwork-medium'; }
	public function get_title(): string { return esc_html__( 'Artwork Medium', 'harmarium-ext' ); }
	public function get_group(): string { return 'harmarium'; }
	public function get_categories(): array { return [ \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY ]; }

	public function render(): void {
		echo esc_html( get_post_meta( get_the_ID(), '_hm_medium', true ) );
	}
}
