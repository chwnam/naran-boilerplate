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
		public function __construct() {
			register_uninstall_hook( nbpc()->get_main_file(), [ $this, 'register' ] );
		}

		/**
		 * Method name can mislead, but it does uninstall callback jobs.
		 */
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Uninstall ) {
					$item->register();
				}
			}
		}
	}
}
