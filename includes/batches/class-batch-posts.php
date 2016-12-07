<?php
/**
 * Posts batch class.
 *
 * @package Locomotive/Batch
 */

namespace Rkv\Locomotive\Batches;

use WP_Query;
use Rkv\Locomotive\Abstracts\Batch;

/**
 * Batch Posts class.
 */
class Posts extends Batch {
	/**
	 * Default arguments for this batch.
	 *
	 * @var array
	 */
	public $default_args = array(
		'post_type'      => 'post',
		'posts_per_page' => 10,
		'offset'         => 0,
	);
}
