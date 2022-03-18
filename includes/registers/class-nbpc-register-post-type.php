<?php
/**
 * NBPC: Custom post type register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Post_Type' ) ) {
	class NBPC_Register_Post_Type extends NBPC_Register_Base_Post_Type {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Post_Type();
		}
	}
}
