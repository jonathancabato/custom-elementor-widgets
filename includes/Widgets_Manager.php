<?php
namespace CustomElements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles auto-discovery and registration of widget class files.
 *
 * Convention: every widget lives in /widgets/ and is named class-{widget-slug}.php
 * The class inside must follow the namespace CustomElements\Widgets\{Widget_Class_Name}.
 *
 * Example:
 *   File:  widgets/class-news-ticker.php
 *   Class: CustomElements\Widgets\News_Ticker
 */
class Widgets_Manager {

	/**
	 * Scan /widgets/, include each class file, and register the widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor's widget manager.
	 */
	public static function register( \Elementor\Widgets_Manager $widgets_manager ): void {
		$widget_files = glob( CUSTOM_ELEMENTS_PATH . 'widgets/class-*.php' );

		if ( empty( $widget_files ) ) {
			return;
		}

		foreach ( $widget_files as $file ) {
			require_once $file;

			// Derive the class name from the filename.
			// e.g. class-news-ticker.php → CustomElements\Widgets\News_Ticker
			$filename   = basename( $file, '.php' );          // class-news-ticker
			$class_slug = str_replace( 'class-', '', $filename ); // news-ticker
			$class_name = __NAMESPACE__ . '\\Widgets\\' . str_replace(
				' ',
				'_',
				ucwords( str_replace( '-', ' ', $class_slug ) )
			);

			if ( class_exists( $class_name ) ) {
				$widgets_manager->register( new $class_name() );
			}
		}
	}
}
