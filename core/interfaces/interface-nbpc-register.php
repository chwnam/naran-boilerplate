<?php
/**
 * NBPC: Register interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Register' ) ) {
	interface NBPC_Register {
		/**
		 * Get list of regs.
		 *
		 * @return Generator
		 */
		public function get_items(): Generator;

		/**
		 * Register all regs.
		 */
		public function register();
	}
}
