<?php
/**
 * NBPC: Menu register.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Menu' ) ) {
	class NBPC_Register_Menu extends NBPC_Register_Base_Menu {
		public function get_items(): Generator {
			yield;
			// yield new NBPC_Reg_Menu();
			// yield new NBPC_Reg_Submenu();
		}
	}
}
