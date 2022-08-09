<?php
/**
 * Naran Boilerplate Core
 *
 * interfaces/interface-nbpc-register.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Register' ) ) {
	interface NBPC_Register {
		/**
		 * Get list of regs.
		 */
		public function get_items(): Generator;

		/**
		 * Register all regs.
		 */
		public function register();
	}
}
