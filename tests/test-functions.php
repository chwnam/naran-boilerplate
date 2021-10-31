<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Functions
 *
 * @package nbpc
 */
class Test_Functions extends WP_UnitTestCase {
	public function test_nbpc() {
		$this->assertInstanceOf( NBPC_Main::class, nbpc() );
	}

	public function test_nbpc_parse_module() {
		$this->assertInstanceOf( NBPC_Register_Ajax::class, nbpc_parse_module( 'registers.ajax' ) );
	}

	public function test_nbpc_parse_callback() {
		$anon = new class { public function cb() { } };
		$func = function () { };

		// Check if callables are returned as-is.
		$this->assertSame( '__return_true', nbpc_parse_callback( '__return_true' ) );
		$this->assertSame( [ $this, 'fake_callback' ], nbpc_parse_callback( [ $this, 'fake_callback' ] ) );
		$this->assertSame(
			[ $this, 'fake_static_callback' ],
			nbpc_parse_callback( [ $this, 'fake_static_callback' ] )
		);
		$this->assertSame( [ $anon, 'cb' ], nbpc_parse_callback( [ $anon, 'cb' ] ) );
		$this->assertSame( $func, nbpc_parse_callback( $func ) );

		// Check if notation string works.
		$output = nbpc_parse_callback( 'registers.ajax@register' );
		$this->assertEquals( [ nbpc()->registers->ajax, 'register' ], $output );

		// Check if nonexist callables throws an exception
		$this->expectException( NBPC_Callback_Exception::class );
		nbpc_parse_callback( [ $this, 'nonexist' ] );
	}

	public function test_nbpc_option() {
		$this->assertInstanceOf( NBPC_Register_Option::class, nbpc_option() );
		$this->assertSame( nbpc()->registers->option, nbpc_option() );
	}

	public function test_nbpc_comment_meta() {
		$this->assertInstanceOf( NBPC_Register_Comment_Meta::class, nbpc_comment_meta() );
		$this->assertSame( nbpc()->registers->comment_meta, nbpc_comment_meta() );
	}

	public function test_nbpc_post_meta() {
		$this->assertInstanceOf( NBPC_Register_Post_Meta::class, nbpc_post_meta() );
		$this->assertSame( nbpc()->registers->post_meta, nbpc_post_meta() );
	}

	public function test_nbpc_term_meta() {
		$this->assertInstanceOf( NBPC_Register_Term_Meta::class, nbpc_term_meta() );
		$this->assertSame( nbpc()->registers->term_meta, nbpc_term_meta() );
	}

	public function test_nbpc_user_meta() {
		$this->assertInstanceOf( NBPC_Register_User_Meta::class, nbpc_user_meta() );
		$this->assertSame( nbpc()->registers->user_meta, nbpc_user_meta() );
	}

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

	public function test_nbpc_format_callback() {
		// anonymouse fuction callback
		$output = nbpc_format_callback( function () { } );
		$this->assertEquals( '{Closure}', $output );

		$anon = new class {
			public function cb() { }

			public static function stcb() { }
		};

		// anonymouse method callback
		$output = nbpc_format_callback( [ $anon, 'cb' ] );
		$this->assertEquals( '{AnonymousClass}::cb', $output );

		// anonymouse static method callback
		$output = nbpc_format_callback( [ $anon, 'stcb' ] );
		$this->assertEquals( '{AnonymousClass}::stcb', $output );

		// regular method callback
		$output = nbpc_format_callback( [ $this, 'fake_callback' ] );
		$this->assertEquals( __CLASS__ . '::fake_callback', $output );

		// regular static method callback
		$output = nbpc_format_callback( [ __CLASS__, 'fake_static_callback' ] );
		$this->assertEquals( __CLASS__ . '::fake_static_callback', $output );

		$output = nbpc_format_callback( [ $this, 'fake_static_callback' ] );
		$this->assertEquals( __CLASS__ . '::fake_static_callback', $output );

		// regular function callback
		$output = nbpc_format_callback( '__return_true' );
		$this->assertEquals( '__return_true', $output );

		// Closure with regular function.
		$output = nbpc_format_callback( Closure::fromCallable( '__return_false' ) );
		$this->assertEquals( '{Closure}', $output );

		// Test against an imaginary methods.
		$output = nbpc_format_callback( 'nbpc_nonexist_function_np2nyei' );
		$this->assertEquals( 'nbpc_nonexist_function_np2nyei', $output );

		$output = nbpc_format_callback( [ $this, 'nonexist' ] );
		$this->assertEquals( __CLASS__ . '::nonexist', $output );

		$output = nbpc_format_callback( [ __CLASS__, 'nonexist' ] );
		$this->assertEquals( __CLASS__ . '::nonexist', $output );
	}

	public function fake_callback() {
	}

	public static function fake_static_callback() {
	}
}
