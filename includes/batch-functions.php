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
 */
function register( $args ) {
	$args = wp_parse_args( $args, array(
		'name'     => '',
		'object'   => 'post',
		'args'     => array(),
		'callback' => '',
	) );
}
