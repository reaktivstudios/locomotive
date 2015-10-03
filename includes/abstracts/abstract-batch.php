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
	 * Args for the batch process.
	 *
	 * @var array
	 */
	public $args;

	/**
	 * Register the batch process so we can run it.
	 *
	 * @param  array $args Details about the batch you are registering.
	 */
	public function register( $args ) {
		if ( $this->verify_register_args( $args ) ) {
			$this->add();
		} else {
			return false;
		}
	}

	/**
	 * Add a batch hook to our system.
	 */
	public function add() {
		$current_batches = self::get_all_batches();

		if ( ! is_array( $current_batches ) ) {
			$current_batches = array();
		}

		$current_batches[ $this->slug ] = array(
			'name'   => $this->name,
			'active' => true,
		);

		return update_site_option( self::REGISTERED_BATCHES_KEY, $current_batches );
	}

	/**
	 * Get the batch hooks that have been added
	 *
	 * @return array
	 */
	public static function get_all_batches() {
		return get_site_option( self::REGISTERED_BATCHES_KEY, array() );
	}

	/**
	 * Verify that our args has everything we need it to.
	 *
	 * @todo  Research the best way to handle exceptions.
	 *
	 * @param  array $args Array of args for register.
	 * @throws \Exception Type must be provided.
	 * @return true|exception
	 */
	private function verify_register_args( $args ) {
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

		return true;
	}
}
