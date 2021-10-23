<?php
/**
 * NBPC: Deactivation register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Deactivation' ) ) {
	class NBPC_Register_Deactivation implements NBPC_Register {
		public function __construct() {
			register_deactivation_hook( nbpc()->get_main_file(), [$this,'register'] );
		}

		/**
		 * Method name can mislead, but it does deactivation callback jobs.
		 */
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Deactivation ) {
					$item->register();
				}
			}
		}

		public function get_items(): Generator {
			yield null;
		}
	}
}
