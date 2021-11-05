<?php
/**
 * NBPC: Activation register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Activation' ) ) {
	class NBPC_Register_Activation implements NBPC_Register {
		public function __construct() {
			register_activation_hook( nbpc()->get_main_file(), [ $this, 'register' ] );
		}

		/**
		 * Method name can mislead, but it does activation callback jobs.
		 */
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Activation ) {
					$item->register();
				}
			}
		}

		public function get_items(): Generator {
			yield call_user_func( [ NBPC_Registers::class, 'regs_activation' ], $this );
		}
	}
}
