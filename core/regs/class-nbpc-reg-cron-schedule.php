<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-cron-schedule.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Cron_Schedule' ) ) {
	class NBPC_Reg_Cron_Schedule implements NBPC_Reg {
		/**
		 * Constructor method
		 */
		public function __construct(
			public string $name,
			public int $interval,
			public string $display
		) {
		}

		public function register( $dispatch = null ): void {
			// Do nothing.
		}
	}
}
