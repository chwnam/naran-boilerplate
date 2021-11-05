<?php
/**
 * NBPC: Cron schedule register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Cron_Schedule' ) ) {
	class NBPC_Register_Cron_Schedule implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			$this->add_filter( 'cron_schedules', 'register' );
		}

		/**
		 * @callback
		 * @filter   cron_schedules
		 *
		 * @return  array
		 * @see      wp_get_schedules()
		 */
		public function register(): array {
			$schedules = func_get_arg( 0 );

			foreach ( $this->get_items() as $item ) {
				if (
					$item instanceof NBPC_Reg_Cron_Schedule &&
					$item->interval > 0 &&
					! isset( $schedules[ $item->name ] )
				) {
					$schedules[ $item->name ] = [
						'interval' => $item->interval,
						'display'  => $item->display,
					];
				}
			}

			return $schedules;
		}

		public function get_items(): Generator {
			yield call_user_func( [ NBPC_Registers::class, 'regs_cron_schedule' ], $this );
		}
	}
}
