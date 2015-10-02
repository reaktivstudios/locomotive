<?php
/**
 * Functions to simplify interacting with the Batch Processing utility.
 *
 * @package Batch_Processing
 */

namespace Batch_Processing;

/**
 * Register a new batch process.
 *
 * @param  array $args Arguments for the batch process.
 * @throws Exception Only post & user are accepted $args['type'].
 */
function register( $args ) {
	switch ( $args['type'] ) {
		case 'post':
			$batch_process = new Posts();
			$batch_process->register( $args );
			break;

		default:
			throw new \Exception( 'Type not supported.' );
			break;
	}
}
