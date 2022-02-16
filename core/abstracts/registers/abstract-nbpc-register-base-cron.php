<?php
/**
 * NBPC: Cron register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Cron' ) ) {
	abstract class NBPC_Register_Base_Cron implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			register_activation_hook( nbpc()->get_main_file(), [ $this, 'register' ] );
			register_deactivation_hook( nbpc()->get_main_file(), [ $this, 'unregister' ] );
		}

		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Cron ) {
					$item->register();
				}
			}
		}

		public function unregister(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Cron ) {
					$item->unregister();
				}
			}
		}
	}
}
