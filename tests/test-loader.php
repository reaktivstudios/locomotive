<?php

class LoaderTest extends WP_UnitTestCase {
	public function setUp() { }

	/**
	 * Make sure our plugin constants are defined.
	 */
	public function test_constants() {
		$this->assertNotNull( LOCO_VERSION );
		$this->assertNotNull( LOCO_PLUGIN_DIR );
		$this->assertNotNull( LOCO_PLUGIN_URL );
		$this->assertNotNull( LOCO_PLUGIN_FILE );
	}
}

