<?php

class BatchTest extends WP_UnitTestCase {
	function setUp() { }
	function tearDown() {
		\Batch_Process\clear_existing_batches();
	}

	/**
	 * Test name is included.
	 *
	 * @expectedExceptionMessage Batch name must be provided.
	 */
	function test_register_batch_includes_name() {
		$this->setExpectedException( 'Exception' );

		$batch_process = new \Batch_Process\Posts();
		$batch_process->register( array(
			'slug'     => 'test-anot22h12er-batch',
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );
	}

	/**
	 * Test name is included.
	 *
	 * @expectedExceptionMessage Type of batch must be defined.
	 */
	function test_register_batch_includes_type() {
		$this->setExpectedException( 'Exception' );

		$batch_process = new \Batch_Process\Posts();
		$batch_process->register( array(
			'name'     => 'Hey',
			'slug'     => 'test-anot22h12er-batch',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );
	}

	/**
	 * Test array args is included.
	 *
	 * @expectedExceptionMessage An array of args must be defined.
	 */
	function test_register_batch_includes_callback() {
		$this->setExpectedException( 'Exception' );

		$batch_process = new \Batch_Process\Posts();
		$batch_process->register( array(
			'name'     => 'Hey',
			'type'     => 'post',
			'slug'     => 'test-anot22h12er-batch',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );
	}

	/**
	 * Test callback defined
	 *
	 * @expectedExceptionMessage A callback must be defined.
	 */
	function test_register_batch_includes_args() {
		$this->setExpectedException( 'Exception' );

		$batch_process = new \Batch_Process\Posts();
		$batch_process->register( array(
			'name'     => 'Hey',
			'type'     => 'post',
			'slug'     => 'test-anot22h12er-batch',
			'args'     => 0,
		) );
	}

	/**
	 * Make sure slugs get slashes.
	 */
	function test_register_batch_no_slug_gets_name() {
		$batch_process = new \Batch_Process\Posts();
		$batch_process->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$this->assertNotNull( $batch_process->currently_registered['hey-there'] );
	}

	/**
	 * Make sure that when a batch process is registered that `currently_registered`
	 * is an array.
	 */
	public function test_empty_currently_registered_is_array_when_new_batch_added() {
		\Batch_Process\clear_existing_batches();

		$batch_process = new \Batch_Process\Posts();
		$this->assertTrue( is_array( $batch_process->currently_registered ) );
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

