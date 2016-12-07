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
	public $default_args = array(
		'post_type'      => 'post',
		'posts_per_page' => 10,
		'offset'         => 0,
	);

	public function individual_clear_result_status() {
		return delete_post_meta_by_key( $this->slug . '_status' );
	}

	public function individual_get_result_status( $result ) {
		return get_post_meta( $result->ID, $this->slug . '_status', true );
	}

	public function individual_update_result_status( $result, $status ) {
		return update_post_meta( $result->ID, $this->slug . '_status', $status );
	}
}
