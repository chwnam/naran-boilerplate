<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

/**
 * Class Test_Register_Ajax
 *
 * @package nbpc
 */
class Test_Register_Ajax extends WP_UnitTestCase {
	private $register;

	public function setUp(): void {
		$this->register = new class() extends NBPC_Register_AJAX {
			public function get_items(): Generator {
				yield new NBPC_Reg_AJAX( 'action_normal', 'callback_0' );

				yield new NBPC_Reg_AJAX( 'action_nopriv', 'callback_1', true );

				yield new NBPC_Reg_AJAX( 'action_only_nopriv', 'callback_2', 'only_nopriv' );

				yield new NBPC_Reg_AJAX( 'action_wc-ajax', 'callback_3', false, true, 55 );

				yield new NBPC_Reg_AJAX( 'action_wc_ajax_test', function () {
					$this->test_called = true;
				}, false, true );
			}

			public bool $test_called = false;
		};

		do_action( 'init' );
	}

	public function test_normal() {
		/** @var NBPC_Reg_AJAX $item */
		$item = iterator_to_array( $this->register->get_items() )[0];

		// Check if item is instance of NPBC_Reg_AJAX.
		$this->assertInstanceOf( NBPC_Reg_AJAX::class, $item );

		// Check if action string is correct.
		$this->assertEquals( 'action_normal', $item->action );

		// Check if callback string is correct.
		$this->assertEquals( 'callback_0', $item->callback );

		// Check if action is correctly added.
		$this->assertEquals(
			$item->priority,
			has_action( "wp_ajax_$item->action", [ $this->register, 'dispatch' ] )
		);

		// 'nopriv' must be skipped.
		$this->assertEquals(
			0,
			has_action( "wp_ajax_nopriv_$item->action", [ $this->register, 'dispatch' ] )
		);

		// Check priority value.
		$this->assertEquals( nbpc_priority(), $item->priority );
	}

	public function test_nopriv() {
		/** @var NBPC_Reg_AJAX $item */
		$item = iterator_to_array( $this->register->get_items() )[1];

		// Check if item is instance of NPBC_Reg_AJAX.
		$this->assertInstanceOf( NBPC_Reg_AJAX::class, $item );

		// Check if action string is correct.
		$this->assertEquals( 'action_nopriv', $item->action );

		// Check if callback string is correct.
		$this->assertEquals( 'callback_1', $item->callback );

		// Check if actions are correctly added.
		$this->assertEquals(
			$item->priority,
			has_action( "wp_ajax_$item->action", [ $this->register, 'dispatch' ] )
		);

		// 'nopriv' is also added.
		$this->assertEquals(
			$item->priority,
			has_action( "wp_ajax_nopriv_$item->action", [ $this->register, 'dispatch' ] )
		);

		// Check priority value.
		$this->assertEquals( nbpc_priority(), $item->priority );
	}

	public function test_only_nopriv() {
		/** @var NBPC_Reg_AJAX $item */
		$item = iterator_to_array( $this->register->get_items() )[2];

		// Check if item is instance of NPBC_Reg_AJAX.
		$this->assertInstanceOf( NBPC_Reg_AJAX::class, $item );

		// Check if action string is correct.
		$this->assertEquals( 'action_only_nopriv', $item->action );

		// Check if callback string is correct.
		$this->assertEquals( 'callback_2', $item->callback );

		// Check if actions are correctly added.
		$this->assertEquals(
			0,
			has_action( "wp_ajax_$item->action", [ $this->register, 'dispatch' ] )
		);

		// only 'nopriv' is added.
		$this->assertEquals(
			$item->priority,
			has_action( "wp_ajax_nopriv_$item->action", [ $this->register, 'dispatch' ] )
		);

		// Check priority value.
		$this->assertEquals( nbpc_priority(), $item->priority );
	}

	public function test_wc_ajax_and_priority() {
		/** @var NBPC_Reg_AJAX $item */
		$item = iterator_to_array( $this->register->get_items() )[3];

		// Check if item is instance of NPBC_Reg_AJAX.
		$this->assertInstanceOf( NBPC_Reg_AJAX::class, $item );

		// Check if action string is correct.
		$this->assertEquals( 'action_wc-ajax', $item->action );

		// Check if callback string is correct.
		$this->assertEquals( 'callback_3', $item->callback );

		// Check if wp_ajax_ is excluded.
		$this->assertEquals(
			0,
			has_action( "wp_ajax_$item->action", [ $this->register, 'dispatch' ] )
		);

		// Check if wp_ajax_nopriv_ is excluded.
		$this->assertEquals(
			0,
			has_action( "wp_ajax_nopriv$item->action", [ $this->register, 'dispatch' ] )
		);

		// Check if 'wc_ajax_' is added.
		$this->assertEquals(
			$item->priority,
			has_action( "wc_ajax_$item->action", [ $this->register, 'dispatch' ] )
		);

		// Check priority value.
		$this->assertEquals( 55, $item->priority );
	}

	public function test_wc_ajax_invocation() {
		// Check if wc-ajax callback is really invoked. (Issue #61)
		$_GET['wc-ajax'] = 'action_wc_ajax_test';

		$this->register->test_called = false;

		do_action( 'wc_ajax_action_wc_ajax_test' );

		$this->assertTrue( $this->register->test_called );
	}
}
