<?php
/**
 * Plugin Name:       Custom Elements for Elementor
 * Plugin URI:        https://github.com/jonathancabato
 * Description:       A suite of custom Elementor widgets built for magazine and news-style websites. Provides flexible, reusable content blocks for editorial layouts.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Jonathan
 * Author URI:        https://github.com/jonathancabato
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       custom-elements
 * Domain Path:       /languages
 *
 * Elementor tested up to: 3.21.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// ─── Constants ───────────────────────────────────────────────────────────────

define( 'CUSTOM_ELEMENTS_VERSION',                  '1.0.0' );
define( 'CUSTOM_ELEMENTS_MINIMUM_ELEMENTOR_VERSION', '3.0.0' );
define( 'CUSTOM_ELEMENTS_MINIMUM_PHP_VERSION',       '7.4' );
define( 'CUSTOM_ELEMENTS_FILE',  __FILE__ );
define( 'CUSTOM_ELEMENTS_PATH',  plugin_dir_path( __FILE__ ) );
define( 'CUSTOM_ELEMENTS_URL',   plugin_dir_url( __FILE__ ) );

// ─── Bootstrap ───────────────────────────────────────────────────────────────

add_action( 'plugins_loaded', 'custom_elements_init' );

/**
 * Verify all dependencies before loading the plugin.
 * Displays an admin notice and aborts if a requirement is not met.
 */
function custom_elements_init(): void {

	// 1. Elementor must be installed and activated.
	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'custom_elements_notice_missing_elementor' );
		return;
	}

	// 2. Elementor must meet the minimum version requirement.
	if ( ! version_compare( ELEMENTOR_VERSION, CUSTOM_ELEMENTS_MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
		add_action( 'admin_notices', 'custom_elements_notice_elementor_version' );
		return;
	}

	// 3. Server PHP version must meet the minimum requirement.
	if ( version_compare( PHP_VERSION, CUSTOM_ELEMENTS_MINIMUM_PHP_VERSION, '<' ) ) {
		add_action( 'admin_notices', 'custom_elements_notice_php_version' );
		return;
	}

	require_once CUSTOM_ELEMENTS_PATH . 'includes/Plugin.php';
	\CustomElements\Plugin::instance();
}

// ─── Admin Notices ───────────────────────────────────────────────────────────

/**
 * Notice: Elementor is not installed or activated.
 */
function custom_elements_notice_missing_elementor(): void {
	$message = sprintf(
		/* translators: 1: Plugin name  2: Elementor link */
		esc_html__( '"%1$s" requires %2$s to be installed and activated.', 'custom-elements' ),
		'<strong>' . esc_html__( 'Custom Elements for Elementor', 'custom-elements' ) . '</strong>',
		'<a href="https://wordpress.org/plugins/elementor/" target="_blank"><strong>Elementor</strong></a>'
	);
	printf( '<div class="notice notice-error"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Notice: Installed Elementor version is too old.
 */
function custom_elements_notice_elementor_version(): void {
	$message = sprintf(
		/* translators: 1: Plugin name  2: Required version */
		esc_html__( '"%1$s" requires Elementor version %2$s or greater. Please update Elementor.', 'custom-elements' ),
		'<strong>' . esc_html__( 'Custom Elements for Elementor', 'custom-elements' ) . '</strong>',
		'<strong>' . CUSTOM_ELEMENTS_MINIMUM_ELEMENTOR_VERSION . '</strong>'
	);
	printf( '<div class="notice notice-error"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Notice: Server PHP version is too old.
 */
function custom_elements_notice_php_version(): void {
	$message = sprintf(
		/* translators: 1: Plugin name  2: Required PHP version */
		esc_html__( '"%1$s" requires PHP %2$s or greater. Please contact your host to upgrade.', 'custom-elements' ),
		'<strong>' . esc_html__( 'Custom Elements for Elementor', 'custom-elements' ) . '</strong>',
		'<strong>' . CUSTOM_ELEMENTS_MINIMUM_PHP_VERSION . '</strong>'
	);
	printf( '<div class="notice notice-error"><p>%s</p></div>', $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
