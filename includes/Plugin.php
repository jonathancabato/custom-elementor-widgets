<?php
namespace CustomElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin class — singleton.
 *
 * Bootstraps the plugin by hooking into Elementor's lifecycle:
 *  - registers the custom widget category
 *  - delegates widget registration to Widgets_Manager
 *  - enqueues frontend assets
 */
final class Plugin {

	/** @var Plugin|null Single instance of this class. */
	private static ?Plugin $instance = null;

	// ─── Singleton ────────────────────────────────────────────────────────────

	/**
	 * Returns the single instance, creating it on first call.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Prevent external instantiation. */
	private function __construct() {
		$this->register_hooks();
	}

	/** Prevent cloning. */
	private function __clone() {}

	// ─── Hooks ────────────────────────────────────────────────────────────────

	/**
	 * Register all plugin hooks with WordPress / Elementor.
	 */
	private function register_hooks(): void {
		add_action( 'after_setup_theme',                       [ $this, 'register_image_sizes' ] );
		add_action( 'elementor/init',                          [ $this, 'load_textdomain' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_categories' ] );
		add_action( 'elementor/widgets/register',              [ $this, 'register_widgets' ] );
		add_action( 'elementor/frontend/after_enqueue_styles',  [ $this, 'enqueue_styles' ] );
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'register_scripts' ] );
	}

	// ─── Callbacks ────────────────────────────────────────────────────────────

	/**
	 * Register custom image sizes.
	 */
	public function register_image_sizes(): void {
		add_image_size( 'magazine_thumbnail', 600,  400, true );
		add_image_size( 'post_slider',        1200, 500, true );
		add_image_size( 'slider_thumbnail',   150,  100, true );
	}

	/**
	 * Load plugin text domain for translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'custom-elements',
			false,
			dirname( plugin_basename( CUSTOM_ELEMENTS_FILE ) ) . '/languages'
		);
	}

	/**
	 * Add a dedicated "Jonathan" category to the Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager
	 */
	public function register_widget_categories( \Elementor\Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			'custom-elements',
			[
				'title' => esc_html__( 'Jonathan', 'custom-elements' ),
				'icon'  => 'eicon-newspaper',
			]
		);
	}

	/**
	 * Hand off widget registration to the Widgets_Manager helper.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager
	 */
	public function register_widgets( \Elementor\Widgets_Manager $widgets_manager ): void {
		require_once CUSTOM_ELEMENTS_PATH . 'includes/Widgets_Manager.php';
		Widgets_Manager::register( $widgets_manager );
	}

	/**
	 * Enqueue the shared frontend stylesheet and register third-party styles.
	 */
	public function enqueue_styles(): void {
		wp_register_style(
			'bxslider',
			'https://cdn.jsdelivr.net/npm/bxslider@4.2.17/dist/jquery.bxslider.min.css',
			[],
			'4.2.17'
		);

		wp_enqueue_style(
			'custom-elements',
			CUSTOM_ELEMENTS_URL . 'assets/css/widgets.css',
			[],
			CUSTOM_ELEMENTS_VERSION
		);
	}

	/**
	 * Register the shared frontend script and third-party libraries (widgets enqueue on demand).
	 */
	public function register_scripts(): void {
		wp_register_script(
			'bxslider',
			'https://cdn.jsdelivr.net/npm/bxslider@4.2.17/dist/jquery.bxslider.min.js',
			[ 'jquery' ],
			'4.2.17',
			true
		);

		wp_register_script(
			'custom-elements',
			CUSTOM_ELEMENTS_URL . 'assets/js/widgets.js',
			[ 'jquery', 'bxslider' ],
			CUSTOM_ELEMENTS_VERSION,
			true
		);
	}
}
