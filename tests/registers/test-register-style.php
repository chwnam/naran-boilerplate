<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Register_Style
 *
 * @package nbpc
 */
class Test_Register_Style extends WP_UnitTestCase {
	private $register;

	public static function setUpBeforeClass() {
		// Force SCRIPT_DEBUG = true
		add_filter( 'nbpc_script_debug', '__return_true' );
	}

	public static function tearDownAfterClass() {
		remove_filter( 'nbpc_script_debug', '__return_true' );
	}

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Style {
			public function get_items(): Generator {
				yield new NBPC_Reg_Style(
					'nbpc-foo',
					$this->src_helper( 'style.min.css' ),
					[],
				);
			}
		};
	}

	public function test_get_items() {
		do_action( 'init' );

		$url = plugin_dir_url( nbpc()->get_main_file() );

		// Check if script is correctly registered.
		$this->assertTrue( wp_style_is( 'nbpc-foo', 'registered' ) );

		$item = iterator_to_array( $this->register->get_items() )[0];

		// Check if the item handle is correct.
		$this->assertEquals( 'nbpc-foo', $item->handle );

		// Check if .min.css is correcly replaced.
		$this->assertEquals( "{$url}assets/css/style.css", $item->src );
	}
}
