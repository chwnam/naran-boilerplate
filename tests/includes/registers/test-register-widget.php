<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Widget extends WP_UnitTestCase {
	public function test_register() {
		$register = new class extends NBPC_Register_Base_Widget {
			public function get_items(): Generator {
				yield new NBPC_Reg_Widget( new class extends WP_Widget {
					public function __construct() { parent::__construct( 'nbpc_widget_1', 'NBPC Widget 1' ); }
				} );
			}
		};

		do_action( 'widgets_init' );

		global $wp_widget_factory;

		$filtered = array_values( wp_list_filter( $wp_widget_factory->widgets, [ 'id_base' => 'nbpc_widget_1' ] ) );
		$reg      = iterator_to_array( $register->get_items() )[0]->widget;

		$this->assertEquals( $filtered[0], $reg );
	}
}