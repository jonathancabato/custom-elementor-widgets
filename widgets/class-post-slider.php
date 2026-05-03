<?php
namespace CustomElements\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Slider widget.
 *
 * Displays a bxSlider-powered slider of the most recent posts
 * from a chosen category. Exposes controls for post count bounds
 * and the slider transition mode.
 */
class Post_Slider extends \Elementor\Widget_Base {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'ce-post-slider';
	}

	public function get_title(): string {
		return esc_html__( 'Post Slider', 'custom-elements' );
	}

	public function get_icon(): string {
		return 'eicon-slider-push';
	}

	public function get_categories(): array {
		return [ 'custom-elements' ];
	}

	public function get_keywords(): array {
		return [ 'slider', 'posts', 'carousel', 'news', 'bxslider', 'category' ];
	}

	public function get_script_depends(): array {
		// 'custom-elements' (widgets.js) already declares 'bxslider' as its own
		// dependency, so both scripts load in the correct order.
		return [ 'custom-elements' ];
	}

	public function get_style_depends(): array {
		// Load bxSlider's stylesheet on pages where this widget appears.
		return [ 'bxslider', 'custom-elements' ];
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {

		// --- Query Section ---
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'category',
			[
				'label'       => esc_html__( 'Category', 'custom-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_category_options(),
				'default'     => '',
				'label_block' => true,
			]
		);

		$this->add_control(
			'posts_min',
			[
				'label'   => esc_html__( 'Minimum Posts', 'custom-elements' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'step'    => 1,
				'default' => 3,
			]
		);

		$this->add_control(
			'posts_max',
			[
				'label'       => esc_html__( 'Maximum Posts', 'custom-elements' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'min'         => 1,
				'max'         => 20,
				'step'        => 1,
				'default'     => 8,
				'description' => esc_html__( 'Must be equal to or greater than the minimum.', 'custom-elements' ),
			]
		);

		$this->end_controls_section();

		// --- Layout Section ---
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'slider_width',
			[
				'label'   => esc_html__( 'Slider Width', 'custom-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'container' => esc_html__( 'Container (default)', 'custom-elements' ),
					'boxed'     => esc_html__( 'Boxed (1200px max)', 'custom-elements' ),
					'full'      => esc_html__( 'Full Width', 'custom-elements' ),
				],
				'default' => 'container',
			]
		);

		$this->add_control(
			'image_size',
			[
				'label'   => esc_html__( 'Image Size', 'custom-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'post_slider'      => esc_html__( 'Standard (1200×500)', 'custom-elements' ),
					'post_slider_wide' => esc_html__( 'Widescreen (1920×600)', 'custom-elements' ),
					'post_slider_tall' => esc_html__( 'Tall (900×600)', 'custom-elements' ),
				],
				'default' => 'post_slider',
			]
		);

		$this->end_controls_section();

		// --- Slider Section ---
		$this->start_controls_section(
			'section_slider',
			[
				'label' => esc_html__( 'Slider', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'transition_mode',
			[
				'label'   => esc_html__( 'Transition Mode', 'custom-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'horizontal' => esc_html__( 'Horizontal', 'custom-elements' ),
					'vertical'   => esc_html__( 'Vertical', 'custom-elements' ),
					'fade'       => esc_html__( 'Fade', 'custom-elements' ),
				],
				'default' => 'horizontal',
			]
		);

		$this->add_control(
			'pager_type',
			[
				'label'   => esc_html__( 'Pager Style', 'custom-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'basic'     => esc_html__( 'Basic (dots)', 'custom-elements' ),
					'thumbnail' => esc_html__( 'Thumbnail', 'custom-elements' ),
					'none'      => esc_html__( 'None (hidden)', 'custom-elements' ),
				],
				'default' => 'basic',
			]
		);

		$this->end_controls_section();
	}

	// ─── Helpers ──────────────────────────────────────────────────────────────

	/**
	 * Returns all registered categories as an options array for the SELECT2 control.
	 *
	 * @return array<string, string>
	 */
	private function get_category_options(): array {
		$options = [ '' => esc_html__( '— All Categories —', 'custom-elements' ) ];

		$terms = get_terms(
			[
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
			]
		);

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ (string) $term->term_id ] = $term->name;
			}
		}

		return $options;
	}

	// ─── Render ───────────────────────────────────────────────────────────────

	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$category     = ! empty( $settings['category'] ) ? (int) $settings['category'] : 0;
		$posts_min    = max( 1, (int) ( $settings['posts_min'] ?? 3 ) );
		$posts_max    = max( $posts_min, (int) ( $settings['posts_max'] ?? 8 ) );
		$mode         = in_array( $settings['transition_mode'], [ 'horizontal', 'vertical', 'fade' ], true )
							? $settings['transition_mode']
							: 'horizontal';
		$pager_type   = in_array( $settings['pager_type'] ?? 'basic', [ 'basic', 'thumbnail', 'none' ], true )
							? $settings['pager_type']
							: 'basic';
		$slider_width = in_array( $settings['slider_width'] ?? 'container', [ 'container', 'boxed', 'full' ], true )
							? $settings['slider_width']
							: 'container';
		$image_size   = in_array( $settings['image_size'] ?? 'post_slider', [ 'post_slider', 'post_slider_wide', 'post_slider_tall' ], true )
							? $settings['image_size']
							: 'post_slider';

		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_max,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		];

		if ( $category > 0 ) {
			$args['cat'] = $category;
		}

		$query = new \WP_Query( $args );

		if ( ! $query->have_posts() ) {
			echo '<p class="ce-post-slider__empty">' . esc_html__( 'No posts found.', 'custom-elements' ) . '</p>';
			return;
		}

		$widget_id = esc_attr( $this->get_id() );
		?>
		<div class="ce-post-slider"
			id="ce-post-slider-<?php echo $widget_id; ?>"
			data-mode="<?php echo esc_attr( $mode ); ?>"
			data-pager="<?php echo esc_attr( $pager_type ); ?>"
			data-layout="<?php echo esc_attr( $slider_width ); ?>"
			data-imgsize="<?php echo esc_attr( $image_size ); ?>">
			<ul class="ce-post-slider__track bxslider">
				<?php while ( $query->have_posts() ) : $query->the_post(); ?>
					<li class="ce-post-slider__slide" data-thumb="<?php echo esc_url( (string) get_the_post_thumbnail_url( get_the_ID(), 'slider_thumbnail' ) ); ?>">
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="ce-post-slider__link">
							<?php if ( has_post_thumbnail() ) : ?>
								<figure class="ce-post-slider__image">
							<?php echo get_the_post_thumbnail( get_the_ID(), $image_size ); ?>
								</figure>
							<?php endif; ?>
							<span class="ce-post-slider__title">
								<?php echo esc_html( get_the_title() ); ?>
							</span>
						</a>
					</li>
				<?php endwhile; ?>
			</ul>
		</div>
		<?php
		wp_reset_postdata();
	}
}
