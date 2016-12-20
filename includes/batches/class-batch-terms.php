<?php
/**
 * Terms batch class.
 *
 * @package Locomotive/Batch
 */

namespace Rkv\Locomotive\Batches;

use WP_Term_Query;
use Rkv\Locomotive\Abstracts\Batch;

/**
 * Batch Term class.
 */
class Terms extends Batch {
	/**
	 * The individual batch's parameter for specifying the amount of results to return.
	 *
	 * @var string
	 */
	public $per_batch_param = 'number';

	/**
	 * Default args for the query.
	 *
	 * @var array
	 */
	public $default_args = array(
		'number' => 10,
		'offset' => 0,
	);

	/**
	 * Get results function for the registered batch process.
	 *
	 * @return array \WP_Term_Query->get_results() result.
	 */
	public function batch_get_results() {
		$query = new WP_Term_Query( $this->args );

		// Need to do a count query in order to get all possible terms as an integer.
		$count_args = wp_parse_args( array( 'fields' => 'count', 'offset' => 0 ), $this->args );
		$count = new WP_Term_Query( $count_args );
		$this->total_num_results = $count->get_terms();

		return $query->get_terms();
	}

	/**
	 * Clear the result status for a batch.
	 *
	 * @return bool
	 */
	public function batch_clear_result_status() {
		return delete_metadata( 'term', null, $this->slug . '_status', '', true );
	}

	/**
	 * Get the status of a result.
	 *
	 * @param \WP_Term $result The result we want to get status of.
	 */
	public function get_result_item_status( $result ) {
		return get_term_meta( $result->data->term_id, $this->slug . '_status', true );
	}

	/**
	 * Update the meta info on a result.
	 *
	 * @param \WP_Term $result  The result we want to track meta data on.
	 * @param string   $status  Status of this result in the batch.
	 */
	public function update_result_item_status( $result, $status ) {
		return update_term_meta( $result->data->term_id, $this->slug . '_status', $status );
	}
}
