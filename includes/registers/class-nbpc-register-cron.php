<?php
/**
 * NBPC: Cron register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Cron' ) ) {
	class NBPC_Register_Cron extends NBPC_Register_Base_Cron {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Cron();
		}
	}
}
