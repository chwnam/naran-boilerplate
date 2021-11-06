<?php
/**
 * NBPC: Script register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Script' ) ) {
	class NBPC_Register_Script extends NBPC_Register_Base_Script {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Script();
		}
	}
}
