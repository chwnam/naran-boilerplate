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
		$nbpc = nbpc();

		$this->assertInstanceOf( NBPC_Main::class, $nbpc );
		$this->assertInstanceOf( NBPC_Admins::class, $nbpc->admins );
		$this->assertInstanceOf( NBPC_Registers::class, $nbpc->registers );

		$this->assertEquals( NBPC_MAIN_FILE, $nbpc->get_main_file() );
		$this->assertEquals( NBPC_VERSION, $nbpc->get_version() );
		$this->assertEquals( NBPC_PRIORITY, $nbpc->get_priority() );

		$nbpc->set( 'foo', '1' );
		$this->assertEquals( '1', $nbpc->get( 'foo' ) );
		$this->assertEquals( '', $nbpc->get( 'bar' ) );

		$this->assertEquals( $nbpc->get_priority(), has_action( 'wp', [ $nbpc, 'init_conditional_modules' ] ) );
		$this->assertTrue( boolval( did_action( 'nbpc_initialized' ) ) );
	}
}
