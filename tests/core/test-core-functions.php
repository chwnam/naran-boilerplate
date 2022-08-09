<?php
/**
 * Naran Boilerplate Core Tests
 *
 * test/test-core-functions.php
 */
class Test_Core_Functions extends WP_UnitTestCase {
	public function test_nbpc_script_debug() {
		$script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG;

		// Check if return value is the same as SCRIPT_DEBUG constant.
		$this->assertSame( $script_debug, nbpc_script_debug() );

		// Check if the filter works.
		$function = function () use ( $script_debug ) { return ! $script_debug; };
		add_filter( 'nbpc_script_debug', $function );
		$this->assertSame( ! $script_debug, nbpc_script_debug() );
		remove_filter( 'nbpc_script_debug', $function );
	}

	public function test_nbpc_format_callable() {
		// anonymous function callback
		$output = nbpc_format_callable( function () { } );
		$this->assertEquals( '{Closure}', $output );

		$anon = new class {
			public function cb(): void {
			}

			public static function stcb(): void {
			}
		};

		// anonymous method callback
		$output = nbpc_format_callable( [ $anon, 'cb' ] );
		$this->assertEquals( '{AnonymousClass}::cb', $output );

		// anonymous static method callback
		$output = nbpc_format_callable( [ $anon, 'stcb' ] );
		$this->assertEquals( '{AnonymousClass}::stcb', $output );

		// regular method callback
		$output = nbpc_format_callable( [ $this, 'fake_callback' ] );
		$this->assertEquals( __CLASS__ . '::fake_callback', $output );

		// regular static method callback
		$output = nbpc_format_callable( [ __CLASS__, 'fake_static_callback' ] );
		$this->assertEquals( __CLASS__ . '::fake_static_callback', $output );

		$output = nbpc_format_callable( [ $this, 'fake_static_callback' ] );
		$this->assertEquals( __CLASS__ . '::fake_static_callback', $output );

		// regular function callback
		$output = nbpc_format_callable( '__return_true' );
		$this->assertEquals( '__return_true', $output );

		// Closure with regular function.
		$output = nbpc_format_callable( Closure::fromCallable( '__return_false' ) );
		$this->assertEquals( '{Closure}', $output );

		// Test against imaginary methods.
		$output = nbpc_format_callable( 'nbpc_nonexist_function_np2nyei' );
		$this->assertEquals( 'nbpc_nonexist_function_np2nyei', $output );

		$output = nbpc_format_callable( [ $this, 'nonexist' ] );
		$this->assertEquals( __CLASS__ . '::nonexist', $output );

		$output = nbpc_format_callable( [ __CLASS__, 'nonexist' ] );
		$this->assertEquals( __CLASS__ . '::nonexist', $output );

		// Test with stdClass
		$std         = new stdClass();
		$std->method = function (): void { };
		$output      = nbpc_format_callable( [ $std, 'method' ] );
		$this->assertEquals( 'stdClass::method', $output );
	}

	public function fake_callback() {
	}

	public static function fake_static_callback() {
	}
}
