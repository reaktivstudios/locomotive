<?php
/**
 * Plugin Name: Locomotive
 * Version: 0.1.0
 * Description: Run custom batch processes from the WP admin.
 * Author: Reaktiv Studios
 * Author URI: http://reaktivstudios.com/
 * Text Domain: locomotive
 * Domain Path: languages
 * License: GPL
 *
 * @package Locomotive
 */

namespace Rkv\Locomotive;

use Rkv\Locomotive\Abstracts\Batch;

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
		define( 'LOCO_VERSION', '0.1.0-dev' );
		define( 'LOCO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'LOCO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		define( 'LOCO_PLUGIN_FILE', __FILE__ );
	}

	/**
	 * Admin Dashboard.
	 */
	public function add_dashboard() {
		add_menu_page(
			'Locomotive',
			'Locomotive',
			'manage_options',
			'locomotive',
			array( $this, 'dashboard_display' )
		);
	}

	/**
	 * Dashboard display.
	 */
	public function dashboard_display() {
		$registered_batches = locomotive_get_all_batches();
		include LOCO_PLUGIN_DIR . 'templates/dashboard.php';
	}

	/**
	 * Load in all the files we need.
	 */
	public function load_includes() {
		require_once( LOCO_PLUGIN_DIR . 'includes/abstracts/abstract-batch.php' );
		require_once( LOCO_PLUGIN_DIR . 'includes/batches/class-batch-posts.php' );
		require_once( LOCO_PLUGIN_DIR . 'includes/batches/class-batch-users.php' );
		require_once( LOCO_PLUGIN_DIR . 'includes/batches/class-batch-terms.php' );
		require_once( LOCO_PLUGIN_DIR . 'includes/functions.php' );
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
	 *
	 * @param string $hook The current page loaded in the WP admin.
	 */
	public function scripts( $hook ) {

		// Exclude our scripts and CSS files from loading globally.
		if ( 'toplevel_page_locomotive' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'batch-process-styles', LOCO_PLUGIN_URL . 'assets/main.css' );
		wp_enqueue_script( 'batch-js', LOCO_PLUGIN_URL . 'assets/dist/batch.min.js', array( 'jquery' ), '0.1.0', true );

		wp_localize_script( 'batch-js', 'batch', array(
			'nonce' => wp_create_nonce( 'run-batch-process' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'batches' => locomotive_get_all_batches(),
			'page_title' => esc_html( get_admin_page_title() ),
		) );
	}

	/**
	 * Let everyone know we are loaded and ready to go.
	 */
	public function loaded() {
		if ( is_admin() ) {
			locomotive_clear_existing_batches();
			do_action( 'locomotive_init' );
		}
	}

	/**
	 * AJAX handler for running a batch.
	 *
	 * @todo Move this to it's own AJAX class.
	 */
	public function run() {
		$batch_process = '';
		$step = 0;
		$errors = array();

		check_ajax_referer( 'run-batch-process', 'nonce' );

		if ( empty( $_POST['batch_process'] ) ) {
			$errors[] = __( 'Batch process not specified.', 'locomotive' );
		} else {
			$batch_process = sanitize_text_field( wp_unslash( $_POST['batch_process'] ) );
		}

		if ( empty( $_POST['step'] ) ) {
			$errors[] = __( 'Step must be defined.', 'locomotive' );
		} else {
			$step = absint( $_POST['step'] );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json( array(
				'success' => false,
				'errors' => $errors,
			) );
		}

		do_action( 'loco_batch_' . $batch_process, $step );
	}

	/**
	 * AJAX handler for running a batch.
	 *
	 * @todo Move this to it's own AJAX class.
	 */
	public function reset() {
		$batch_process = '';
		$errors = array();

		check_ajax_referer( 'run-batch-process', 'nonce' );

		if ( empty( $_POST['batch_process'] ) ) {
			$errors[] = __( 'Batch process not specified.', 'locomotive' );
		} else {
			$batch_process = sanitize_text_field( wp_unslash( $_POST['batch_process'] ) );
		}

		if ( ! empty( $errors ) ) {
			wp_send_json( array(
				'success' => false,
				'errors' => $errors,
			) );
		}

		do_action( 'loco_batch_' . $batch_process . '_reset' );

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
