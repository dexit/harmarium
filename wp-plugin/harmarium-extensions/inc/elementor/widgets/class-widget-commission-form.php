<?php
/**
 * Elementor Widget: Commission Request Form.
 * Fully styled, AJAX-submitted commission form with Elementor controls for all visual properties.
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

class HM_Widget_Commission_Form extends \Elementor\Widget_Base {

	public function get_name(): string { return 'hm_commission_form'; }
	public function get_title(): string { return esc_html__( 'Commission Form', 'harmarium-ext' ); }
	public function get_icon(): string { return 'eicon-form-horizontal'; }
	public function get_categories(): array { return [ 'harmarium' ]; }
	public function get_keywords(): array { return [ 'commission', 'form', 'contact', 'request' ]; }

	public function has_widget_inner_wrapper(): bool { return false; }

	public function get_script_depends(): array { return [ 'hm-commission-widget' ]; }

	protected function register_controls(): void {
		/* ── Content ── */
		$this->start_controls_section( 'section_content', [
			'label' => esc_html__( 'Form', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_CONTENT,
		] );
		$this->add_control( 'heading', [
			'label'   => esc_html__( 'Heading', 'harmarium-ext' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Commission a Portrait', 'harmarium-ext' ),
			'dynamic' => [ 'active' => true ],
		] );
		$this->add_control( 'submit_label', [
			'label'   => esc_html__( 'Submit button label', 'harmarium-ext' ),
			'type'    => Controls_Manager::TEXT,
			'default' => esc_html__( 'Submit Request', 'harmarium-ext' ),
		] );
		$this->add_control( 'success_message', [
			'label'   => esc_html__( 'Success message', 'harmarium-ext' ),
			'type'    => Controls_Manager::TEXTAREA,
			'default' => esc_html__( 'Thank you — your request has been received. I will be in touch within 2–3 working days.', 'harmarium-ext' ),
		] );
		$this->end_controls_section();

		/* ── Style: Form ── */
		$this->start_controls_section( 'section_style_form', [
			'label' => esc_html__( 'Form Container', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_group_control( Group_Control_Background::get_type(), [
			'name'     => 'form_background',
			'selector' => '{{WRAPPER}} .hm-commission-form',
		] );
		$this->add_responsive_control( 'form_padding', [
			'label'      => esc_html__( 'Padding', 'harmarium-ext' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em', '%' ],
			'selectors'  => [ '{{WRAPPER}} .hm-commission-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'form_border',
			'selector' => '{{WRAPPER}} .hm-commission-form',
		] );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), [
			'name'     => 'form_shadow',
			'selector' => '{{WRAPPER}} .hm-commission-form',
		] );
		$this->end_controls_section();

		/* ── Style: Inputs ── */
		$this->start_controls_section( 'section_style_inputs', [
			'label' => esc_html__( 'Inputs', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_control( 'input_color', [
			'label'     => esc_html__( 'Text color', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-commission-form input, {{WRAPPER}} .hm-commission-form textarea, {{WRAPPER}} .hm-commission-form select' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'input_bg', [
			'label'     => esc_html__( 'Background', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-commission-form input, {{WRAPPER}} .hm-commission-form textarea, {{WRAPPER}} .hm-commission-form select' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Border::get_type(), [
			'name'     => 'input_border',
			'selector' => '{{WRAPPER}} .hm-commission-form input, {{WRAPPER}} .hm-commission-form textarea, {{WRAPPER}} .hm-commission-form select',
		] );
		$this->end_controls_section();

		/* ── Style: Button ── */
		$this->start_controls_section( 'section_style_button', [
			'label' => esc_html__( 'Submit Button', 'harmarium-ext' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		] );
		$this->add_control( 'btn_color', [
			'label'     => esc_html__( 'Text color', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-commission-form__submit' => 'color: {{VALUE}};' ],
		] );
		$this->add_control( 'btn_bg', [
			'label'     => esc_html__( 'Background', 'harmarium-ext' ),
			'type'      => Controls_Manager::COLOR,
			'selectors' => [ '{{WRAPPER}} .hm-commission-form__submit' => 'background-color: {{VALUE}};' ],
		] );
		$this->add_group_control( Group_Control_Typography::get_type(), [
			'name'     => 'btn_typography',
			'selector' => '{{WRAPPER}} .hm-commission-form__submit',
		] );
		$this->add_responsive_control( 'btn_padding', [
			'label'      => esc_html__( 'Padding', 'harmarium-ext' ),
			'type'       => Controls_Manager::DIMENSIONS,
			'size_units' => [ 'px', 'em' ],
			'selectors'  => [ '{{WRAPPER}} .hm-commission-form__submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
		] );
		$this->end_controls_section();
	}

	protected function render(): void {
		$s = $this->get_settings_for_display();
		?>
		<div class="hm-commission-form-wrap">
			<?php if ( $s['heading'] ) : ?>
				<h2 class="hm-commission-form__heading"><?php echo esc_html( $s['heading'] ); ?></h2>
			<?php endif; ?>

			<div class="hm-commission-form__success" role="alert" aria-live="polite" style="display:none">
				<?php echo esc_html( $s['success_message'] ); ?>
			</div>

			<form class="hm-commission-form" method="post" novalidate
				data-rest-url="<?php echo esc_url( rest_url( 'harmarium/v1/commission' ) ); ?>"
				data-nonce="<?php echo esc_attr( wp_create_nonce( 'harmarium_commission' ) ); ?>"
				data-success="<?php echo esc_attr( $s['success_message'] ); ?>">

				<div class="hm-commission-form__row hm-commission-form__row--two-col">
					<div class="hm-commission-form__field">
						<label for="hm_commission_name"><?php esc_html_e( 'Your name', 'harmarium-ext' ); ?> <span aria-hidden="true">*</span></label>
						<input type="text" id="hm_commission_name" name="name" required aria-required="true" autocomplete="name">
					</div>
					<div class="hm-commission-form__field">
						<label for="hm_commission_email"><?php esc_html_e( 'Email address', 'harmarium-ext' ); ?> <span aria-hidden="true">*</span></label>
						<input type="email" id="hm_commission_email" name="email" required aria-required="true" autocomplete="email">
					</div>
				</div>

				<div class="hm-commission-form__field">
					<label for="hm_commission_subject"><?php esc_html_e( 'Subject', 'harmarium-ext' ); ?> <span aria-hidden="true">*</span></label>
					<input type="text" id="hm_commission_subject" name="subject" required aria-required="true">
				</div>

				<div class="hm-commission-form__field">
					<label for="hm_commission_message"><?php esc_html_e( 'Describe your commission', 'harmarium-ext' ); ?> <span aria-hidden="true">*</span></label>
					<textarea id="hm_commission_message" name="message" rows="5" required aria-required="true"></textarea>
				</div>

				<div class="hm-commission-form__row hm-commission-form__row--two-col">
					<div class="hm-commission-form__field">
						<label for="hm_commission_budget"><?php esc_html_e( 'Budget', 'harmarium-ext' ); ?></label>
						<select id="hm_commission_budget" name="budget">
							<option value=""><?php esc_html_e( 'Select budget…', 'harmarium-ext' ); ?></option>
							<option value="under-500">Under €500</option>
							<option value="500-1500">€500–€1,500</option>
							<option value="1500-3000">€1,500–€3,000</option>
							<option value="3000-plus">€3,000+</option>
						</select>
					</div>
					<div class="hm-commission-form__field">
						<label for="hm_commission_timeline"><?php esc_html_e( 'Timeline', 'harmarium-ext' ); ?></label>
						<select id="hm_commission_timeline" name="timeline">
							<option value=""><?php esc_html_e( 'Select timeline…', 'harmarium-ext' ); ?></option>
							<option value="flexible"><?php esc_html_e( 'Flexible', 'harmarium-ext' ); ?></option>
							<option value="1-3-months">1–3 months</option>
							<option value="3-6-months">3–6 months</option>
							<option value="asap"><?php esc_html_e( 'As soon as possible', 'harmarium-ext' ); ?></option>
						</select>
					</div>
				</div>

				<div class="hm-commission-form__errors" role="alert" aria-live="assertive" style="display:none"></div>

				<button type="submit" class="hm-commission-form__submit" aria-busy="false">
					<?php echo esc_html( $s['submit_label'] ); ?>
				</button>
			</form>
		</div>
		<?php
	}
}
