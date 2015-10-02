<?php
/**
 * Plugin Name: Batch Processing
 * Version: 0.1.0
 * Description: Enables developers to utilize an abstract class to achieve easy batch processes that can be run through WP Admin or (if installed) WP CLI.
 * Author: Reaktiv Studios
 * Author URI: http://reaktivstudios.com/
 * License: GPL
 *
 * @package Batch_Process
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin class to handle setting constants and loading files and static helper methods.
 */
final class Batch_Processing {
	/**
	 * Define all the constants we need
	 */
	public function define_constants() {
		define( 'BATCH_VERSION', '0.1.0-dev' );
		define( 'BATCH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'BATCH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'BATCH_PLUGIN_FILE', __FILE__ );
	}

	/**
	 * Admin Dashboard.
	 */
	public function add_dashboard() {
		add_menu_page(
			'Batch Processes',
			'Batch Processes',
			'manage_options',
			'batch-processes',
			array( $this, 'dashboard_display' )
		);
	}

	/**
	 * Dashboard display.
	 */
	public function dashboard_display() {
		include BATCH_PLUGIN_DIR . 'templates/dashboard.php';
	}

	/**
	 * Load in all the files we need.
	 */
	public function load_includes() {
		require_once( BATCH_PLUGIN_DIR . '/includes/abstracts/abstract-batch.php' );
		require_once( BATCH_PLUGIN_DIR . '/includes/batch-functions.php' );
	}

	/**
	 * Handle hooks.
	 */
	public function attach_hooks() {
		add_action( 'admin_menu', array( $this, 'add_dashboard' ) );
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->define_constants();
		$this->load_includes();
		$this->attach_hooks();
	}
}

$batch_processing = new Batch_Processing();
$batch_processing->init();
