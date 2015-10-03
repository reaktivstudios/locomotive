# Batch Processing

Creating batch processes in WordPress has never been so easy

## Example Implementation

``` php
add_action( 'add_batch_processes', 'my_batch_processes' );

/**
 * Register our batch process.
 */
function my_batch_processes() {
	try {
		Batch_Process\register( array(
			'name'     => 'My Test Batch process',
			'slug'     => 'test-another-batch',
			'type'     => 'post',
			'callback' => 'my_callback_function',
			'args'     => array(
				'posts_per_page' => 5,
				'post_type'      => 'post',
			),
		) );
	} catch ( Exception $e ) {
		var_dump( $e->getMessage() );
		die();
	}
}

/**
 * This is what we want to do with each individual result during a batch routine/
 *
 * @param  array $result Individual result from batch query.
 */
function my_callback_function( $result ) {
	var_dump( $result->post_title );
}
```
