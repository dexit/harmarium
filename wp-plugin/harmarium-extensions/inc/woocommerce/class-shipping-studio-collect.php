<?php
/**
 * Shipping method: Collect from Studio (free, by appointment).
 */

defined( 'ABSPATH' ) || exit;

class HM_Shipping_Studio_Collect extends WC_Shipping_Method {

	public function __construct( int $instance_id = 0 ) {
		$this->id                 = 'hm_studio_collect';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'Collect from Studio', 'harmarium-ext' );
		$this->method_description = __( 'Free collection from the studio by appointment. You will be contacted to arrange a time.', 'harmarium-ext' );
		$this->supports           = [ 'shipping-zones', 'instance-settings' ];
		$this->init();
	}

	public function init(): void {
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option( 'enabled' );
		$this->title   = $this->get_option( 'title', __( 'Collect from Studio', 'harmarium-ext' ) );
		add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	public function init_form_fields(): void {
		$this->instance_form_fields = [
			'enabled' => [
				'title'   => __( 'Enable', 'harmarium-ext' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable collect from studio', 'harmarium-ext' ),
				'default' => 'yes',
			],
			'title' => [
				'title'   => __( 'Method title', 'harmarium-ext' ),
				'type'    => 'text',
				'default' => __( 'Collect from Studio', 'harmarium-ext' ),
			],
		];
	}

	public function calculate_shipping( array $package = [] ): void {
		$this->add_rate( [
			'id'    => $this->get_rate_id(),
			'label' => $this->title,
			'cost'  => 0,
		] );
	}
}
