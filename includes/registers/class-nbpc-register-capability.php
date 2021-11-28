<?php
/**
 * NBPC: Role register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( NBPC_Reg_Capability::class, 'NBPC_Reg_Cap' );

if ( ! class_exists( 'NBPC_Register_Capability' ) ) {
	class NBPC_Register_Capability extends NBPC_Register_Base_Capability {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Cap();
		}
	}
}
