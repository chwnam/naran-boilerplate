<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Sidebar extends WP_UnitTestCase {
	public function test_register() {
		$register = new class extends NBPC_Register_Base_Sidebar {
			public function get_items(): Generator {
				yield new NBPC_Reg_Sidebar(
					[
						'id'   => 'nbpc_teset_sidebar',
						'name' => 'NBPC Test Sidebar',
					]
				);
			}
		};

		do_action( 'widgets_init' );

		global $wp_registered_sidebars;

		$reg = iterator_to_array( $register->get_items() )[0];

		$this->assertArrayHasKey( $reg->id, $wp_registered_sidebars );
		$this->assertEquals( $reg->args['id'], $wp_registered_sidebars[ $reg->id ]['id'] );
	}
}
