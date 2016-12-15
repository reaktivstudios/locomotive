Locomotive
==========

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/badges/quality-score.png?b=master&s=86399ae1ed8459dbcaa0c4a5d5e34947d7454cf8)](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/badges/coverage.png?b=master&s=656ebaea7636b3882b1834f7226c53327e826bb2)](https://scrutinizer-ci.com/g/reaktivstudios/locomotive/?branch=master)

## About
Creating batch processes in WordPress has never been so easy. Locomotive allows developers to write a single function (or set of functions) to process actions across a large data set. These registered batch processes can be run from the WP admin as needed.

[Documentation](https://github.com/reaktivstudios/locomotive/wiki)

## Contributors
* [Josh Eaton](https://github.com/jjeaton)
* [Zach Wills](https://github.com/zachwills)
* [Andrew Norcross](https://github.com/norcross)

## Example Implementations

#### Register a standard post query

``` php
function my_post_query_batch_process() {

	register_batch_process( array(
		'name'     => 'Just another batch',
		'type'     => 'post',
		'callback' => 'my_callback_function',
		'args'     => array(
			'posts_per_page' => 1,
			'post_type'      => 'post',
		),
	) );
}
add_action( 'locomotive_init', 'my_post_query_batch_process' );
```

#### Register a query for a non-existent post type
``` php
function my_non_post_query_batch_process() {

	register_batch_process( array(
		'name'     => 'Not existing post type',
		'type'     => 'post',
		'callback' => 'my_callback_function',
		'args'     => array(
			'posts_per_page' => 1,
			'post_type'      => 'Not existing post type',
		),
	) );
}
add_action( 'locomotive_init', 'my_non_post_query_batch_process' );
```

#### Register a query for pages
``` php
function my_page_query_batch_process() {

	register_batch_process( array(
		'name'     => 'Pages Batch',
		'type'     => 'post',
		'callback' => 'my_callback_function',
		'args'     => array(
			'posts_per_page' => 2,
			'post_type'      => 'page',
		),
	) );
}
add_action( 'locomotive_init', 'my_page_query_batch_process' );
```

#### Register a query for users
``` php
function my_user_query_batch_process() {

	register_batch_process( array(
		'name'     => 'User Batch',
		'type'     => 'user',
		'callback' => 'my_callback_function',
		'args'     => array(
			'number' => 10,
		),
	) );
}
add_action( 'locomotive_init', 'my_user_query_batch_process' );
```

#### Example callback
``` php
/**
 * This is what we want to do with each individual result during a batch routine/
 *
 * @param  array $result Individual result from batch query.
 */
function my_callback_function( $result ) {
	error_log( print_r( $result->post_title, true ) );
}
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).


#### [Pull requests](https://github.com/reaktivstudios/locomotive/pulls) are very much welcome and encouraged.
