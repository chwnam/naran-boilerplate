<?php
/**
 * NBPC: Shortcode register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Shortcode' ) ) {
	class NBPC_Register_Shortcode extends NBPC_Register_Base_Shortcode {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Shortcode();
		}
	}
}
