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
	 *
	 * @param int $offset The offset to use for querying data.
	 */
	public function get_results( $offset ) {
		// Set some defaults.
		$this->args = wp_parse_args( $this->args, array(
			'post_type'      => 'post',
			'posts_per_page' => get_option( 'posts_per_page' ),
		) );

		// Make sure we set this specifically as it should change as this gets run..
		$this->args['offset'] = $offset;

		$query = new \WP_Query( $this->args );

		return $query->get_posts();
	}
}
