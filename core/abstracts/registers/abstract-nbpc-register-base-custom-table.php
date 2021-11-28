<?php
/**
 * NBPC: Custom table register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Custom_Table' ) ) {
	abstract class NBPC_Register_Base_Custom_Table implements NBPC_Register {
		public function register() {
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Custom_Table ) {
					$item->register();
				}
			}
		}
	}
}
