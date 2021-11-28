<?php
/**
 * NBPC: Sidebar register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Sidebar' ) ) {
	class NBPC_Register_Sidebar extends NBPC_Register_Base_Sidebar {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Sidebar();
		}
	}
}
