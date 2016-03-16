<?php
/**
 * Users batch class.
 *
 * @package Batch_Process/Batch
 */

namespace Batch_Process;

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

		if ( 1 !== $this->current_step ) {
			// Example: step 2: 1 * 10 = offset of 10, step 3: 2 * 10 = offset of 20.
			$this->args['offset'] = ( ( $this->current_step - 1 ) * $this->args['number'] );
		}

		$query = new \WP_User_Query( $this->args );

		// Update the batch object with the total num of results found.
		$this->total_num_results = $query->total_users();

		return $query->get_results();
	}
}
