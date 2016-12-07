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
	 * Get the results of the query.
	 */
	public function get_results() {
		// Set some defaults.
		$this->args = wp_parse_args( $this->args, array(
			'post_type'      => 'post',
			'posts_per_page' => get_option( 'posts_per_page' ),
			'offset'         => 0,
		) );

		$this->calculate_offset( $this->args['posts_per_page'] );

		$query = new WP_Query( $this->args );

		// Update the batch object with the total num of results found.
		$this->total_num_results = $query->found_posts;

		return $query->get_posts();
	}
}
