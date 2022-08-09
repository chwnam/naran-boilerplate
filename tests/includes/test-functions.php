<?php

/**
 * Naran Boilerplate Tests
 *
 * test-functions.php
 */
class Test_Functions extends WP_UnitTestCase {
	public function setup(): void {
		// Initialize
		NBPC_Main::set_instance( new NBPC_Main() );
	}

	public function test_nbpc() {
		$this->assertInstanceOf( NBPC_Main::class, nbpc() );
	}

	public function test_nbpc_parse_module() {
		$this->assertInstanceOf( NBPC_Register_AJAX::class, nbpc_parse_module( 'registers.ajax' ) );
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

	/**
	 * @throws NBPC_Callback_Exception
	 */
	public function test_nbpc_parse_callback() {
		$anon = new class { public function cb(): void { } };
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

		// Check if non-exist callables throws an exception
		$this->expectException( NBPC_Callback_Exception::class );
		nbpc_parse_callback( [ $this, 'non_exist' ] );
	}

	public function fake_callback() {
	}

	public static function fake_static_callback() {
	}
}
