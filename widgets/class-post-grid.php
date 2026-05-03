<?php
namespace CustomElements\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Grid widget.
 *
 * Displays posts in a configurable CSS grid (1–6 columns) with an optional
 * title bar and AJAX-powered "Load More" button. Skeleton cards are shown
 * while the next page is loading.
 */
class Post_Grid extends \Elementor\Widget_Base {

	// ─── Identity ─────────────────────────────────────────────────────────────

	public function get_name(): string {
		return 'ce-post-grid';
	}

	public function get_title(): string {
		return esc_html__( 'Post Grid', 'custom-elements' );
	}

	public function get_icon(): string {
		return 'eicon-posts-grid';
	}

	public function get_categories(): array {
		return [ 'custom-elements' ];
	}

	public function get_keywords(): array {
		return [ 'grid', 'posts', 'news', 'category', 'load more', 'ajax' ];
	}

	public function get_script_depends(): array {
		return [ 'custom-elements' ];
	}

	public function get_style_depends(): array {
		return [ 'custom-elements' ];
	}

	// ─── Controls ─────────────────────────────────────────────────────────────

	protected function register_controls(): void {

		// ── Query ─────────────────────────────────────────────────────────────
		$this->start_controls_section(
			'section_query',
			[
				'label' => esc_html__( 'Query', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'categories',
			[
				'label'       => esc_html__( 'Categories', 'custom-elements' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'options'     => $this->get_category_options(),
				'multiple'    => true,
				'default'     => [],
				'label_block' => true,
				'description' => esc_html__( 'Leave empty to show all categories.', 'custom-elements' ),
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label'   => esc_html__( 'Initial Post Count', 'custom-elements' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 48,
				'step'    => 1,
				'default' => 6,
			]
		);

		$this->end_controls_section();

		// ── Layout ────────────────────────────────────────────────────────────
		$this->start_controls_section(
			'section_layout',
			[
				'label' => esc_html__( 'Layout', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'columns',
			[
				'label'   => esc_html__( 'Columns', 'custom-elements' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => [
					'1' => esc_html__( '1 Column',  'custom-elements' ),
					'2' => esc_html__( '2 Columns', 'custom-elements' ),
					'3' => esc_html__( '3 Columns', 'custom-elements' ),
					'4' => esc_html__( '4 Columns', 'custom-elements' ),
					'5' => esc_html__( '5 Columns', 'custom-elements' ),
					'6' => esc_html__( '6 Columns', 'custom-elements' ),
				],
				'default' => '3',
			]
		);

		$this->end_controls_section();

		// ── Title Bar ─────────────────────────────────────────────────────────
		$this->start_controls_section(
			'section_title_bar',
			[
				'label' => esc_html__( 'Title Bar', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_title_bar',
			[
				'label'        => esc_html__( 'Show Title Bar', 'custom-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'custom-elements' ),
				'label_off'    => esc_html__( 'Hide', 'custom-elements' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'title_bar_text',
			[
				'label'       => esc_html__( 'Title', 'custom-elements' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => esc_html__( 'Latest Posts', 'custom-elements' ),
				'placeholder' => esc_html__( 'Enter section title…', 'custom-elements' ),
				'label_block' => true,
				'condition'   => [ 'show_title_bar' => 'yes' ],
			]
		);

		$this->add_control(
			'title_bar_tag',
			[
				'label'     => esc_html__( 'HTML Tag', 'custom-elements' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => [
					'h2'  => 'H2',
					'h3'  => 'H3',
					'h4'  => 'H4',
					'div' => 'div',
				],
				'default'   => 'h2',
				'condition' => [ 'show_title_bar' => 'yes' ],
			]
		);

		$this->end_controls_section();

		// ── Load More ─────────────────────────────────────────────────────────
		$this->start_controls_section(
			'section_load_more',
			[
				'label' => esc_html__( 'Load More', 'custom-elements' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'load_more_enabled',
			[
				'label'        => esc_html__( 'Enable AJAX Load More', 'custom-elements' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'custom-elements' ),
				'label_off'    => esc_html__( 'No', 'custom-elements' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'load_more_position',
			[
				'label'     => esc_html__( 'Button Position', 'custom-elements' ),
				'type'      => \Elementor\Controls_Manager::SELECT,
				'options'   => [
					'bottom-left'   => esc_html__( 'Bottom Left',              'custom-elements' ),
					'bottom-center' => esc_html__( 'Bottom Center',            'custom-elements' ),
					'bottom-right'  => esc_html__( 'Bottom Right',             'custom-elements' ),
					'top-right'     => esc_html__( 'Top Right (beside title)', 'custom-elements' ),
				],
				'default'   => 'bottom-center',
				'condition' => [ 'load_more_enabled' => 'yes' ],
			]
		);

		$this->add_control(
			'posts_per_load',
			[
				'label'     => esc_html__( 'Posts Per Load', 'custom-elements' ),
				'type'      => \Elementor\Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 24,
				'step'      => 1,
				'default'   => 6,
				'condition' => [ 'load_more_enabled' => 'yes' ],
			]
		);

		$this->end_controls_section();
	}

	// ─── Helpers ──────────────────────────────────────────────────────────────

	/**
	 * Returns all registered categories as SELECT2 options.
	 *
	 * @return array<string, string>
	 */
	private function get_category_options(): array {
		$options = [];

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

		$categories     = ! empty( $settings['categories'] ) ? (array) $settings['categories'] : [];
		$posts_per_page = max( 1, (int) ( $settings['posts_per_page'] ?? 6 ) );
		$columns        = in_array( (string) ( $settings['columns'] ?? '3' ), [ '1', '2', '3', '4', '5', '6' ], true )
							? (string) $settings['columns'] : '3';
		$show_title_bar = ( $settings['show_title_bar'] ?? 'yes' ) === 'yes';
		$title_bar_text = sanitize_text_field( $settings['title_bar_text'] ?? '' );
		$title_bar_tag  = in_array( $settings['title_bar_tag'] ?? 'h2', [ 'h2', 'h3', 'h4', 'div' ], true )
							? $settings['title_bar_tag'] : 'h2';
		$load_more      = ( $settings['load_more_enabled'] ?? 'yes' ) === 'yes';
		$lm_position    = in_array( $settings['load_more_position'] ?? 'bottom-center', [ 'bottom-left', 'bottom-center', 'bottom-right', 'top-right' ], true )
							? $settings['load_more_position'] : 'bottom-center';
		$posts_per_load = max( 1, (int) ( $settings['posts_per_load'] ?? 6 ) );

		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_page,
			'paged'          => 1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		if ( ! empty( $categories ) ) {
			$args['category__in'] = array_map( 'intval', $categories );
		}

		$query    = new \WP_Query( $args );
		$has_more = $query->max_num_pages > 1;

		$widget_id       = esc_attr( $this->get_id() );
		$cat_ids_encoded = esc_attr( implode( ',', array_map( 'intval', $categories ) ) );

		$show_header = $show_title_bar || ( $load_more && 'top-right' === $lm_position );
		$show_footer = $load_more && in_array( $lm_position, [ 'bottom-left', 'bottom-center', 'bottom-right' ], true );
		?>
		<div class="ce-post-grid"
			id="ce-post-grid-<?php echo $widget_id; ?>"
			data-widget-id="<?php echo $widget_id; ?>"
			data-cols="<?php echo esc_attr( $columns ); ?>"
			data-ppp="<?php echo esc_attr( (string) $posts_per_page ); ?>"
			data-ppl="<?php echo esc_attr( (string) $posts_per_load ); ?>"
			data-cats="<?php echo $cat_ids_encoded; ?>"
			data-page="1"
			data-has-more="<?php echo $has_more ? '1' : '0'; ?>">

			<?php if ( $show_header ) : ?>
			<div class="ce-post-grid__header<?php echo ( $load_more && 'top-right' === $lm_position ) ? ' ce-post-grid__header--has-btn' : ''; ?>">
				<?php if ( $show_title_bar ) : ?>
				<<?php echo esc_attr( $title_bar_tag ); ?> class="ce-post-grid__title">
					<?php echo esc_html( $title_bar_text ); ?>
				</<?php echo esc_attr( $title_bar_tag ); ?>>
				<?php endif; ?>

				<?php if ( $load_more && 'top-right' === $lm_position ) : ?>
				<button class="ce-post-grid__load-more"<?php echo ! $has_more ? ' hidden' : ''; ?>>
					<?php esc_html_e( 'Load More', 'custom-elements' ); ?>
				</button>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<div class="ce-post-grid__grid ce-post-grid__grid--cols-<?php echo esc_attr( $columns ); ?>">
				<?php
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						$query->the_post();
						self::render_card( get_the_ID() );
					}
					wp_reset_postdata();
				} else {
					echo '<p class="ce-post-grid__empty">' . esc_html__( 'No posts found.', 'custom-elements' ) . '</p>';
				}
				?>
			</div>

			<?php if ( $show_footer ) : ?>
			<div class="ce-post-grid__footer ce-post-grid__footer--<?php echo esc_attr( $lm_position ); ?>">
				<button class="ce-post-grid__load-more"<?php echo ! $has_more ? ' hidden' : ''; ?>>
					<?php esc_html_e( 'Load More', 'custom-elements' ); ?>
				</button>
			</div>
			<?php endif; ?>

		</div>
		<?php
	}

	/**
	 * Render a single post card.
	 * Called from render() and ajax_load_more() so output is identical.
	 *
	 * @param int $post_id
	 */
	public static function render_card( int $post_id ): void {
		$permalink = get_permalink( $post_id );
		$title     = get_the_title( $post_id );
		$date      = get_the_date( get_option( 'date_format' ), $post_id );
		$date_iso  = get_the_date( 'c', $post_id );
		$cats      = get_the_category( $post_id );
		$cat_name  = ! empty( $cats ) ? esc_html( $cats[0]->name ) : '';
		$thumb     = get_the_post_thumbnail( $post_id, 'magazine_thumbnail' );
		?>
		<article class="ce-post-grid__card">
			<a href="<?php echo esc_url( (string) $permalink ); ?>" class="ce-post-grid__card-link">
				<figure class="ce-post-grid__thumb<?php echo ! $thumb ? ' ce-post-grid__thumb--no-image' : ''; ?>">
					<?php if ( $thumb ) : ?>
						<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php else : ?>
						<span class="ce-post-grid__thumb-placeholder" aria-hidden="true"></span>
					<?php endif; ?>
				</figure>
				<div class="ce-post-grid__card-body">
					<?php if ( $cat_name ) : ?>
					<span class="ce-post-grid__cat"><?php echo $cat_name; // already escaped via esc_html() above ?></span>
					<?php endif; ?>
					<h3 class="ce-post-grid__post-title"><?php echo esc_html( $title ); ?></h3>
					<time class="ce-post-grid__date" datetime="<?php echo esc_attr( (string) $date_iso ); ?>">
						<?php echo esc_html( (string) $date ); ?>
					</time>
				</div>
			</a>
		</article>
		<?php
	}

	// ─── AJAX ─────────────────────────────────────────────────────────────────

	/**
	 * Handle the Load More AJAX request.
	 *
	 * Hooked to:
	 *   wp_ajax_ce_post_grid_load_more
	 *   wp_ajax_nopriv_ce_post_grid_load_more
	 */
	public static function ajax_load_more(): void {
		// ── Security ──────────────────────────────────────────────────────────
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'ce_post_grid_load_more' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Security check failed.', 'custom-elements' ) ], 403 );
		}

		// ── Parameters ────────────────────────────────────────────────────────
		$page           = max( 1, (int) ( $_POST['page'] ?? 1 ) );
		$posts_per_load = max( 1, min( 24, (int) ( $_POST['ppl'] ?? 6 ) ) );
		$cats_raw       = isset( $_POST['cats'] ) ? sanitize_text_field( wp_unslash( $_POST['cats'] ) ) : '';
		$categories     = array_filter( array_map( 'intval', explode( ',', $cats_raw ) ) );

		// ── Query ─────────────────────────────────────────────────────────────
		$args = [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $posts_per_load,
			'paged'          => $page,
			'orderby'        => 'date',
			'order'          => 'DESC',
		];

		if ( ! empty( $categories ) ) {
			$args['category__in'] = $categories;
		}

		$query = new \WP_Query( $args );

		// ── Render ────────────────────────────────────────────────────────────
		ob_start();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				self::render_card( get_the_ID() );
			}
			wp_reset_postdata();
		}
		$html = (string) ob_get_clean();

		wp_send_json_success(
			[
				'html'     => $html,
				'has_more' => $query->max_num_pages > $page,
			]
		);
	}
}
