<?php
/**
 * NBPC: Custom table register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Custom_Table' ) ) {
	class NBPC_Register_Custom_Table extends NBPC_Register_Base_Custom_Table {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Custom_Table();
		}
	}
}
