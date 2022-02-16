<?php
/**
 * NBPC: Uninstall register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Uninstall' ) ) {
	abstract class NBPC_Register_Base_Uninstall implements NBPC_Register {
		/**
		 * Method name can mislead, but it does uninstall callback jobs.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Uninstall ) {
					$item->register();
				}
			}
		}
	}
}
