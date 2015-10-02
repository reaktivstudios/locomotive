<?php
/**
 * Abstract batch class.
 *
 * @package Batch_Process/Batch
 */

namespace Batch_Processing;

/**
 * Abstract batch class.
 */
abstract class Batch {
	/**
	 * Register the batch process so we can run it.
	 *
	 * @param  array $args Details about the batch you are registering.
	 */
	public function register( $args ) {
		$this->verify_register_args( $args );
	}

	/**
	 * Verify that our args has everything we need it to.
	 *
	 * @param  array $args Array of args for register.
	 * @throws \Exception Type must be provided.
	 * @return true|exception
	 */
	private function verify_register_args( $args ) {
		if ( empty( $args['name'] ) ) {
			throw new \Exception( 'Batch name must be provided.' );
		}

		if ( empty( $args['type'] ) ) {
			throw new \Exception( 'Type of batch must be defined.' );
		}

		if ( empty( $args['args'] ) || ! is_array( $args['args'] ) ) {
			throw new \Exception( 'An array of args must be defined.' );
		}

		if ( empty( $args['callback'] ) ) {
			throw new \Exception( 'A callback must be defined.' );
		}

		return true;
	}
}
