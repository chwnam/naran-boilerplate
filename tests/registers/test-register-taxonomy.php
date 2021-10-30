<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Texonomy extends WP_UnitTestCase {
	private $register;

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Taxonomy {
			public array $args = [
				'label'  => 'NBPC Tag',
				'public' => true
			];

			public function get_items(): Generator {
				yield new NBPC_Reg_Taxonomy( 'nbpc_tag', 'post', $this->args );
			}
		};

		do_action( 'init' );
	}

	public function test_taxonomy() {
		$taxonomy = get_taxonomy( 'nbpc_tag' );

		$this->assertInstanceOf( WP_Taxonomy::class, $taxonomy );
		$this->assertEqualSets( [ 'post' ], $taxonomy->object_type );
		$this->assertEquals( $this->register->args['label'], $taxonomy->label );
		$this->assertEquals( $this->register->args['public'], $taxonomy->public );
	}
}