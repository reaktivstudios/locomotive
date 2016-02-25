<?php
/**
 * Abstract batch class.
 *
 * @package Batch_Process/Batch
 */

namespace Batch_Process;

/**
 * Abstract batch class.
 */
abstract class Batch {
	/**
	 * Prefix for batch hook actions.
	 *
	 * @var string
	 */
	const BATCH_HOOK_PREFIX = '_rkv_batch_';

	/**
	 * Meta key for the option that holds all of the batch hooks that a dev
	 * registers.
	 *
	 * @var string
	 */
	const REGISTERED_BATCHES_KEY = '_rkv_batches';

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
	 * Args for the batch query.
	 *
	 * @var array
	 */
	public $args = array();

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
	 * Total number of processed results.
	 *
	 * @var array
	 */
	public $total_num_processed_results = 0;

	/**
	 * Main plugin method for querying data.
	 *
	 * @since 0.1
	 *
	 * @return mixed                 An array of data to be processed in bulk fashion.
	 */
	abstract function get_results();

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
		} else {
			return false;
		}
	}

	/**
	 * Add a batch process to our system.
	 */
	private function add() {
		if ( ! is_array( $this->currently_registered ) ) {
			$this->currently_registered = array();
		}

		if ( ! isset( $this->currently_registered[ $this->slug ] ) ) {
			$this->currently_registered[ $this->slug ] = array(
				'name' => $this->name,
			);
		} else {
			$this->currently_registered[ $this->slug ]['name'] = $this->name;
		}

		return update_registered_batches( $this->currently_registered );
	}

	/**
	 * Setup our Batch object to have everything it needs (callback, name, slug,
	 * etc).
	 *
	 * @todo Research the best way to handle exceptions.
	 *
	 * @param  array $args Array of args for register.
	 * @throws \Exception Type must be provided.
	 * @return true|exception
	 */
	private function setup( $args ) {
		if ( empty( $args['name'] ) ) {
			throw new \Exception( 'Batch name must be provided.' );
		} else {
			$this->name = $args['name'];
		}

		if ( empty( $args['slug'] ) ) {
			$this->slug = sanitize_title_with_dashes( $args['name'] );
		} else {
			$this->slug = $args['slug'];
		}

		if ( empty( $args['type'] ) ) {
			throw new \Exception( 'Type of batch must be defined.' );
		} else {
			$this->type = $args['type'];
		}

		if ( empty( $args['args'] ) || ! is_array( $args['args'] ) ) {
			throw new \Exception( 'An array of args must be defined.' );
		} else {
			$this->args = $args['args'];
		}

		if ( empty( $args['callback'] ) ) {
			throw new \Exception( 'A callback must be defined.' );
		} else {
			$this->callback = $args['callback'];
		}

		$this->currently_registered = get_all_batches();

		add_action( self::BATCH_HOOK_PREFIX . $this->slug, array( $this, 'run' ) );
		add_action( self::BATCH_HOOK_PREFIX . $this->slug . '_reset', array( $this, 'clear_result_status' ) );

		return true;
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
			wp_send_json( $this->format_ajax_details( array(
				'success' => true,
				'error' => __( 'No results found.' ),
			) ) );
		}

		$this->process_results( $results );

		$total_steps = ceil( $this->total_num_results / $this->args['posts_per_page'] );
		$progress = ( 0 === (int) $total_steps ) ? 100 : round( ( $this->current_step / $total_steps ) * 100 );

		if ( (int) $this->current_step === (int) $total_steps ) {
			// If we are on the last step.
			$this->update_status( 'finished' );
		} else if ( $this->total_num_processed_results >= $this->total_num_results ) {
			// If we already processed everything.
			$this->update_status( 'already processed' );

			// Set progress to 100 since it is already processed.
			$progress = 100;
		} else {
			$this->update_status( 'running' );
		}

		wp_send_json( $this->format_ajax_details( array(
			'total_steps'   => $total_steps,
			'query_results' => $results,
			'progress'      => $progress,
		) ) );
	}

	/**
	 * Get details for Ajax requests.
	 *
	 * @param  array $details Array of details to send via Ajax.
	 */
	private function format_ajax_details( $details = array() ) {
		return wp_parse_args( $details, array(
			'success'                     => true,
			'current_step'                => $this->current_step,
			'callback'                    => $this->callback,
			'status'                      => $this->status,
			'batch'                       => $this->name,
			'total_num_results'           => $this->total_num_results,
			'total_num_processed_results' => $this->total_num_processed_results,
		) );
	}

	/**
	 * Update the processed number of results.
	 */
	private function update_processed_result_count() {
		$this->total_num_processed_results = $this->get_processed_result_count();
	}

	/**
	 * Get the count of processed results.
	 */
	private function get_processed_result_count() {
		global $wpdb;

		// @todo How inefficent is this?
		$num_processed_results = $wpdb->get_var( $wpdb->prepare(
			"SELECT count(*) FROM $wpdb->postmeta WHERE meta_key = %s",
			$this->slug . '_status'
		) );

		return $num_processed_results;
	}

	/**
	 * Update batch timestamps.
	 *
	 * @param  string $status Status of batch process.
	 */
	private function update_status( $status ) {
		update_site_option( self::BATCH_HOOK_PREFIX . $this->slug, array(
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
		$success_status = 'success';
		$failed_status = 'failed';

		foreach ( $results as $result ) {
			// If this result item has been processed already, skip it.
			if ( $success_status === $this->get_result_status( $result ) ) {
				continue;
			}

			try {
				call_user_func_array( $this->callback, array( $result ) );
				$this->update_result_status( $result, $success_status );
			} catch ( \Exception $e ) {
				$this->update_result_status( $result, $failed_status );
				wp_send_json( $this->format_ajax_details( array(
					'success' => false,
					'status'  => __( 'Failed' ),
					'error'   => $e->getMessage(),
				) ) );
			}
		}

		$this->update_processed_result_count();
	}

	/**
	 * Update the meta info on a result.
	 *
	 * @param mixed  $result The result we want to track meta data on.
	 * @param string $status  Status of this result in the batch.
	 */
	public function update_result_status( $result, $status ) {
		if ( $result instanceof \WP_Post ) {
			update_post_meta( $result->ID, $this->slug . '_status', $status );
		}
	}

	/**
	 * Update the meta info on a result.
	 *
	 * @param mixed $result The result we want to get status of.
	 */
	public function get_result_status( $result ) {
		if ( $result instanceof \WP_Post ) {
			return get_post_meta( $result->ID, $this->slug . '_status', true );
		}

		return false;
	}

	/**
	 * Clear the result status for a batch.
	 */
	public function clear_result_status() {
		if ( 'post' === $this->type ) {
			delete_post_meta_by_key( $this->slug . '_status' );
		}

		$this->update_status( 'reset' );
	}
}
