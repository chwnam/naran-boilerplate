<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


class Test_Register_Block extends WP_UnitTestCase {
	public function setUp(): void {
		new class() extends NBPC_Register_Base_Block {
			public function get_items(): Generator {
				yield new NBPC_Reg_Block( 'nbpc/nbpc-test' );
			}
		};
	}

	public function test_block() {
		do_action( 'init' );

		$instance = WP_Block_Type_Registry::get_instance();
		$this->assertTrue( $instance->is_registered( 'nbpc/nbpc-test' ) );
	}
}
