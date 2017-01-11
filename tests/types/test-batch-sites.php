<?php

namespace Rkv\Locomotive\Tests;

use WP_UnitTestCase;
use Rkv\Locomotive\Batches\Sites;

class SiteBatchTest extends WP_UnitTestCase {
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
		$this->blogs = $this->factory->blog->create_many( 5 );
		$this->user = $this->factory->user->create();
	}

	protected function checkRequirements() {
		parent::checkRequirements();

		$annotations = $this->getAnnotations();

		foreach ( array( 'class', 'method' ) as $depth ) {
			if ( empty( $annotations[ $depth ]['requires'] ) ) {
				continue;
			}

			$requires = array_flip( $annotations[ $depth ]['requires'] );
			if ( isset( $requires['Multisite'] ) && ! is_multisite() ) {
				$this->markTestSkipped( 'Multisite must be enabled' );
			}
		}
	}


	/**
	 * Test that site batch runs
	 *
	 * @requires Multisite
	 */
	public function test_site_finished_run() {
		$site_batch = new Sites();

		$site_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'site',
			'callback' => __NAMESPACE__ . '\\my_site_callback_function_test',
			'args'     => array(
				'number' => 10,
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $site_batch->slug );
		$this->assertFalse( $batch_status );

		$site_batch->run( 1 );

		$batch_status = get_option( 'loco_batch_' . $site_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );

		// Loop through each post and make sure our value was set.
		foreach ( $this->blogs as $site ) {
			$meta = get_metadata( 'site', $site, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_metadata( 'site', $site, $site_batch->slug . '_status', true );
			$this->assertEquals( 'success', $status );
		}

		// Run again so it skips some.
		$site_batch->run( 1 );
	}

	/**
	 * Test that you can clear individual result status.
	 *
	 * @requires Multisite
	 */
	public function test_clear_site_result_status() {
		$site_batch = new Sites();

		$site_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'site',
			'callback' => __NAMESPACE__ . '\\my_site_callback_function_test',
			'args'     => array(
				'number' => 7,
			),
		) );

		$site_batch->run( 1 );

		$site_batch->clear_result_status();

		// Loop through each post and make sure our value was set.
		foreach ( $this->blogs as $site ) {
			$meta = get_metadata( 'site', $site, 'custom-key', true );
			$this->assertEquals( 'my-value', $meta );

			$status = get_metadata( 'site', $site, $site_batch->slug . '_status', true );
			$this->assertEquals( '', $status );
		}

		$batches = locomotive_get_all_batches();
		$this->assertEquals( 'reset', $batches['hey-there']['status'] );
	}

	/**
	 * Test that sites offset batch gets run.
	 *
	 * @requires Multisite
	 */
	public function test_users_offset_run() {
		$site_batch = new Sites();

		$site_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'site',
			'callback' => __NAMESPACE__ . '\\my_site_callback_function_test',
			'args'     => array(
				'number' => 3,
				'offset' => 3,
			),
		) );

		$batch_status = get_option( 'loco_batch_' . $site_batch->slug );
		$this->assertFalse( $batch_status );

		$site_batch->run( 2 );

		$batch_status = get_option( 'loco_batch_' . $site_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

	/**
	 * Test that batch gets run when data is destroyed during process.
	 *
	 * @requires Multisite
	 */
	public function test_run_with_destructive_callback() {

		$site_batch = new Sites();

		// Offset to account for main site
		$site_batch->register( array(
			'name'     => 'Hey there',
			'type'     => 'site',
			'callback' => __NAMESPACE__ . '\\my_callback_delete_site',
			'args'     => array(
				'number' => 3,
				'offset' => 1
			),
		) );

		// Simulate running twice with pass back of results number from client.
		$site_batch->run( 1 );
		$_POST['total_num_results'] = 5;
		$site_batch->run( 2 );

		// Check that all sites have been deleted.
		// Ignore site ID 1 since that is the main site
		$all_sites = get_sites( array( 'site__not_in' => array( 1 ) ) );
		$this->assertCount( 0, $all_sites );

		// Ensure that we are still getting a finished message.
		$batch_status = get_option( 'loco_batch_' . $site_batch->slug );
		$this->assertEquals( 'finished', $batch_status['status'] );
	}

}

/**
 * My callback function test for sites.
 *
 * @param WP_Site $result Result item.
 */
function my_site_callback_function_test( $result ) {
	update_metadata( 'site', $result->blog_id, 'custom-key', 'my-value' );
}

/**
 * Callback function with destructive action (deletion).
 *
 * @param  WP_Site $result Result item.
 */
function my_callback_delete_site( $result ) {
	wpmu_delete_blog( $result->blog_id, true );
}
