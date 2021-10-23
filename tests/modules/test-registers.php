<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class MainTest
 *
 * @package nbpc
 */
class Test_Registers extends WP_UnitTestCase {
	public function test_submodules() {
		$registers = nbpc()->registers;

		$this->assertInstanceOf( NBPC_Register_Activation::class, $registers->activation );
		$this->assertInstanceOf( NBPC_Register_Ajax::class, $registers->ajax );
		$this->assertInstanceOf( NBPC_Register_Comment_Meta::class, $registers->comment_meta );
		$this->assertInstanceOf( NBPC_Register_Cron::class, $registers->cron );
		$this->assertInstanceOf( NBPC_Register_Cron_Schedule::class, $registers->cron_schedule );
		$this->assertInstanceOf( NBPC_Register_Deactivation::class, $registers->deactivation );
		$this->assertInstanceOf( NBPC_Register_Option::class, $registers->option );
		$this->assertInstanceOf( NBPC_Register_Post_Meta::class, $registers->post_meta );
		$this->assertInstanceOf( NBPC_Register_Script::class, $registers->script );
		$this->assertInstanceOf( NBPC_Register_Style::class, $registers->style );
		$this->assertInstanceOf( NBPC_Register_Submit::class, $registers->submit );
		$this->assertInstanceOf( NBPC_Register_Taxonomy::class, $registers->taxonomy );
		$this->assertInstanceOf( NBPC_Register_Term_Meta::class, $registers->term_meta );
		$this->assertInstanceOf( NBPC_Register_User_Meta::class, $registers->user_meta );
	}
}
