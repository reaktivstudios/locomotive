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

		$run = $site_batch->run( 1 );

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
		$run = $site_batch->run( 1 );
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

		$run = $site_batch->run( 1 );

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

		$run = $site_batch->run( 2 );

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
