<?php
namespace CustomElements\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hello World widget.
 *
 * A minimal starter widget that renders a configurable heading.
 * Use this as a reference when building new widgets.
 */
class Hello_World extends \Elementor\Widget_Base {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'ce-hello-world';
	}

	public function get_title(): string {
		return esc_html__( 'Hello World', 'custom-elements' );
	}

	public function get_icon(): string {
		return 'eicon-text';
	}

	public function get_categories(): array {
		return [ 'custom-elements' ];
	}

	public function get_keywords(): array {
		return [ 'hello', 'world', 'test', 'sample' ];
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'message',
			[
				'label'       => esc_html__( 'Message', 'custom-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Hello World!', 'custom-elements' ),
				'placeholder' => esc_html__( 'Enter your message…', 'custom-elements' ),
				'label_block' => true,
			]
		);

		$this->end_controls_section();
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$message  = ! empty( $settings['message'] ) ? $settings['message'] : '';

		echo '<h3 class="ce-hello-world">' . esc_html( $message ) . '</h3>';
	}
}
