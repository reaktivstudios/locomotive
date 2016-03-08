# Batch Processing

Creating batch processes in WordPress has never been so easy

## Example Implementation

``` php
/**
 * Register our batch process.
 */
function my_batch_processes() {
	// Example post query.
	try {
		Batch_Process\register( array(
			'name'     => 'Just another batch',
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 1,
				'post_type'      => 'post',
			),
		) );
	} catch ( Exception $e ) {
		error_log( print_r( $e->getMessage(), true ) );
	}
	
	// Example non existant post type query.
	try {
		Batch_Process\register( array(
			'name'     => 'Not existing post type',
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 1,
				'post_type'      => 'Not existing post type',
			),
		) );
	} catch ( Exception $e ) {
		error_log( print_r( $e->getMessage(), true ) );
	}
	
	// Example page batch.
	try {
		Batch_Process\register( array(
			'name'     => 'Pages Batch',
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 2,
				'post_type'      => 'page',
			),
		) );
	} catch ( Exception $e ) {
		error_log( print_r( $e->getMessage(), true ) );
	}
}
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

## Running Unit Tests

In order to run the unit tests you'll want to `vagrant ssh` into your Virtual machine (assuming you're using one) and `cd` to the directory where the plugin is. From there:
 
 * Run `bin/install-wp-tests.sh` to install the test database.
 * Run `phpunit` to run the tests.

You should see the tests run and complete with 0 errors!
