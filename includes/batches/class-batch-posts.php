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
		$this->args = wp_parse_args( $this->args, array(
			'post_type'      => 'post',
			'posts_per_page' => get_option( 'posts_per_page' ),
			'offset'         => 0,
		) );

		return $this->base_get_results();
	}
}
