<?php

namespace Rkv\Locomotive;

class BatchFunctionTest extends \WP_UnitTestCase {
	function tearDown() {
		parent::tearDown();
		clear_existing_batches();

		// Manually dequeue the CSS and JS files.
		wp_dequeue_style( 'batch-process-styles' );
		wp_dequeue_script( 'batch-js' );
	}

	/**
	 * Test successful batch registers.
	 */
	function test_successful_register_batch() {
		$this->register_successful_batch( '1' );

		$all_batches = get_all_batches();
		$this->assertCount( 1, $all_batches );

		register( array(
			'name'     => 'My Test Batch process',
			'type'     => 'user',
			'callback' => __NAMESPACE__ . 'my_callback_function',
			'args'     => array(
				'number' => 10,
			),
		) );

		$all_batches = get_all_batches();
		$this->assertCount( 2, $all_batches );
	}

	/**
	 * Test unsupported type of batch.
	 *
	 * @expectedExceptionMessage Type not supported.
	 */
	function test_register_empty_type() {
		$this->setExpectedException( 'Exception' );

		register( array(
			'name'     => 'My Test Batch process',
			'slug'     => 'test-anot22h12er-batch',
			'callback' => __NAMESPACE__ . 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );
	}

	/**
	 * Test unsupported type of batch.
	 *
	 * @expectedExceptionMessage Type not supported.
	 */
	function test_register_unsupported_type() {
		$this->setExpectedException( 'Exception' );

		register( array(
			'name'     => 'My Test Batch process',
			'slug'     => 'test-anot22h12er-batch',
			'type'     => 'notTrue',
			'callback' => __NAMESPACE__ . 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$all_batches = get_all_batches();
		$this->assertCount( 0, $all_batches );
	}

	/**
	 * Test that we can get all batches with proper data.
	 */
	function test_all_batches() {
		$this->register_successful_batch( 'my-batch' );
		$batches = get_all_batches();

		$this->assertNotNull( $batches['my-batch']['last_run'] );
		$this->assertNotNull( $batches['my-batch']['status'] );

		$post_batch = new Posts();
		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => __NAMESPACE__ . 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$post_batch->run( 1 );
		$batches = get_all_batches();

		$this->assertTrue( ( 'no results found' === $batches['hey-there']['status'] ) );
	}

	/**
	 * Test that we can clear existing batches.
	 */
	function test_clear_batches() {
		$this->register_successful_batch( 'hello' );
		$batches = get_all_batches();
		$this->assertCount( 1, $batches );

		clear_existing_batches();
		$batches = get_all_batches();
		$this->assertCount( 0, $batches );
	}

	/**
	 * Test time_ago() returns what we expect;
	 */
	function test_time_ago() {
		$time = current_time( 'timestamp' );
		$time_ago = time_ago( $time );

		$this->assertEquals( '1 min ago', $time_ago );
	}

	/**
	 * Confirm our CSS and JS assets are loading inside our settings page.
	 */
	function test_asset_loading() {

		// Check that the items are not enquened before we start.
		$this->assertFalse( wp_style_is( 'batch-process-styles', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'batch-js', 'enqueued' ) );

		// Call our loader class on the locomotive settings page.
		$this->load_admin_enqueue_hook( 'toplevel_page_locomotive' );

		// Check that the items are enquened.
		$this->assertTrue( wp_style_is( 'batch-process-styles', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'batch-js', 'enqueued' ) );
	}

	/**
	 * Confirm our CSS and JS assets are not loading outside our settings page.
	 */
	function test_asset_not_loading() {

		// Check that the items are not enquened before we start.
		$this->assertFalse( wp_style_is( 'batch-process-styles', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'batch-js', 'enqueued' ) );

		// Call our loader class on the general options page.
		$this->load_admin_enqueue_hook( 'options-general.php' );

		// Check that our assets aren't enquened.
		$this->assertFalse( wp_style_is( 'batch-process-styles', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'batch-js', 'enqueued' ) );
	}

	/**
	 * Helper function to register a successful batch.
	 *
	 * @param string $slug Slug of test batch.
	 */
	private function register_successful_batch( $slug = 'test-batch' ) {
		register( array(
			'name'     => 'My Test Batch process',
			'slug'     => $slug,
			'type'     => 'post',
			'callback' => __NAMESPACE__ . 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );
	}

	/**
	 * Helper function to call our script loader function.
	 *
	 * @param string $hook The hook that is passed to admin_enqueue_scripts.
	 */
	private function load_admin_enqueue_hook( $hook = '' ) {

		// Call our loader class.
		$this->admin_page = new Loader;

		// Set the admin hook to the requested page.
		$this->admin_page->scripts( $hook );
	}
}

