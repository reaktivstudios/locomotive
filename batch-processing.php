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

namespace Batch_Process;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin class to handle setting constants and loading files and static helper methods.
 */
final class Loader {
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
		$registered_batches = get_all_batches();
		include BATCH_PLUGIN_DIR . 'templates/dashboard.php';
	}

	/**
	 * Load in all the files we need.
	 */
	public function load_includes() {
		require_once( BATCH_PLUGIN_DIR . 'includes/abstracts/abstract-batch.php' );
		require_once( BATCH_PLUGIN_DIR . 'includes/batches/class-batch-posts.php' );
		require_once( BATCH_PLUGIN_DIR . 'includes/batch-functions.php' );
	}

	/**
	 * Handle hooks.
	 */
	public function attach_hooks() {
		add_action( 'admin_menu', array( $this, 'add_dashboard' ) );
		add_action( 'after_setup_theme', array( $this, 'loaded' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

		add_action( 'wp_ajax_run_batch', array( $this, 'run' ) );
		add_action( 'wp_ajax_reset_batch', array( $this, 'reset' ) );
	}

	/**
	 * Plugin stylesheet and JavaScript.
	 */
	public function scripts() {
		wp_enqueue_style( 'batch-process-styles', BATCH_PLUGIN_URL . 'assets/main.css' );
		wp_enqueue_script( 'batch-js', BATCH_PLUGIN_URL . 'assets/dist/batch.min.js', array( 'jquery' ), '0.1.0', true );

		wp_localize_script( 'batch-js', 'batch', array(
			'nonce' => wp_create_nonce( 'run-batch-process' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'batches' => get_all_batches(),
			'page_title' => esc_html( get_admin_page_title() ),
		) );
	}

	/**
	 * Let everyone know we are loaded and ready to go.
	 */
	public function loaded() {
		if ( is_admin() ) {
			clear_existing_batches();
			do_action( 'add_batch_processes' );
		}
	}

	/**
	 * AJAX handler for running a batch.
	 *
	 * @todo Move this to it's own AJAX class.
	 */
	public function run() {
		$errors = array();
		check_ajax_referer( 'run-batch-process', 'nonce' );

		if ( empty( $_POST['batch_process'] ) ) {
			$errors[] = 'Batch process not specified.';
		} else {
			$batch_process = sanitize_text_field( wp_unslash( $_POST['batch_process'] ) );
		}

		if ( empty( $_POST['step'] ) ) {
			$errors[] = 'Step must be defined.';
		} else {
			$step = absint( $_POST['step'] );
		}

		if ( $errors ) {
			wp_send_json( array(
				'success' => false,
				'errors' => $errors,
			) );
		}

		do_action( Batch::BATCH_HOOK_PREFIX . $batch_process, $step );
	}

	/**
	 * AJAX handler for running a batch.
	 *
	 * @todo Move this to it's own AJAX class.
	 */
	public function reset() {
		$errors = array();
		check_ajax_referer( 'run-batch-process', 'nonce' );

		if ( empty( $_POST['batch_process'] ) ) {
			$errors[] = 'Batch process not specified.';
		} else {
			$batch_process = sanitize_text_field( wp_unslash( $_POST['batch_process'] ) );
		}

		if ( $errors ) {
			wp_send_json( array(
				'success' => false,
				'errors' => $errors,
			) );
		}

		do_action( Batch::BATCH_HOOK_PREFIX . $batch_process . '_reset' );

		wp_send_json( array( 'success' => true ) );
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

$batch_processing = new Loader();
$batch_processing->init();
