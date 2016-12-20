<?php
/**
 * Functions to simplify interacting with the Locomotive utility.
 *
 * @package Locomotive
 */

use Rkv\Locomotive\Abstracts\Batch;
use Rkv\Locomotive\Batches\Posts;
use Rkv\Locomotive\Batches\Users;
use Rkv\Locomotive\Batches\Terms;
use Rkv\Locomotive\Batches\Comments;

/**
 * Register a new batch process.
 *
 * @param  array $args Arguments for the batch process.
 * @throws Exception Only post & user are accepted $args['type'].
 */
function register_batch_process( $args ) {
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

		case 'term':
			$batch_process = new Terms();
			$batch_process->register( $args );
			break;

		case 'comment':
			$batch_process = new Comments();
			$batch_process->register( $args );
			break;
	}
}

/**
 * Get the batch hooks that have been added and some info about them.
 *
 * @return array
 */
function locomotive_get_all_batches() {
	$batches = get_option( 'loco_batches', array() );

	foreach ( $batches as $k => $batch ) {
		if ( $batch_status = get_option( 'loco_batch_' . $k ) ) {
			$last_run = locomotive_time_ago( $batch_status['timestamp'] );
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
function locomotive_update_registered_batches( $batches ) {
	return update_option( 'loco_batches', $batches );
}

/**
 * Template function for showing time ago.
 *
 * @todo Move this to a template functions file.
 *
 * @param  integer $time Timestamp.
 */
function locomotive_time_ago( $time ) {
	return sprintf( _x( '%s ago', 'amount of time that has passed', 'locomotive' ), human_time_diff( $time, current_time( 'timestamp' ) ) );
}

/**
 * Clear all existing batches.
 */
function locomotive_clear_existing_batches() {
	return update_option( 'loco_batches', array() );
}
