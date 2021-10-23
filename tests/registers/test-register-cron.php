<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Cron extends WP_UnitTestCase {
	private $register;

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Cron {
			public int $now;

			public function __construct() {
				parent::__construct();
				$this->now = time();
			}

			public function get_items(): Generator {
				yield new NBPC_Reg_Cron(
					$this->now,
					'daily',
					'nbpc_test_cron',
					[ 'foo' => 'bar' ]
				);

				yield new NBPC_Reg_Cron(
					$this->now + 20,
					'hourly',
					'nbpc_test_cron_single',
					[],
					false,
					true // Single event.
				);
			}
		};
	}

	public function test_cron_activation() {
		$file = plugin_basename( nbpc()->get_main_file() );
		do_action( 'activate_' . $file );

		// Check if a wrong arg returns false.
		$this->assertFalse( wp_get_scheduled_event( 'nbpc_test_cron' ) );

		// Check if the schedule is returned.
		$event = wp_get_scheduled_event( 'nbpc_test_cron', [ 'foo' => 'bar' ] );
		$this->assertIsObject( $event );
		$this->assertEqualSets( [ 'foo' => 'bar' ], $event->args );
		$this->assertEquals( 'nbpc_test_cron', $event->hook );
		$this->assertEquals( DAY_IN_SECONDS, $event->interval );
		$this->assertEquals( 'daily', $event->schedule );
		$this->assertEquals( $this->register->now, $event->timestamp );

		// Check if the single schedule is returned
		$event = wp_get_scheduled_event( 'nbpc_test_cron_single' );
		$this->assertIsObject( $event );
		$this->assertEmpty( $event->args );
		$this->assertEquals( 'nbpc_test_cron_single', $event->hook );
		$this->assertFalse( $event->schedule ); // False means that it is a single event.
		$this->assertEquals( $this->register->now + 20, $event->timestamp );
	}

	public function test_cron_deactivation() {
		$file = plugin_basename( nbpc()->get_main_file() );
		do_action( 'activate_' . $file );

		$file = plugin_basename( nbpc()->get_main_file() );
		do_action( 'deactivate_' . $file );

		// Check if the schedule is removed.
		$event = wp_get_scheduled_event( 'nbpc_test_cron', [ 'foo' => 'bar' ] );
		$this->assertFalse( $event );

		// Check if the single schedule is removed.
		$event = wp_get_scheduled_event( 'nbpc_test_cron_single' );
		$this->assertFalse( $event );
	}
}