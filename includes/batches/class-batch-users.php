<?php
/**
 * Users batch class.
 *
 * @package Locomotive/Batch
 */

namespace Rkv\Locomotive\Batches;

use WP_User_Query;
use Rkv\Locomotive\Abstracts\Batch;

/**
 * Batch Users class.
 */
class Users extends Batch {
	/**
	 * Default arguments for this batch.
	 *
	 * @var array
	 */
	public $default_args = array(
		'number' => 10,
		'offset' => 0,
	);

	/**
	 * Clear the result status for the registered batch process.
	 *
	 * @return bool
	 */
	public function individual_clear_result_status() {
		return delete_metadata( 'user', null, $this->slug . '_status', '', true );
	}

	/**
	 * Get the status for an individual result for the registered batch process.
	 *
	 * @param mixed $result The result we are requesting status of.
	 *
	 * @return mixed
	 */
	public function individual_get_result_status( $result ) {
		return get_user_meta( $result->data->ID, $this->slug . '_status', true );
	}
}
