<?php
/**
 * NBPC: Capability register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Capability' ) ) {
	abstract class NBPC_Register_Base_Capability implements NBPC_Register {
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Capability ) {
					$item->register();
				}
			}
		}

		public function unregister() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Capability ) {
					$item->unregister();
				}
			}
		}
	}
}
