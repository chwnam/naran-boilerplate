<?php
/**
 * NBPC: WP CLI register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_WP_CLI' ) ) {
	abstract class NBPC_Register_Base_WP_CLI implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				$this->add_action( 'plugins_loaded', 'register' );
			}
		}

		/**
		 * @throws Exception
		 */
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_WP_CLI ) {
					$item->register();
				}
			}
		}
	}
}