<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Post_Type extends WP_UnitTestCase {
	private $register;

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Post_Type {
			public array $args = [
				'label'  => 'NBPC Test',
				'public' => false
			];

			public function get_items(): Generator {
				yield new NBPC_Reg_Post_Type( 'nbpc_test', $this->args );
			}
		};

		do_action( 'init' );
	}

	public function test_post_type() {
		$post_type_object = get_post_type_object( 'nbpc_test' );

		$this->assertInstanceOf( WP_Post_Type::class, $post_type_object );
		$this->assertEquals( $this->register->args['label'], $post_type_object->label );
		$this->assertEquals( $this->register->args['public'], $post_type_object->public );
	}
}
