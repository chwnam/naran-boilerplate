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
	private $register;

	public static function setupBeforeClass(): void {
		// Force SCRIPT_DEBUG = true
		add_filter( 'nbpc_script_debug', '__return_true' );
	}

	public static function tearDownAfterClass(): void {
		remove_filter( 'nbpc_script_debug', '__return_true' );
	}

	public function setUp(): void {
		$this->register = new class() extends NBPC_Register_Base_Script {
			public function get_items(): Generator {
				yield new NBPC_Reg_Script(
					'nbpc-foo',
					$this->src_helper( 'script.min.js' ),
					[],
				);

				yield new NBPC_Reg_Script(
					'nbpc-bar',
					$this->src_helper( 'dist/bar.js' ),
					NBPC_Reg_Script::WP_SCRIPT
				);
			}
		};
	}

	public function tearDown(): void {
		wp_deregister_script( 'nbpc-foo' );
		wp_deregister_script( 'nbpc-bar' );
	}

	public function test_get_items() {
		do_action( 'init' );

		$url = plugin_dir_url( nbpc_main_file() );

		// Check if script is correctly registered.
		$this->assertTrue( wp_script_is( 'nbpc-foo', 'registered' ) );

		$item = iterator_to_array( $this->register->get_items() )[0];

		// Check if the item handle is correct.
		$this->assertEquals( 'nbpc-foo', $item->handle );

		// Check if .min.js is correctly replaced when SCRIPT_DEBUG=true.
		$this->assertEquals( "{$url}assets/js/script.js", $item->src );
	}

	public function test_wp_script() {
		$path = plugin_dir_path( nbpc_main_file() ) . 'assets/js/dist/';

		file_put_contents( $path . 'bar.js', '' );
		file_put_contents( $path . 'bar.asset.php', '<?php return [];' );

		do_action( 'init' );

		// Check if absolute URL of WP_SCRIPT is handled and is correct.
		$wp_scripts   = wp_scripts();
		$src_expected = plugins_url( 'assets/js/dist/bar.js', nbpc_main_file() );
		$src_actual   = $wp_scripts->registered['nbpc-bar']->src;

		unlink( $path . 'bar.js' );
		unlink( $path . 'bar.asset.php' );

		$this->assertEquals( $src_expected, $src_actual );
	}
}
