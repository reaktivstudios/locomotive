<?php

namespace Rkv\Locomotive\Tests;

use WP_UnitTestCase;
use Rkv\Locomotive\Batches\Comments;

class CommentBatchTest extends WP_UnitTestCase {
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
		$this->comments = $this->factory->comment->create_many( 10 );
		$this->user = $this->factory->user->create();
	}


	/**
	 * Test that comment batch runs
   */
	public function test_comments_finished_run() {
		$comment_batch = new Comments();

		$comment_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'comment',
			'callback' => __NAMESPACE__ . '\\my_comment_callback_function',
			'args'     => array(
				'number' => 10,
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $comment_batch->slug );
		$this->assertFalse( $batch_status );

		$comment_batch->run( 1 );

		$batch_status = get_option( 'loco_batch_' . $comment_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );

		// Loop through each post and make sure our value was set.
		foreach ( $this->comments as $comment ) {
			$meta = get_metadata( 'comment', $comment, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_metadata( 'comment', $comment, $comment_batch->slug . '_status', true );
			$this->assertEquals( 'success', $status );
		}

		// Run again so it skips some.
		$comment_batch->run( 1 );
	}

	/**
	 * Test that you can clear individual result status.
	 */
	public function test_clear_comments_result_status() {
		$comment_batch = new Comments();

		$comment_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'comment',
			'callback' => __NAMESPACE__ . '\\my_comment_callback_function',
			'args'     => array(
				'number' => 10,
			),
		) );

		$comment_batch->run( 1 );

		$comment_batch->clear_result_status();

		// Loop through each post and make sure our value was set.
		foreach ( $this->comments as $comment ) {
			$meta = get_metadata( 'comment', $comment, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_metadata( 'comment', $comment, $comment_batch->slug . '_status', true );
			$this->assertEquals( '', $status );
		}

		$batches = locomotive_get_all_batches();
		$this->assertEquals( 'reset', $batches['hey-there']['status'] );
	}

	/**
	 * Test that comments offset batch gets run.
	 */
	public function test_comments_offset_run() {
		$comment_batch = new Comments();

		$comment_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'comment',
			'callback' => __NAMESPACE__ . '\\my_comment_callback_function',
			'args'     => array(
				'number' => 5,
				'offset' => 5,
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $comment_batch->slug );
		$this->assertFalse( $batch_status );

		$comment_batch->run( 2 );

		$batch_status = get_option( 'loco_batch_' . $comment_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

	/**
	 * Test that batch gets run when data is destroyed during process.
	 */
	public function test_run_with_destructive_callback() {

		$comment_batch = new Comments();

		$comment_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'comment',
			'callback' => __NAMESPACE__ . '\\my_callback_delete_comment',
			'args'     => array(
				'number' => 5,
			),
		) );

		// Simulate running twice with pass back of results number from client.
		$comment_batch->run( 1 );
		$_POST['total_num_results'] = 10;
		$comment_batch->run( 2 );

		// Check that all comments have been deleted.
		$all_posts = get_comments();
		$this->assertCount( 0, $all_posts );

		// Ensure that we are still getting a finished message.
		$batch_status = get_option( 'loco_batch_' . $comment_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

}

function my_comment_callback_function( $result ) {
	update_comment_meta( $result->comment_ID, 'custom-key', 'my-value' );
}

/**
 * Callback function with destructive action (deletion).
 *
 * @param  WP_Comment $result Result item.
 */
function my_callback_delete_comment( $result ) {
	wp_delete_comment( $result->comment_ID, true );
}
