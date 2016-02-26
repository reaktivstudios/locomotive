<?php

class BatchFunctionTest extends WP_UnitTestCase {
	function setUp() { }
	function tearDown() {
		\Batch_Process\clear_existing_batches();
	}

	/**
	 * Test successful batch registers.
	 */
	function test_successful_register_batch() {
		$this->register_successful_batch( '1' );

		$all_batches = \Batch_Process\get_all_batches();
		$this->assertCount( 1, $all_batches );
	}

	/**
	 * Test unsupported type of batch.
	 */
	function test_register_unsupported_type() {
		$this->setExpectedException( 'Exception' );

		Batch_Process\register( array(
			'name'     => 'My Test Batch process',
			'slug'     => 'test-anot22h12er-batch',
			'type'     => 'notTrue',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$all_batches = \Batch_Process\get_all_batches();
		$this->assertCount( 0, $all_batches );
	}

	/**
	 * Test that we can get all batches with proper data.
	 */
	function test_all_batches() {
		$this->register_successful_batch( 'my-batch' );
		$batches = \Batch_Process\get_all_batches();

		$this->assertNotNull( $batches['my-batch']['last_run'] );
		$this->assertNotNull( $batches['my-batch']['status'] );
	}

	/**
	 * Test that we can clear existing batches.
	 */
	function test_clear_batches() {
		$this->register_successful_batch( 'hello' );
		$batches = \Batch_Process\get_all_batches();
		$this->assertCount( 1, $batches );

		\Batch_Process\clear_existing_batches();
		$batches = \Batch_Process\get_all_batches();
		$this->assertCount( 0, $batches );
	}

	/**
	 * Test time_ago() returns what we expect;
	 */
	function test_time_ago() {
		$time = current_time( 'timestamp' );
		$time_ago = \Batch_Process\time_ago( $time );

		$this->assertEquals( '1 min ago', $time_ago );
	}

	/**
	 * Helper function to register a successful batch.
	 *
	 * @param string $slug Slug of test batch.
	 */
	private function register_successful_batch( $slug = 'test-batch' ) {
		Batch_Process\register( array(
			'name'     => 'My Test Batch process',
			'slug'     => $slug,
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );
	}
}

