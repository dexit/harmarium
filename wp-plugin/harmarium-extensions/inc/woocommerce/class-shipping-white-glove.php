<?php
/**
 * Shipping method: White Glove Delivery (insured art handling + installation).
 */

defined( 'ABSPATH' ) || exit;

class HM_Shipping_White_Glove extends WC_Shipping_Method {

	public function __construct( int $instance_id = 0 ) {
		$this->id                 = 'hm_white_glove';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'White Glove Delivery', 'harmarium-ext' );
		$this->method_description = __( 'Insured, climate-controlled delivery with optional installation service. Price calculated on enquiry.', 'harmarium-ext' );
		$this->supports           = [ 'shipping-zones', 'instance-settings' ];
		$this->init();
	}

	public function init(): void {
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option( 'enabled' );
		$this->title   = $this->get_option( 'title', __( 'White Glove Delivery', 'harmarium-ext' ) );
		$this->cost    = (float) $this->get_option( 'cost', 0 );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields(): void {
		$this->instance_form_fields = [
			'enabled' => [
				'title'   => __( 'Enable', 'harmarium-ext' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable white glove delivery', 'harmarium-ext' ),
				'default' => 'yes',
			],
			'title' => [
				'title'   => __( 'Method title', 'harmarium-ext' ),
				'type'    => 'text',
				'default' => __( 'White Glove Delivery', 'harmarium-ext' ),
			],
			'cost' => [
				'title'       => __( 'Flat rate cost', 'harmarium-ext' ),
				'type'        => 'price',
				'description' => __( 'Leave 0 for "price on enquiry"', 'harmarium-ext' ),
				'default'     => '0',
			],
		];
	}

	public function calculate_shipping( array $package = [] ): void {
		$label = $this->cost > 0
			? $this->title
			: $this->title . ' — ' . __( 'price on enquiry', 'harmarium-ext' );

		$this->add_rate( [
			'id'    => $this->get_rate_id(),
			'label' => $label,
			'cost'  => $this->cost,
		] );
	}
}
