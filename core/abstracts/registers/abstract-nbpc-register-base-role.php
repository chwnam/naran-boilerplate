<?php
/**
 * NBPC: Role register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Role' ) ) {
	abstract class NBPC_Register_Base_Role implements NBPC_Register {
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Role ) {
					$item->register();
				}
			}
		}
	}
}
