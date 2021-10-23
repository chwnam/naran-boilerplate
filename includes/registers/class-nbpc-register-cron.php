<?php
/**
 * NBPC: Cron register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Cron' ) ) {
	class NBPC_Register_Cron implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			register_activation_hook( nbpc()->get_main_file(), [ $this, 'register' ] );
			register_deactivation_hook( nbpc()->get_main_file(), [ $this, 'unregister' ] );
		}

		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Cron ) {
					$item->register();
				}
			}
		}

		public function unregister() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Cron ) {
					$item->unregister();
				}
			}
		}

		public function get_items(): Generator {
			yield null;
		}
	}
}
