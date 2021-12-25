<?php
/**
 * NBPC: Block register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Block' ) ) {
	class NBPC_Register_Block extends NBPC_Register_Base_Block {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Block();
		}
	}
}
