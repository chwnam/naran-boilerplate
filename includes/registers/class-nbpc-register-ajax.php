<?php
/**
 * NBPC: AJAX (admin-ajax.php, or wc-ajax) register.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Ajax' ) ) {
	class NBPC_Register_Ajax extends NBPC_Register_Base_Ajax {
		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Ajax();
		}
	}
}
