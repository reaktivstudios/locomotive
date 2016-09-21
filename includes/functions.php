<?php
/**
 * Functions to simplify interacting with the Locomotive utility.
 *
 * @package Locomotive
 */

namespace RKV\Locomotive;

/**
 * Register a new batch process.
 *
 * @param  array $args Arguments for the batch process.
 * @throws \Exception Only post & user are accepted $args['type'].
 */
function register( $args ) {
	if ( empty( $args['type'] ) ) {
		$args['type'] = '';
	}

	switch ( $args['type'] ) {
		case 'post':
			$batch_process = new Posts();
			$batch_process->register( $args );
			break;

		case 'user':
			$batch_process = new Users();
			$batch_process->register( $args );
			break;

		default:
			throw new \Exception( 'Type not supported.' );
			break;
	}
}

/**
 * Get the batch hooks that have been added and some info about them.
 *
 * @return array
 */
function get_all_batches() {
	$batches = get_option( Batch::REGISTERED_BATCHES_KEY, array() );

	foreach ( $batches as $k => $batch ) {
		if ( $batch_status = get_option( Batch::LOCO_HOOK_PREFIX . $k ) ) {
			$last_run = time_ago( $batch_status['timestamp'] );
			$status = $batch_status['status'];
		} else {
			$last_run = 'never';
			$status = 'new';
		}

		$batches[ $k ]['last_run'] = $last_run;
		$batches[ $k ]['status'] = $status;
	}

	return $batches;
}

/**
 * Update the registered batches.
 *
 * @param array $batches Batches you want to register.
 */
function update_registered_batches( $batches ) {
	return update_option( Batch::REGISTERED_BATCHES_KEY, $batches );
}

/**
 * Template function for showing time ago.
 *
 * @todo Move this to a template functions file.
 *
 * @param  timestamp $time Timestamp.
 */
function time_ago( $time ) {
	return human_time_diff( $time, current_time( 'timestamp' ) ) . ' ago';
}

/**
 * Clear all existing batches.
 */
function clear_existing_batches() {
	return update_option( Batch::REGISTERED_BATCHES_KEY, array() );
}
