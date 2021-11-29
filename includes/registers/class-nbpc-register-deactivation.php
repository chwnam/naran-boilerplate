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
			// Remove defined roles
			yield new NBPC_Reg_Activation( 'registers.role@unregister' );

			// Remove defined caps
			yield new NBPC_Reg_Activation( 'registers.cap@unregister' );
		}
	}
}
