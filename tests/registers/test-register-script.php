<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 * @noinspection PhpMultipleClassesDeclarationsInOneFile
 */

if ( ! class_exists( 'CPBN_Regiser_Script' ) ) {
	class CPBN_Register_Script extends NBPC_Register_Script {
		public function get_items(): Generator {
			yield new NBPC_Reg_Script(
				'nbpc-foo',
				$this->src_helper( 'script.min.js' ),
				[],
			);
		}
	}
}


/**
 * Class Test_Register_Script
 *
 * @package nbpc
 */
class Test_Register_Script extends WP_UnitTestCase {
	public static function setUpBeforeClass() {
		// Force SCRIPT_DEBUG = true
		add_filter( 'nbpc_script_debug', '__return_true' );
	}

	public static function tearDownAfterClass() {
		remove_filter( 'nbpc_script_debug', '__return_true' );
	}

	public function test_get_items() {
		$register = new CPBN_Register_Script();

		do_action( 'init' );

		$url = plugin_dir_url( nbpc()->get_main_file() );

		// Check if script is correctly registered.
		$this->assertTrue( wp_script_is( 'nbpc-foo', 'registered' ) );

		$item = iterator_to_array( $register->get_items() )[0];

		// Check if the item handle is correct.
		$this->assertEquals( 'nbpc-foo', $item->handle );

		// Check if .min.js is correctly replaced when SCRIPT_DEBUG=true.
		$this->assertEquals( "{$url}assets/js/script.js", $item->src );
	}
}
