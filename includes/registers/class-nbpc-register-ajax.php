<?php
/**
 * NBPC: AJAX (admin-ajax.php, or wc-ajax) register.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_AJAX' ) ) {
	class NBPC_Register_AJAX extends NBPC_Register_Base_AJAX {
		// Disable AJAX autobind.
		// protected bool $autobind = false;

		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_AJAX();
		}
	}
}
