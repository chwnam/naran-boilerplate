<?php
/**
 * NBPC: Style register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Style' ) ) {
	class NBPC_Register_Style extends NBPC_Register_Base_Style {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Style();
		}
	}
}
