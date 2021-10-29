<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Submit extends WP_UnitTestCase {
	private $register;

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Submit {
			public function get_items(): Generator {
				yield new NBPC_Reg_Submit( 'action_normal', 'callback_0' );

				yield new NBPC_Reg_Submit( 'action_nopriv', 'callback_1', true );

				yield new NBPC_Reg_Submit( 'action_only_nopriv', 'callback_2', 'only_nopriv', 50 );
			}
		};

		do_action( 'init' );
	}

	public function test_normal() {
		/** @var NBPC_Reg_Submit $item */
		$item = iterator_to_array( $this->register->get_items() )[0];

		// Check if item is instance of NPBC_Reg_Ajax.
		$this->assertInstanceOf( NBPC_Reg_Submit::class, $item );

		// Check if action string is correct.
		$this->assertEquals( 'action_normal', $item->action );

		// Check if callback string is correct.
		$this->assertEquals( 'callback_0', $item->callback );

		// Check if action is correctly added.
		$this->assertEquals(
			$item->priority,
			has_action( "admin_post_{$item->action}", [ $this->register, 'dispatch' ] )
		);

		// 'nopriv' must be skipped.
		$this->assertEquals(
			0,
			has_action( "admin_post_nopriv_{$item->action}", [ $this->register, 'dispatch' ] )
		);

		// Check priority vaulue.
		$this->assertEquals( nbpc()->get_priority(), $item->priority );
	}

	public function test_nopriv() {
		/** @var NBPC_Reg_Submit $item */
		$item = iterator_to_array( $this->register->get_items() )[1];

		// Check if item is instance of NPBC_Reg_Ajax.
		$this->assertInstanceOf( NBPC_Reg_Submit::class, $item );

		// Check if action string is correct.
		$this->assertEquals( 'action_nopriv', $item->action );

		// Check if callback string is correct.
		$this->assertEquals( 'callback_1', $item->callback );

		// Check if actions are correctly added.
		$this->assertEquals(
			$item->priority,
			has_action( "admin_post_{$item->action}", [ $this->register, 'dispatch' ] )
		);

		// 'nopriv' is also added.
		$this->assertEquals(
			$item->priority,
			has_action( "admin_post_nopriv_{$item->action}", [ $this->register, 'dispatch' ] )
		);

		// Check priority vaulue.
		$this->assertEquals( nbpc()->get_priority(), $item->priority );
	}

	public function test_only_nopriv() {
		/** @var NBPC_Reg_Submit $item */
		$item = iterator_to_array( $this->register->get_items() )[2];

		// Check if item is instance of NPBC_Reg_Ajax.
		$this->assertInstanceOf( NBPC_Reg_Submit::class, $item );

		// Check if action string is correct.
		$this->assertEquals( 'action_only_nopriv', $item->action );

		// Check if callback string is correct.
		$this->assertEquals( 'callback_2', $item->callback );

		// Check if actions are correctly added.
		$this->assertEquals(
			0,
			has_action( "admin_post_{$item->action}", [ $this->register, 'dispatch' ] )
		);

		// only 'nopriv' is added.
		$this->assertEquals(
			$item->priority,
			has_action( "admin_post_nopriv_{$item->action}", [ $this->register, 'dispatch' ] )
		);

		// Check priority vaulue.
		$this->assertEquals( 50, $item->priority );
	}
}