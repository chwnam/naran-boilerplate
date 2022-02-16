<?php
/**
 * NBPC: Activation register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Activation' ) ) {
	abstract class NBPC_Register_Base_Activation implements NBPC_Register {
		/**
		 * Constructor method.
		 */
		public function __construct() {
			register_activation_hook( nbpc()->get_main_file(), [ $this, 'register' ] );
		}

		/**
		 * Method name can mislead, but it does activation callback jobs.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Activation ) {
					$item->register();
				}
			}
		}
	}
}
