<?php
/**
 * Users batch class.
 *
 * @package Locomotive/Batch
 */

namespace Rkv\Locomotive\Batches;

use WP_User_Query;
use Rkv\Locomotive\Abstracts\Batch;

/**
 * Batch Users class.
 */
class Users extends Batch {
	/**
	 * Default arguments for this batch.
	 *
	 * @var array
	 */
	public $default_args = array(
		'number' => 10,
		'offset' => 0,
	);
}
