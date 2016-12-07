<?php
/**
 * Posts batch class.
 *
 * @package Locomotive/Batch
 */

namespace Rkv\Locomotive\Batches;

use WP_Query;
use Rkv\Locomotive\Abstracts\Batch;

/**
 * Batch Posts class.
 */
class Posts extends Batch {
	/**
	 * Default arguments for this batch.
	 *
	 * @var array
	 */
	public $default_args = array(
		'post_type'      => 'post',
		'posts_per_page' => 10,
		'offset'         => 0,
	);

	/**
	 * Clear the result status for the registered batch process.
	 *
	 * @return mixed
	 */
	public function individual_clear_result_status() {
		delete_post_meta_by_key( $this->slug . '_status' );
	}

	/**
	 * Get the status for an individual result for the registered batch process.
	 *
	 * @param mixed $result The result we are requesting status of.
	 *
	 * @return mixed
	 */
	public function individual_get_result_status( $result ) {
		return get_post_meta( $result->ID, $this->slug . '_status', true );
	}
}
