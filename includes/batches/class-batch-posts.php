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

		if ( 1 !== $this->current_step ) {
			// Example: step 2: 1 * 10 = offset of 10, step 3: 2 * 10 = offset of 20.
			$this->args['offset'] = ( ( $this->current_step - 1 ) * $this->args['posts_per_page'] );
		}

		$query = new WP_Query( $this->args );

		// Update the batch object with the total num of results found.
		$this->total_num_results = $query->found_posts;

		return $query->get_posts();
	}
}
