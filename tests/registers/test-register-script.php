<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Register_Script
 *
 * @package nbpc
 */
class Test_Register_Script extends WP_UnitTestCase {
	public function testGetItems() {
		nbpc();

		do_action( 'init' );

		$url = plugin_dir_url( nbpc()->get_main_file() );

		// Check if script is correctly registered.
		$this->assertTrue( wp_script_is( 'nbpc-foo', 'registered' ) );

		$item = iterator_to_array( nbpc()->registers->script->get_items() )[0];

		// Check if the item handle is correct.
		$this->assertEquals( 'nbpc-foo', $item->handle );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			// Check if .min.js is correcly replaced.
			$this->assertEquals( "{$url}assets/js/script.js", $item->src );
		}
	}
}
