<?php
/**
 * Abstract batch class.
 *
 * @package Locomotive/Batch
 */

namespace Rkv\Locomotive\Abstracts;

use Exception;

/**
 * Abstract batch class.
 */
abstract class Batch {
	/**
	 * Name of the batch process.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Slug name for the batch process.
	 *
	 * @var string
	 */
	public $slug;

	/**
	 * The individual batch's parameter for specifying the amount of results to return.
	 *
	 * Can / should be overwritten within the class that extends this abstract class.
	 *
	 * @var string
	 */
	public $per_batch_param = 'posts_per_page';

	/**
	 * Args for the batch query.
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Default args for the query.
	 *
	 * Should be implemented on the classes that extend this class.
	 *
	 * @var array
	 */
	public $default_args = array();

	/**
	 * Tyoe of batch.
	 *
	 * @var array
	 */
	public $type;

	/**
	 * Callback function to run on each result of query.
	 *
	 * @var array
	 */
	public $callback;

	/**
	 * Currently registered batches.
	 *
	 * @var array
	 */
	public $currently_registered = array();

	/**
	 * Current step this batch is on.
	 *
	 * @var array
	 */
	public $current_step = 0;

	/**
	 * Total number of results.
	 *
	 * @var array
	 */
	public $total_num_results;

	/**
	 * Holds difference between total from client and total from query, if one exists.
	 *
	 * @var int
	 */
	public $difference_in_result_totals = 0;

	/**
	 * Errors from results
	 *
	 * @var array
	 */
	public $result_errors = array();

	/**
	 * Get results function for the registered batch process.
	 *
	 * @return array
	 */
	abstract public function batch_get_results();

	/**
	 * Clear the result status for the registered batch process.
	 *
	 * @return bool
	 */
	abstract public function batch_clear_result_status();

	/**
	 * Get the result status for a given result item.
	 *
	 * @param mixed $result The result we are requesting status of.
	 *
	 * @return mixed
	 */
	abstract public function get_result_item_status( $result );

	/**
	 * Update the result status for a result item.
	 *
	 * @param mixed  $result The result we are updating the status of.
	 * @param string $status The status to set.
	 *
	 * @return bool
	 */
	abstract public function update_result_item_status( $result, $status );

	/**
	 * Main plugin method for querying data.
	 *
	 * @since 0.1
	 *
	 * @return mixed An array of data to be processed in bulk fashion.
	 */
	public function get_results() {
		$this->args = wp_parse_args( $this->args, $this->default_args );
		$results = $this->batch_get_results();
		$this->calculate_offset();
		return $results;
	}

	/**
	 * Set the total number of results
	 *
	 * Uses a number passed from the client to the server and compares it to the total objects
	 * pulled by the latest query. If the dataset is larger, we increase the total_num_results number.
	 * Otherwise, keep it at the original (to acount for deletion / changes).
	 *
	 * @param int $total_from_query Total number of results from latest query.
	 */
	public function set_total_num_results( $total_from_query ) {
		// If this is past step 1, the client is passing back the total number of results.
		// This accounts for deletion / descructive actions to the data.
		$total_from_request = isset( $_POST['total_num_results'] ) ? absint( $_POST['total_num_results'] ) : 0; // Input var okay.

		// We need to check to see if there is any new data that has been added.
		if ( $total_from_query > $total_from_request ) {
			$this->total_num_results = (int) $total_from_query;
		} else {
			$this->total_num_results = (int) $total_from_request;
		}

		$this->record_change_if_totals_differ( $total_from_request, $total_from_query );
	}

	/**
	 * If the amount of total records has changed, the amount is recorded so that it can
	 * be applied to the offeset when it is calculated. This ensures that the offset takes into
	 * account if new objects have been added or removed from the query.
	 *
	 * @param  int $total_from_request    Total number of results passed up from client.
	 * @param  int $total_from_query      Total number of results retreived from query.
	 */
	public function record_change_if_totals_differ( $total_from_request, $total_from_query ) {
		if ( $total_from_query !== $total_from_request && $total_from_request > 0 ) {
			$this->difference_in_result_totals = $total_from_request - $total_from_query;
		}
	}

