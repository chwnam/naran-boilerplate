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
			// Add defined roles
			yield new NBPC_Reg_Activation( 'registers.role@register' );

			// Add defined caps
			yield new NBPC_Reg_Activation( 'registers.cap@register' );

			// Add custom tables
			yield new NBPC_Reg_Activation( 'registers.custom_table@register' );
		}
	}
}
