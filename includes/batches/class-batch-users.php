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
	 * Get the results of the query.
	 */
	public function get_results() {
		$this->args = wp_parse_args( $this->args, array(
			'number' => 10,
			'offset' => 0,
		) );

		return $this->base_get_results();
	}
}
