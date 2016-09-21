<?php

class LoaderTest extends WP_UnitTestCase {
	function setUp() { }

	/**
	 * Make sure our plugin constants are defined.
	 */
	function test_constants() {
		$this->assertNotNull( LOCO_VERSION );
		$this->assertNotNull( LOCO_PLUGIN_DIR );
		$this->assertNotNull( LOCO_PLUGIN_URL );
		$this->assertNotNull( LOCO_PLUGIN_FILE );
	}
}

