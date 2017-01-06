<?php

namespace Rkv\Locomotive\Tests;

use WP_UnitTestCase;
use Rkv\Locomotive\Batches\Posts;

class PostBatchTest extends WP_UnitTestCase {
	/**
	 * Tear down.
	 */
	public function tearDown() {
		parent::tearDown();
		locomotive_clear_existing_batches();
	}

	/**
	 * Tear down.
	 */
	public function setUp() {
		parent::setUp();
		$this->posts = $this->factory->post->create_many( 10 );
	}


	/**
	 * Test that post batch gets run.
	 */
	public function test_post_finished_run() {

		$post_batch = new Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => __NAMESPACE__ . '\\my_post_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );

		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 1 );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );

		// Loop through each post and make sure our value was set.
		foreach ( $this->posts as $post ) {
			$meta = get_post_meta( $post, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_post_meta( $post, $post_batch->slug . '_status', true );
			$this->assertEquals( 'success', $status );
		}

		// Run again so it skips some.
		$run = $post_batch->run( 1 );
	}

	/**
	 * Test that you can clear individual result status.
	 */
	public function test_clear_result_status() {

		$post_batch = new Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => __NAMESPACE__ . '\\my_post_callback_function_test',
			'args'     => array(
				'posts_per_page' => 10,
				'post_type'      => 'post',
			),
		) );

		$run = $post_batch->run( 1 );

		$post_batch->clear_result_status();

		// Loop through each post and make sure our value was set.
		foreach ( $this->posts as $post ) {
			$meta = get_post_meta( $post, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_post_meta( $post, $post_batch->slug . '_status', true );
			$this->assertEquals( '', $status );
		}

		$batches = locomotive_get_all_batches();
		$this->assertEquals( 'reset', $batches['hey-there']['status'] );
	}

	/**
	 * Test that batch gets run.
	 */
	public function test_offset_run() {

		$post_batch = new Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => __NAMESPACE__ . '\\my_post_callback_function_test',
			'args'     => array(
				'posts_per_page' => 5,
				'post_type'      => 'post',
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertFalse( $batch_status );

		$run = $post_batch->run( 2 );

		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

	/**
	 * Test that batch gets run.
	 */
	public function test_run_with_destructive_callback() {

		$post_batch = new Posts();

		$post_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'post',
			'callback' => __NAMESPACE__ . '\\my_callback_delete_post',
			'args'     => array(
				'posts_per_page' => 5,
				'post_type'      => 'post',
			),
		) );

		// Simulate running twice with pass back of results number from client.
		$post_batch->run( 1 );
		$_POST['total_num_results'] = 10;
		$post_batch->run( 2 );

		// Check that all posts have been deleted.
		$all_posts = get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
		$this->assertCount( 0, $all_posts );

		// Ensure that we are still getting a finished message.
		$batch_status = get_option( 'loco_batch_' . $post_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

}

/**
 * My callback function test for posts.
 *
 * @param WP_Post $result Result item.
 */
function my_post_callback_function_test( $result ) {
	update_post_meta( $result->ID, 'custom-key', 'my-value' );
}

/**
 * Callback function with destructive action (deletion).
 *
 * @param  WP_Post $result Result item.
 */
function my_callback_delete_post( $result ) {
	wp_delete_post( $result->ID, true );
}
