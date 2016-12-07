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
		// Set some defaults.
		$this->args = wp_parse_args( $this->args, array(
			'number' => 10,
			'offset' => 0,
		) );

		$this->calculate_offset( $this->args['number'] );

		$query = new WP_User_Query( $this->args );

		// Update the batch object with the total num of results found.
		$this->total_num_results = $query->get_total();

		return $query->get_results();
	}
}
