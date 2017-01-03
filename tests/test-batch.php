<?php

namespace Rkv\Locomotive\Tests;

use WP_UnitTestCase;
use Exception;
use Rkv\Locomotive\Batches\Posts;
use Rkv\Locomotive\Batches\Users;
use Rkv\Locomotive\Batches\Terms;


class BatchTest extends WP_UnitTestCase {
	/**
	 * Tear down.
	 */
	public function tearDown() {
		parent::tearDown();
		locomotive_clear_existing_batches();
	}

	/**
	 * Test name is included.
	 */
	public function test_register_batch_includes_name() {
		try {
			$batch_process = new Posts();
			$batch_process->register( array(
				'slug'     => 'test-anot22h12er-batch',
				'type'     => 'post',
				'callback' => 'my_callback_function',
				'args'     => array(
					'posts_per_page' => 10,
					'post_type'      => 'post',
				),
			) );
		} catch ( Exception $e ) {
			$this->assertEquals( 'Batch name must be defined.', $e->getMessage() );
		}

	}

	/**
	 * Test type is included.
	 */
	public function test_register_batch_includes_type() {
		try {
			$batch_process = new Posts();
			$batch_process->register( array(
				'name'     => 'Hey',
				'slug'     => 'test-anot22h12er-batch',
				'callback' => 'my_callback_function',
				'args'     => array(
					'posts_per_page' => 10,
					'post_type'      => 'post',
				),
			) );
		} catch ( Exception $e ) {
			$this->assertEquals( 'Batch type must be defined.', $e->getMessage() );
		}
	}

	/**
	 * Test callback defined
	 */
	public function test_register_batch_includes_callback() {
		try {
			$batch_process = new Posts();
			$batch_process->register( array(
				'name'     => 'Hey',
				'type'     => 'post',
				'slug'     => 'test-anot22h12er-batch',
				'args'     => 0,
			) );
		} catch ( Exception $e ) {
			$this->assertEquals( 'A callback must be defined.', $e->getMessage() );
		}
	}

	/**
	 * Test without arguments
	 */
	public function test_register_batch_doesnt_require_args() {
		$batch_process = new Posts();
		$batch_process->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function',
		) );

		$this->assertNotNull( $batch_process->currently_registered['hey-there'] );
	}

	/**
	 * Make sure slugs get slashes.
	 */
	public function test_register_batch_no_slug_gets_name() {
		$batch_process = new Posts();
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
	public function test_register_overwrites_currently_registered_if_same_slug() {
		$batch = $this->register_successful_batch( 'hey' );

		$batch = new Posts();
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

		$this->assertEquals( 'My Test Batch process OVERWRITE', $batch->currently_registered['hey']['name'] );
	}

	/**
	 * Make sure that when a batch process is registered that `currently_registered`
	 * is an array.
	 */
	public function test_empty_currently_registered_is_array_when_new_batch_added() {
		locomotive_clear_existing_batches();

		$batch_process = new Posts();
		$this->assertTrue( is_array( $batch_process->currently_registered ) );
	}

	/**
	 * Test that status gets updated on a batch to no results found.
	 */
	public function test_no_results_found() {
		$post_batch = new Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertEquals( 'no results found', $batch_status['status'] );
	}

	/**
	 * Test that batch gets run.
	 */
	public function test_running_run() {
		// Create 5 posts.
		for ( $x = 0; $x < 15; $x++ ) {
			$this->factory->post->create();
		}

		$post_batch = new Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => 'my_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertEquals( 'running', $batch_status['status'] );
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

		$post_batch = new Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => __NAMESPACE__ . '\\my_callback_function_test_false',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		// Error is returned.
		$this->assertArrayHasKey( 'errors', $run );

		// Errors return matches class errors object.
		$this->assertCount( 5, $run['errors'] );
		$this->assertCount( 5, $post_batch->result_errors );

		// Sample message is returned properly.
		$this->assertEquals( 'Not working', $run['errors'][0]['message'] );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}


	/**
	 * Helper function to register a successful batch.
	 *
	 * @param string $slug Slug of test batch.
	 */
	private function register_successful_batch( $slug = 'test-batch' ) {
		$batch = new Posts();
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
 * @param object $result Result item.
 * @throws \Exception Not working.
 */
function my_callback_function_test_false( $result ) {
	throw new \Exception( 'Not working' );
}
