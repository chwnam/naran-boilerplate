<?php
/**
 * NBPC: Deactivation register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Deactivation' ) ) {
	class NBPC_Register_Deactivation extends NBPC_Register_Base_Deactivation {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Deactivation();
		}
	}
}
