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
	public $per_batch_param = 'number';

	public $default_args = array(
		'number' => 10,
		'offset' => 0,
	);

	public function individual_get_results() {
		$query = new \WP_User_Query( $this->args );
		$this->total_num_results = $query->get_total();
		return $query->get_results();
	}

	public function individual_clear_result_status() {
		return delete_metadata( 'user', null, $this->slug . '_status', '', true );
	}

	public function individual_get_result_status( $result ) {
		return get_user_meta( $result->data->ID, $this->slug . '_status', true );
	}

	public function individual_update_result_status( $result, $status ) {
		return update_user_meta( $result->data->ID, $this->slug . '_status', $status );
	}
}
