<?php

class BatchTest extends WP_UnitTestCase {
	/**
	 * Tear down.
	 */
	function tearDown() {
		parent::tearDown();
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
	 * Make sure adding batch adds to currently registered.
	 */
	function test_register_overwrites_currently_registered_if_same_slug() {
		$batch = $this->register_successful_batch( 'hey' );

		$batch = new Batch_Process\Posts();
		$batch->register( array(
			'name'     => 'My Test Batch process OVERWRITE',
			'slug'     => 'hey',
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$this->assertTrue( ( 'My Test Batch process OVERWRITE' === $batch->currently_registered['hey']['name'] ) );
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
	 * Test that status gets updated on a batch to no results found.
	 */
	public function test_no_results_found() {
		$post_batch = new \Batch_Process\Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertTrue( ( 'no results found' === $batch_status['status'] ) );
	}

	/**
	 * Test that batch gets run.
	 */
	public function test_finished_run() {
		$posts = array();

		// Create 5 posts.
		for ( $x = 0; $x < 5; $x++ ) {
			$posts[] = $this->factory->post->create();
		}

		$post_batch = new \Batch_Process\Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertTrue( ( 'finished' === $batch_status['status'] ) );

		// Loop through each post and make sure our value was set.
		foreach ( $posts as $post ) {
			$meta = get_post_meta( $post, 'custom-key', true );
			$this->assertTrue( ( 'my-value' === $meta ) );

			$status = get_post_meta( $post, $post_batch->slug . '_status', true );
			$this->assertTrue( ( 'success' === $status ) );
		}

		// Run again so it skips some.
		$run = $post_batch->run( 1 );
	}

	/**
	 * Test that you can clear individual result status.
	 */
	public function test_clear_result_status() {
		$posts = array();

		// Create 5 posts.
		for ( $x = 0; $x < 5; $x++ ) {
			$posts[] = $this->factory->post->create();
		}

		$post_batch = new \Batch_Process\Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$run = $post_batch->run( 1 );

		$post_batch->clear_result_status();

		// Loop through each post and make sure our value was set.
		foreach ( $posts as $post ) {
			$meta = get_post_meta( $post, 'custom-key', true );
			$this->assertTrue( ( 'my-value' === $meta ) );

			$status = get_post_meta( $post, $post_batch->slug . '_status', true );
			$this->assertTrue( ( '' === $status ) );
		}

		$batches = \Batch_Process\get_all_batches();
		$this->assertTrue( ( 'reset' === $batches['hey-there']['status'] ) );
	}

	/**
	 * Test that batch gets run.
	 */
	public function test_running_run() {
		// Create 5 posts.
		for ( $x = 0; $x < 15; $x++ ) {
			$this->factory->post->create();
		}

		$post_batch = new \Batch_Process\Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertTrue( ( 'running' === $batch_status['status'] ) );
	}

	/**
	 * Test that batch gets run.
	 */
	public function test_offset_run() {
		// Create 5 posts.
		for ( $x = 0; $x < 13; $x++ ) {
			$this->factory->post->create();
		}

		$post_batch = new \Batch_Process\Posts();
		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 2 );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertTrue( ( 'finished' === $batch_status['status'] ) );
	}

	/**
	 * Test that batch gets run.
	 */
	public function test_run_with_error() {
		$posts = array();

		// Create 5 posts.
		for ( $x = 0; $x < 5; $x++ ) {
			$posts[] = $this->factory->post->create();
		}

		$post_batch = new \Batch_Process\Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test_false',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		$batch_status = get_site_option( $post_batch::BATCH_HOOK_PREFIX . $post_batch->slug );
		$this->assertTrue( ( 'finished' === $batch_status['status'] ) );
	}

	/**
	 * Helper function to register a successful batch.
	 *
	 * @param string $slug Slug of test batch.
	 */
	private function register_successful_batch( $slug = 'test-batch' ) {
		$batch = new Batch_Process\Posts();
		$batch->register( array(
			'name'     => 'My Test Batch process',
			'slug'     => $slug,
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		return $batch;
	}
}

/**
 * My callback function test.
 *
 * @param WP_Post $result Result item.
 */
function my_callback_function_test( $result ) {
	update_post_meta( $result->ID, 'custom-key', 'my-value' );
}

/**
 * My callback function test.
 *
 * @throws Exception Not worknig.
 */
function my_callback_function_test_false() {
	throw new Exception( 'Not working' );
}

