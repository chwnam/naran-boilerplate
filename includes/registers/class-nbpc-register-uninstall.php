<?php
/**
 * NBPC: Uninstall register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Uninstall' ) ) {
	class NBPC_Register_Uninstall extends NBPC_Register_Base_Uninstall {
		public function get_items(): Generator {
			yield new NBPC_Reg_Uninstall( 'registers.custom_table@unregister' );
		}
	}
}
