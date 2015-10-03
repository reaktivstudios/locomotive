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
	 * Meta key for the option that holds all of the batch hooks that a dev
	 * registers.
	 *
	 * @var string
	 */
	const REGISTERED_BATCHES_KEY = '_rkv_batches';

	/**
	 * Prefix for batch hook actions.
	 *
	 * @var string
	 */
	const BATCH_HOOK_PREFIX = '_rkv_batch_';

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
	public $args;

	/**
	 * Tyoe if batch.
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
	public $currently_registered;

	/**
	 * Main plugin method for querying data.
	 *
	 * @since 0.1
	 *
	 * @param int $offset            The offset to use for querying data.
	 * @return mixed                 An array of data to be processed in bulk fashion.
	 */
	abstract function get_results( $offset );

	/**
	 * Register the batch process so we can run it.
	 *
	 * @param  array $args Details about the batch you are registering.
	 */
	public function register( $args ) {
		if ( $this->setup( $args ) ) {
			$this->add();
		} else {
			return false;
		}
	}

	/**
	 * Add a batch process to our system.
	 */
	public function add() {
		if ( ! is_array( $this->currently_registered ) ) {
			$this->currently_registered = array();
		}

		$this->currently_registered[ $this->slug ] = array(
			'name' => $this->name,
		);

		return update_site_option( self::REGISTERED_BATCHES_KEY, $this->currently_registered );
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

		add_action( self::BATCH_HOOK_PREFIX . $this->slug, array( $this, 'process_results' ) );

		return true;
	}

	/**
	 * Run this batch process (query for the data and process the results).
	 *
	 * @param int $offset The offset to use for querying data.
	 */
	public function run( $offset ) {
		$results = $this->get_results( $offset );
		$this->process_results( $results );
	}

	/**
	 * Loop over an array of results (posts, pages, etc) and run the callback
	 * function that was passed through when this batch was registered.
	 *
	 * @param  array $results Array of results from the query.
	 */
	public function process_results( $results ) {
		foreach ( $results as $result ) {
			call_user_func_array( $this->callback, array( $result ) );
		}
	}
}
