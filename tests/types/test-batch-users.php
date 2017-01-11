<?php

namespace Rkv\Locomotive\Tests;

use WP_UnitTestCase;
use Rkv\Locomotive\Batches\Users;

class UserBatchTest extends WP_UnitTestCase {
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
		$this->users = $this->factory->user->create_many( 9 );
	}


	/**
	 * Test that user batch gets run.
	 */
	public function test_user_finished_run() {
		$user_batch = new Users();

		$user_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'user',
			'callback' => __NAMESPACE__ . '\\my_user_callback_function_test',
			'args'     => array(
				'number' => 10,
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $user_batch->slug );
		$this->assertFalse( $batch_status );

		$user_batch->run( 1 );

		$batch_status = get_option( 'loco_batch_' . $user_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );

		// Loop through each post and make sure our value was set.
		foreach ( $this->users as $user ) {
			$meta = get_user_meta( $user, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_user_meta( $user, $user_batch->slug . '_status', true );
			$this->assertEquals( 'success', $status );
		}

		// Run again so it skips some.
		$user_batch->run( 1 );
	}

	/**
	 * Test that you can clear individual result status.
	 */
	public function test_clear_user_result_status() {
		$user_batch = new Users();

		$user_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'user',
			'callback' => __NAMESPACE__ . '\\my_user_callback_function_test',
			'args'     => array(
				'number' => 10,
			),
		) );

		$user_batch->run( 1 );

		$user_batch->clear_result_status();

		// Loop through each post and make sure our value was set.
		foreach ( $this->users as $user ) {
			$meta = get_user_meta( $user, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_user_meta( $user, $user_batch->slug . '_status', true );
			$this->assertEquals( '', $status );
		}

		$batches = locomotive_get_all_batches();
		$this->assertEquals( 'reset', $batches['hey-there']['status'] );
	}

	/**
	 * Test that users offset batch gets run.
	 */
	public function test_users_offset_run() {
		$user_batch = new Users();

		$user_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'user',
			'callback' => __NAMESPACE__ . '\\my_user_callback_function_test',
			'args'     => array(
				'number' => 5,
				'offset' => 5,
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $user_batch->slug );
		$this->assertFalse( $batch_status );

		$user_batch->run( 2 );

		$batch_status = get_option( 'loco_batch_' . $user_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

	public function test_run_with_destructive_callback() {

		$user_batch = new Users();

		$user_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'user',
			'callback' => __NAMESPACE__ . '\\my_callback_delete_user',
			'args'     => array(
				'number' => 5,
			),
		) );

		// Simulate running twice with pass back of results number from client.
		$user_batch->run( 1 );
		$_POST['total_num_results'] = 9;
		$user_batch->run( 2 );

		// Check that all users have been deleted.
		$all_users = get_users();
		$this->assertCount( 0, $all_users );

		// Ensure that we are still getting a finished message.
		$batch_status = get_option( 'loco_batch_' . $user_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

}

/**
 * My callback function test for users.
 *
 * @param WP_User $result Result item.
 */
function my_user_callback_function_test( $result ) {
	update_metadata( 'user', $result->ID, 'custom-key', 'my-value' );
}

/**
 * Callback function with destructive action (deletion).
 *
 * @param  WP_User $result Result item.
 */
function my_callback_delete_user( $result ) {
	wp_delete_user( $result->ID );
}
