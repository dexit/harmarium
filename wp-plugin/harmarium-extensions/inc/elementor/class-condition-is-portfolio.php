<?php
/**
 * Elementor Theme Builder Condition: Is Portfolio Item.
 */

defined( 'ABSPATH' ) || exit;

class HM_Condition_Is_Portfolio extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

	public static function get_type(): string { return 'singular'; }
	public function get_name(): string { return 'hm_is_portfolio'; }
	public function get_label(): string { return esc_html__( 'Portfolio Item', 'harmarium-ext' ); }

	public function check( array $args ): bool {
		return is_singular( 'portfolio' );
	}
}
