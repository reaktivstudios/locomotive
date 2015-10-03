<?php
/**
 * Abstract batch class.
 *
 * @package Batch_Process/Batch
 */

namespace Batch_Process;

/**
 * Batch Posts class.
 */
class Posts extends Batch {
	/**
	 * Get the results of the query.
	 */
	public function get_results() {
		// Set some defaults.
		$this->args = wp_parse_args( $this->args, array(
			'post_type'      => 'post',
			'posts_per_page' => get_option( 'posts_per_page' ),
			'offset'         => 0,
		) );

		// Make sure we set this specifically as it should change as this gets run.
		$this->args['offset'] = $offset;

		$query = new \WP_Query( $this->args );

		// Update the batch object with the total num of results found.
		$this->total_num_results = $query->found_posts;

		return $query->get_posts();
	}
}
