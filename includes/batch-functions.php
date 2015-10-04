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
	$batches = get_site_option( \Batch_Processing::REGISTERED_BATCHES_KEY, array() );
	$timestamps = get_all_timestamps();

	foreach ( $batches as $k => $batch ) {
		if ( ! empty( $timestamps[ $k ] ) ) {
			$batches[ $k ]['last_run'] = $timestamps[ $k ];
		} else {
			$batches[ $k ]['last_run'] = '';
		}
	}

	return $batches;
}

/**
 * Update the registered batches.
 *
 * @param array $batches Batches you want to register.
 */
function update_registered_batches( $batches ) {
	return update_site_option( \Batch_Processing::REGISTERED_BATCHES_KEY, $batches );
}

/**
 * Get the batch hooks that have been added.
 *
 * @return array
 */
function get_all_timestamps() {
	return get_site_option( Batch::BATCH_TIMESTAMPS_KEY, array() );
}

/**
 * Template function for showing time ago.
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
	return update_site_option( \Batch_Processing::REGISTERED_BATCHES_KEY, array() );
}
