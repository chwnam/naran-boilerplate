<?php
/**
 * NBPC: Activation register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Activation' ) ) {
	class NBPC_Register_Activation extends NBPC_Register_Base_Activation {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Activation();
		}
	}
}
