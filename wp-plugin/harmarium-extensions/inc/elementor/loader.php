<?php
/**
 * Elementor integration loader.
 * Registers widgets, dynamic tags, and Loop Grid query filters.
 */

defined( 'ABSPATH' ) || exit;

add_action( 'elementor/widgets/register', 'harmarium_register_elementor_widgets' );
add_action( 'elementor/dynamic_tags/register', 'harmarium_register_elementor_dynamic_tags' );

function harmarium_register_elementor_widgets( \Elementor\Widgets_Manager $manager ): void {
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/widgets/class-widget-portfolio-loop.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/widgets/class-widget-commission-form.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/widgets/class-widget-artwork-meta.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/widgets/class-widget-product-spotlight.php';

	$manager->register( new HM_Widget_Portfolio_Loop() );
	$manager->register( new HM_Widget_Commission_Form() );
	$manager->register( new HM_Widget_Artwork_Meta() );
	$manager->register( new HM_Widget_Product_Spotlight() );
}

function harmarium_register_elementor_dynamic_tags( \Elementor\Core\DynamicTags\Manager $manager ): void {
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/dynamic-tags/class-tag-artwork-medium.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/dynamic-tags/class-tag-artwork-availability.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/dynamic-tags/class-tag-artwork-price.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/dynamic-tags/class-tag-artwork-dimensions.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/dynamic-tags/class-tag-artwork-year.php';

	$manager->register( new HM_Tag_Artwork_Medium() );
	$manager->register( new HM_Tag_Artwork_Availability() );
	$manager->register( new HM_Tag_Artwork_Price() );
	$manager->register( new HM_Tag_Artwork_Dimensions() );
	$manager->register( new HM_Tag_Artwork_Year() );
}

/* ── Loop Grid query filters ── */
add_action( 'elementor/query/hm_available_artworks', function ( \WP_Query $query ): void {
	$query->set( 'post_type', 'portfolio' );
	$query->set( 'meta_query', [
		[ 'key' => '_hm_availability', 'value' => 'available', 'compare' => '=' ],
	] );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );
} );

add_action( 'elementor/query/hm_portfolio_all', function ( \WP_Query $query ): void {
	$query->set( 'post_type', 'portfolio' );
	$query->set( 'orderby', 'date' );
	$query->set( 'order', 'DESC' );
} );

add_action( 'elementor/query/hm_commission_products', function ( \WP_Query $query ): void {
	$query->set( 'post_type', 'product' );
	$query->set( 'tax_query', [
		[
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => [ 'commission', 'artwork' ],
		],
	] );
} );

/* ── Theme Builder conditions ── */
add_action( 'elementor/theme/register_conditions', function ( \ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager $manager ): void {
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/class-condition-is-portfolio.php';
	require_once HARMARIUM_EXT_DIR . 'inc/elementor/class-condition-is-commission.php';
	$manager->get_condition( 'general' )->register_sub_condition( new HM_Condition_Is_Portfolio() );
	$manager->get_condition( 'general' )->register_sub_condition( new HM_Condition_Is_Commission() );
} );
