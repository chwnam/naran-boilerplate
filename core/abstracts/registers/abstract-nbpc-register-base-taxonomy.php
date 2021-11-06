<?php
/**
 * NBPC: Custom taxonomy register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Taxonomy' ) ) {
	abstract class NBPC_Register_Base_Taxonomy implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			$this->add_filter( 'init', 'register' );
		}

		/**
		 * @callback
		 * @actin       init
		 */
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Taxonomy ) {
					$item->register();
				}
			}
		}
	}
}
