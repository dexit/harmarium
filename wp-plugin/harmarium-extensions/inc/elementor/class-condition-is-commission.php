<?php
/**
 * Elementor Theme Builder Condition: Is Commission Page.
 */

defined( 'ABSPATH' ) || exit;

class HM_Condition_Is_Commission extends \ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base {

	public static function get_type(): string { return 'singular'; }
	public function get_name(): string { return 'hm_is_commission'; }
	public function get_label(): string { return esc_html__( 'Commission Page', 'harmarium-ext' ); }

	public function check( array $args ): bool {
		return is_page( 'commission' ) || is_singular( 'hm_commission' );
	}
}
