<?php
/**
 * Functions to simplify interacting with the Batch Processing utility.
 *
 * @package Batch_Processing
 */

namespace Batch_Process;

/**
 * Register a new batch process.
 *
 * @param  array $args Arguments for the batch process.
 * @throws \Exception Only post & user are accepted $args['type'].
 */
function register( $args ) {
	switch ( $args['type'] ) {
		case 'post':
			$batch_process = new Posts();
			$batch_process->register( $args );
			$batch_process->run( 10, 0 );
			break;

		default:
			throw new \Exception( 'Type not supported.' );
			break;
	}
}

/**
 * Get the batch hooks that have been added
 *
 * @return array
 */
function get_all_batches() {
	return get_site_option( Batch::REGISTERED_BATCHES_KEY, array() );
}

/**
 * Clear all existing batches.]
 */
function clear_existing_batches() {
	return update_site_option( Batch::REGISTERED_BATCHES_KEY, array() );
}