	/**
	 * Calculate the offset for the current query.
	 */
	public function calculate_offset() {
		if ( 1 !== $this->current_step ) {
			// Example: step 2: 1 * 10 = offset of 10, step 3: 2 * 10 = offset of 20.
			// Also subtracting by results changed to account for deleted and added objects.
			$this->args['offset'] = ( ( $this->current_step - 1 ) * $this->args[ $this->per_batch_param ] ) - $this->difference_in_result_totals;
		}
	}

	/**
	 * Register the batch process so we can run it.
	 *
	 * @param  array $args Details about the batch you are registering.
	 */
	public function register( $args ) {
		if ( $this->setup( $args ) ) {
			if ( ! defined( 'DOING_AJAX' ) ) {
				$this->add();
			}
		}
	}

	/**
	 * Add a batch process to our system.
	 */
	private function add() {
		if ( ! isset( $this->currently_registered[ $this->slug ] ) ) {
			$this->currently_registered[ $this->slug ] = array(
				'name' => $this->name,
			);
		} else {
			$this->currently_registered[ $this->slug ]['name'] = $this->name;
		}

		return locomotive_update_registered_batches( $this->currently_registered );
	}

	/**
	 * Setup our Batch object to have everything it needs (callback, name, slug,
	 * etc).
	 *
	 * @todo Research the best way to handle exceptions.
	 *
	 * @param  array $args Array of args for register.
	 * @throws Exception Type must be provided.
	 * @return true|exception
	 */
	private function setup( $args ) {
		if ( empty( $args['name'] ) ) {
			throw new Exception( __( 'Batch name must be defined.', 'locomotive' ) );
		} else {
			$this->name = $args['name'];
		}

		if ( empty( $args['slug'] ) ) {
			$this->slug = sanitize_title_with_dashes( $args['name'] );
		} else {
			$this->slug = $args['slug'];
		}

		if ( empty( $args['type'] ) ) {
			throw new Exception( __( 'Batch type must be defined.', 'locomotive' ) );
		} else {
			$this->type = $args['type'];
		}

		if ( empty( $args['args'] ) || ! is_array( $args['args'] ) ) {
			$this->args = array();
		} else {
			$this->args = $args['args'];
		}

		if ( empty( $args['callback'] ) ) {
			throw new Exception( __( 'A callback must be defined.', 'locomotive' ) );
		} else {
			$this->callback = $args['callback'];
		}

		$this->currently_registered = locomotive_get_all_batches();

		add_action( 'loco_batch_' . $this->slug, array( $this, 'run_ajax' ) );
		add_action( 'loco_batch_' . $this->slug . '_reset', array( $this, 'clear_result_status' ) );

		return true;
	}

	/**
	 * Return JSON for AJAX requests to run.
	 *
	 * @param int $current_step Current step.
	 */
	public function run_ajax( $current_step ) {
		wp_send_json( $this->run( $current_step ) );
	}

	/**
	 * Run this batch process (query for the data and process the results).
	 *
	 * @param int $current_step Current step.
	 */
	public function run( $current_step ) {
		$this->current_step = $current_step;

		$results = $this->get_results();

		if ( empty( $results ) ) {
			$this->update_status( 'no results found' );
			return $this->format_ajax_details( array(
				'success' => true,
				'message' => __( 'No results found.', 'locomotive' ),
			) );
		}

		$this->process_results( $results );

		$per_page = get_option( 'posts_per_page' );
		if ( isset( $this->per_batch_param ) ) {
			$per_page = $this->args[ $this->per_batch_param ];
		}

		/**
		 * Filter the per_page number used to calculate total number of steps. You would get use
		 * out of this if you had a custom $wpdb query that didn't paginate in one of the default
		 * ways supported by the plugin.
		 *
		 * @param int $per_page The number of results per page.
		 */
		$per_page = apply_filters( 'loco_batch_' . $this->slug . '_per_page', $per_page );

		$total_steps = ceil( $this->total_num_results / $per_page );

		if ( (int) $this->current_step === (int) $total_steps ) {

			// Need to really check to make sure there were no results added while processing.
			// In the case of destructive actions (i.e. deletion) there will be a gap equal to the per_page param.
			// In all other cases, the difference in totals should equal total number of results.
			// If neither of these are true, we need to run the last step over again.
			$difference = $this->total_num_results - $this->difference_in_result_totals;
			if ( $difference <= $per_page || $difference === $this->total_num_results ) {
				$this->update_status( 'finished' );
			} else {
				$this->current_step = $this->current_step - 1;
				$this->update_status( 'running' );
			}
		} else {
			$this->update_status( 'running' );
		}

		$progress = ( 0 === (int) $total_steps ) ? 100 : round( ( $this->current_step / $total_steps ) * 100 );

		// If there are errors, return the error variable as true so front-end can handle.
		if ( is_array( $this->result_errors ) && count( $this->result_errors ) > 0  ) {
			return $this->format_ajax_details( array(
				'error'         => true,
				'errors'        => $this->result_errors,
				'total_steps'   => $total_steps,
				'query_results' => $results,
				'progress'      => $progress,
			) );
		}

		return $this->format_ajax_details( array(
			'total_steps'   => $total_steps,
			'query_results' => $results,
			'progress'      => $progress,
		) );
	}

