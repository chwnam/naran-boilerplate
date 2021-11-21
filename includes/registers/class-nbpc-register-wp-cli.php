<?php
/**
 * NBPC: WP-CLI register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_WP_CLI' ) ) {
	class NBPC_Register_WP_CLI extends NBPC_Register_Base_WP_CLI {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_WP_CLI();
		}
	}
}
