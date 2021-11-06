<?php
/**
 * NBPC: Cron schedule register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Cron_Schedule' ) ) {
	class NBPC_Register_Cron_Schedule extends NBPC_Register_Base_Cron_Schedule {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Cron_Schedule();
		}
	}
}
