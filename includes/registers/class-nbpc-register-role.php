<?php
/**
 * NBPC: Role register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Role' ) ) {
	class NBPC_Register_Role extends NBPC_Register_Base_Role {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Role();
		}
	}
}
