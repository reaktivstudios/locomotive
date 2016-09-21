# Locomotive

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/badges/quality-score.png?b=master&s=86399ae1ed8459dbcaa0c4a5d5e34947d7454cf8)](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/badges/coverage.png?b=master&s=656ebaea7636b3882b1834f7226c53327e826bb2)](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/?branch=master)

Creating batch processes in WordPress has never been so easy

## Example Implementation

``` php
/**
 * Register our batch process.
 */
function my_batch_processes() {
// Example post query.

register_batch_process( array(
	'name'     => 'Just another batch',
	'type'     => 'post',
	'callback' => 'my_callback_function',
	'args'     => array(
		'posts_per_page' => 1,
		'post_type'      => 'post',
	),
) );

// Example non existent post type query.

register_batch_process( array(
	'name'     => 'Not existing post type',
	'type'     => 'post',
	'callback' => 'my_callback_function',
	'args'     => array(
		'posts_per_page' => 1,
		'post_type'      => 'Not existing post type',
	),
) );

// Example page batch.

register_batch_process( array(
	'name'     => 'Pages Batch',
	'type'     => 'post',
	'callback' => 'my_callback_function',
	'args'     => array(
		'posts_per_page' => 2,
		'post_type'      => 'page',
	),
) );

add_action( 'add_batch_processes', 'my_batch_processes' );

/**
 * This is what we want to do with each individual result during a batch routine/
 *
 * @param  array $result Individual result from batch query.
 */
function my_callback_function( $result ) {
	error_log( print_r( $result->post_title, true ) );
}
```
