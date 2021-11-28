<?php
/**
 * NBPC: Sidebar register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Sidebar' ) ) {
	abstract class NBPC_Register_Base_Sidebar implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			$this->add_action( 'widgets_init', 'register' );
		}

		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Sidebar ) {
					$item->register();
				}
			}
		}
	}
}
