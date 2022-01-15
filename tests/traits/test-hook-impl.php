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

		// Check if calling add_action() without $function_to_add is works.
		$this->action_value = 0;
		$this->add_action( 'fake_action_callback' );
		do_action( 'fake_action_callback' );
		$this->assertEquals( 1, $this->action_value );
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

		// Check if calling add_filter() without $function_to_add is works.
		$this->add_action( 'fake_filter_callback' );
		$this->assertEquals( 2, apply_filters( 'fake_filter_callback', 1 ) );
	}

	public function test_add_action_once() {
		$counter = 0;

		// Callback: increase $counter.
		$this->add_action_once( 'test_action_once', function () use ( &$counter ) { $counter += 1; } );

		// Call the action twice.
		do_action( 'test_action_once' );
		do_action( 'test_action_once' );

		// Check if $counter is one even if the action is called twice.
		$this->assertEquals( 1, $counter );
	}

	public function test_add_filter_once() {
		$counter = 0;

		// Filter: increase $counter.
		$this->add_filter_once( 'test_filter_once', function ( $c ) { return $c + 1; } );

		// Apply the filter twice.
		$counter = apply_filters( 'test_filter_once', $counter );
		$counter = apply_filters( 'test_filter_once', $counter );

		// Check if $counter is one even if the filter is called twice.
		$this->assertEquals( 1, $counter );
	}

	public function fake_action_callback() {
		$this->action_value = 1;
	}

	public function fake_filter_callback( int $args ): int {
		return 2 * $args;
	}
}
