<?php

class LoaderTest extends WP_UnitTestCase {
	function setUp() { }

	/**
	 * Make sure our plugin constants are defined.
	 */
	function test_constants() {
		$this->assertNotNull( BATCH_VERSION );
		$this->assertNotNull( BATCH_PLUGIN_DIR );
		$this->assertNotNull( BATCH_PLUGIN_URL );
		$this->assertNotNull( BATCH_PLUGIN_FILE );
	}
}

