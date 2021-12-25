<?php
/**
 * NBPC: Block register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CLASSNAME' ) ) {
	abstract class NBPC_Register_Base_Block implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Block ) {
					$item->register();
				}
			}
		}
	}
}
