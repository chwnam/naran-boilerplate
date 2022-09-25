<?php
/**
 * NBPC: Submit (admin-post.php) register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Submit' ) ) {
	class NBPC_Register_Submit extends NBPC_Register_Base_Submit {
		// Disable 'admin-post.php' autobind.
		// protected bool $autobind = false;

		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Submit();
		}
	}
}
