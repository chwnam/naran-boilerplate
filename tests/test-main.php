<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Main
 *
 * @package nbpc
 */
class Test_Main extends WP_UnitTestCase {
	public function test_nbpc() {
		$nbpc = NBPC_Main::get_instance();

		// Check submodule types
		$this->assertInstanceOf( NBPC_Main::class, $nbpc );
		$this->assertInstanceOf( NBPC_Admins::class, $nbpc->admins );
		$this->assertInstanceOf( NBPC_Registers::class, $nbpc->registers );

		// Check getter methods.
		$this->assertEquals( NBPC_MAIN_FILE, $nbpc->get_main_file() );
		$this->assertEquals( NBPC_VERSION, $nbpc->get_version() );
		$this->assertEquals( NBPC_PRIORITY, $nbpc->get_priority() );

		// Check if set/set method works.
		$nbpc->set( 'foo', '1' );
		$this->assertEquals( '1', $nbpc->get( 'foo' ) );
		$this->assertEquals( '', $nbpc->get( 'bar' ) );

		// Check if conditional module callback is added.
		$this->assertEquals( $nbpc->get_priority(), has_action( 'wp', [ $nbpc, 'init_conditional_modules' ] ) );

		// Check if 'nbpc_initialized' action is done.
		$this->assertTrue( boolval( did_action( 'nbpc_initialized' ) ) );
		// Check if 'get_module_by_notation' method works.
	}

	public function test_get_module_by_notation() {
		$nbpc = NBPC_Main::get_instance();

		$this->assertInstanceOf( NBPC_Admins::class, $nbpc->get_module_by_notation( 'admins' ) );
		$this->assertInstanceOf( NBPC_Registers::class, $nbpc->get_module_by_notation( 'registers' ) );

		// Check registers.activation
		$this->assertInstanceOf(
			NBPC_Register_Activation::class,
			$nbpc->get_module_by_notation( 'registers.activation' )
		);

		// Check registers.ajax
		$this->assertInstanceOf(
			NBPC_Register_Ajax::class,
			$nbpc->get_module_by_notation( 'registers.ajax' )
		);

		// Check registers.comment_meta
		$this->assertInstanceOf(
			NBPC_Register_Comment_Meta::class,
			$nbpc->get_module_by_notation( 'registers.comment_meta' )
		);

		// Check registers.cron
		$this->assertInstanceOf(
			NBPC_Register_Cron::class,
			$nbpc->get_module_by_notation( 'registers.cron' )
		);

		// Check registers.cron_schedule
		$this->assertInstanceOf(
			NBPC_Register_Cron_Schedule::class,
			$nbpc->get_module_by_notation( 'registers.cron_schedule' )
		);

		// Check registers.deactivation
		$this->assertInstanceOf(
			NBPC_Register_Deactivation::class,
			$nbpc->get_module_by_notation( 'registers.deactivation' )
		);

		// Check registers.option
		$this->assertInstanceOf(
			NBPC_Register_Option::class,
			$nbpc->get_module_by_notation( 'registers.option' )
		);

		// Check registers.post_meta
		$this->assertInstanceOf(
			NBPC_Register_Post_Meta::class,
			$nbpc->get_module_by_notation( 'registers.post_meta' )
		);

		// Check registers.post_type
		$this->assertInstanceOf(
			NBPC_Register_Post_Type::class,
			$nbpc->get_module_by_notation( 'registers.post_type' )
		);

		// Check registers.script
		$this->assertInstanceOf(
			NBPC_Register_Script::class,
			$nbpc->get_module_by_notation( 'registers.script' )
		);

		// Check registers.style
		$this->assertInstanceOf(
			NBPC_Register_Style::class,
			$nbpc->get_module_by_notation( 'registers.style' )
		);

		// Check registers.submit
		$this->assertInstanceOf(
			NBPC_Register_Submit::class,
			$nbpc->get_module_by_notation( 'registers.submit' )
		);

		// Check registers.taxonomy
		$this->assertInstanceOf(
			NBPC_Register_Taxonomy::class,
			$nbpc->get_module_by_notation( 'registers.taxonomy' )
		);

		// Check registers.term_meta
		$this->assertInstanceOf(
			NBPC_Register_Term_Meta::class,
			$nbpc->get_module_by_notation( 'registers.term_meta' )
		);

		// Check registers.user_meta
		$this->assertInstanceOf(
			NBPC_Register_User_Meta::class,
			$nbpc->get_module_by_notation( 'registers.user_meta' )
		);
	}

	public function test_parse_callback() {
		$nbpc = NBPC_Main::get_instance();

		// Check if closure is returned as-is.
		$func    = function () { };
		$closure = Closure::fromCallable( $func );
		$this->assertSame( $func, $nbpc->parse_callback( $func ) );
		$this->assertSame( $closure, $nbpc->parse_callback( $closure ) );

		// Check if callable array is returned as-is.
		$this->assertSame(
			[ $this, 'test_parse_callback' ],
			$nbpc->parse_callback( [ $this, 'test_parse_callback' ] )
		);

		// Check if function is returned as-is.
		$this->assertSame( 'get_post', $nbpc->parse_callback( 'get_post' ) );

		// Check if string notation parsing works.
		$this->assertSame(
			[ $nbpc->registers->ajax, 'get_items' ],
			$nbpc->parse_callback( 'registers.ajax@get_items' )
		);

		// Check if wrong notation raise exception.
		$this->expectException(NBPC_Callback_Exception::class);
		$nbpc->parse_callback( 'non_exist@method' );
	}
}
