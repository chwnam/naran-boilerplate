<?php
/**
 * Naran Boilerplate Core
 *
 * abstracts/registers/abstract-nbpc-register-base-capability.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Capability' ) ) {
	abstract class NBPC_Register_Base_Capability implements NBPC_Register {
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Capability ) {
					$item->register();
				}
			}
		}

		public function unregister(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Capability ) {
					$item->unregister();
				}
			}
		}
	}
}
