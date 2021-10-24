<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */


/**
 * Class Test_Hook_Impl
 *
 * @package nbpc
 */
class Test_Hook_Impl extends WP_UnitTestCase {
	use NBPC_Hook_Impl;

	private int $action_value = 0;

	public function test_add_action_remove_action() {
		// add_action with function.
		$this->add_action( 'nbpc_test_hook_action', '__return_empty_string', 28 );
		$this->assertEquals( 28, has_action( 'nbpc_test_hook_action', '__return_empty_string' ) );

		// remove action
		$this->remove_action( 'nbpc_test_hook_action', '__return_empty_string', 28 );
		$this->assertEquals( 0, has_action( 'nbpc_test_hook_action', '__return_empty_string' ) );

		// add_action with class method, and null priority.
		$this->add_action( 'nbpc_test_hook_action', 'fake_action_callback' );
		$this->assertEquals(
			nbpc()->get_priority(), // Check if null priority is accepted as the default priority.
			has_action( 'nbpc_test_hook_action', [ $this, 'fake_action_callback' ] )
		);

		// Check if add_action relly works.
		$this->assertEquals( 0, $this->action_value );
		do_action( 'nbpc_test_hook_action' );
		$this->assertEquals( 1, $this->action_value );

		// remove action
		$this->remove_action( 'nbpc_test_hook_action', 'fake_action_callback' );
		$this->assertEquals( 0, has_action( 'nbpc_test_hook_action', 'fake_action_callback' ) );
	}

	public function test_add_filter_remove_filter() {
		// add_filter with function
		$this->add_filter( 'nbpc_test_hook_filter', '__return_empty_string', 56 );
		$this->assertEquals( 56, has_filter( 'nbpc_test_hook_filter', '__return_empty_string' ) );

		// remove filter
		$this->remove_filter( 'nbpc_test_hook_filter', '__return_empty_string', 56 );
		$this->assertEquals( 0, has_action( 'nbpc_test_hook_filter', '__return_empty_string' ) );

		// add_filter with class method, and null priority.
		$this->add_filter( 'nbpc_test_hook_filter', 'fake_filter_callback' );
		$this->assertEquals(
			nbpc()->get_priority(), // Check if null priority is accepted as the default priority.
			has_filter( 'nbpc_test_hook_filter', [ $this, 'fake_filter_callback' ] )
		);

		// Check if add_filter relly works.
		$this->assertEquals( 2, apply_filters( 'nbpc_test_hook_filter', 1 ) );

		// remove action
		$this->remove_action( 'nbpc_test_hook_filter', 'fake_filter_callback' );
		$this->assertEquals( 0, has_action( 'nbpc_test_hook_filter', 'fake_filter_callback' ) );
	}

	public function fake_action_callback() {
		$this->action_value = 1;
	}

	public function fake_filter_callback( int $args ): int {
		return 2 * $args;
	}
}