	/**
	 * Get details for Ajax requests.
	 *
	 * @param  array $details Array of details to send via Ajax.
	 */
	private function format_ajax_details( $details = array() ) {
		return wp_parse_args( $details, array(
			'success'           => true,
			'current_step'      => $this->current_step,
			'callback'          => $this->callback,
			'status'            => $this->status,
			'batch'             => $this->name,
		) );
	}

	/**
	 * Update batch timestamps.
	 *
	 * @param  string $status Status of batch process.
	 */
	private function update_status( $status ) {
		update_option( 'loco_batch_' . $this->slug, array(
			'status' => $status,
			'timestamp' => current_time( 'timestamp' ),
		) );

		$this->status = __( ucfirst( $status ) );
	}

	/**
	 * Loop over an array of results (posts, pages, etc) and run the callback
	 * function that was passed through when this batch was registered.
	 *
	 * @param array $results Array of results from the query.
	 */
	public function process_results( $results ) {
		/**
		 * The key used to define the status of whether or not a result was processed successfully.
		 *
		 * @param string $string_text 'success'
		 */
		$success_status = apply_filters( 'loco_batch_success_status', 'success' );

		/**
		 * The key used to define the status of whether or not a result was not able to be processed.
		 *
		 * @param string $string_text 'failed'
		 */
		$failed_status = apply_filters( 'loco_batch_failed_status', 'failed' );

		foreach ( $results as $result ) {
			// If this result item has been processed already, skip it.
			if ( $success_status === $this->get_result_status( $result ) ) {
				continue;
			}

			try {
				call_user_func_array( $this->callback, array( $result ) );
				$this->update_result_status( $result, $success_status );
			} catch ( Exception $e ) {
				$this->update_status( $failed_status );
				$this->update_result_status( $result, $failed_status );
				$this->result_errors[] = array(
					'item' => $result->ID,
					'message' => $e->getMessage(),
				);

			}
		}
	}

	/**
	 * Update the meta info on a result.
	 *
	 * @param mixed  $result The result we want to track meta data on.
	 * @param string $status  Status of this result in the batch.
	 */
	public function update_result_status( $result, $status ) {
		/**
		 * Action to hook into when a result gets processed and it's status is updated.
		 *
		 * @param mixed  $result The current result.
		 * @param string $status The status to set on a result.
		 */
		do_action( 'loco_batch_' . $this->slug . '_update_result_status', $result, $status );

		return $this->update_result_item_status( $result, $status );
	}

	/**
	 * Get the status of a result.
	 *
	 * @param mixed $result The result we want to get status of.
	 */
	public function get_result_status( $result ) {
		/**
		 * Action to hook into when a result is being checked for whether or not
		 * it was updated.
		 *
		 * @param mixed $result The current result which is getting it's status checked.
		 */
		do_action( 'loco_batch_' . $this->slug . '_get_result_status', $result );

		return $this->get_result_item_status( $result );
	}

	/**
	 * Clear the result status for a batch.
	 */
	public function clear_result_status() {
		/**
		 * Action to hook into when the 'reset' button is clicked in the admin UI.
		 *
		 * @param Batch $this The current batch object.
		 */
		do_action( 'loco_batch_' . $this->slug . '_clear', $this );

		$this->batch_clear_result_status();
		$this->update_status( 'reset' );
	}
}
