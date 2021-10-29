<?php
/**
 * @noinspection PhpIllegalPsrClassPathInspection
 * @noinspection PhpMultipleClassDeclarationsInspection
 */

class Test_Register_Cron_Schedule extends WP_UnitTestCase {
	private $register;

	public function setUp() {
		$this->register = new class() extends NBPC_Register_Cron_Schedule {
			public array $schedules = [
				[ 'nbpc_sched_1', 90, 'NBPC Test Schedule #1' ],
				[ 'nbpc_sched_2', 180, 'NBPC Test Schedule #2' ],
				[ 'nbpc_sched_3', 270, 'NBPC Test Schedule #3' ],
			];

			public function get_items(): Generator {
				yield new NBPC_Reg_Cron_Schedule( ...$this->schedules[0] );
				yield new NBPC_Reg_Cron_Schedule( ...$this->schedules[1] );
				yield new NBPC_Reg_Cron_Schedule( ...$this->schedules[2] );
			}
		};
	}

	public function test_schedules() {
		$schedules = wp_get_schedules();

		// Check if our schedules are returned.
		$name = $this->register->schedules[0][0];
		$this->assertArrayHasKey( $name, $schedules );
		$this->assertEquals( $this->register->schedules[0][1], $schedules[ $name ]['interval'] );
		$this->assertEquals( $this->register->schedules[0][2], $schedules[ $name ]['display'] );

		$name = $this->register->schedules[1][0];
		$this->assertArrayHasKey( $name, $schedules );
		$this->assertEquals( $this->register->schedules[1][1], $schedules[ $name ]['interval'] );
		$this->assertEquals( $this->register->schedules[1][2], $schedules[ $name ]['display'] );

		$name = $this->register->schedules[2][0];
		$this->assertArrayHasKey( $name, $schedules );
		$this->assertEquals( $this->register->schedules[2][1], $schedules[ $name ]['interval'] );
		$this->assertEquals( $this->register->schedules[2][2], $schedules[ $name ]['display'] );
	}
}