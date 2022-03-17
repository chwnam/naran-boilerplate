<?php
/**
 * NBPC: Deactivation register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Deactivation' ) ) {
	abstract class NBPC_Register_Base_Deactivation implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'nbpc_deactivation', 'register' );
		}

		/**
		 * Method name can mislead, but it does deactivation callback jobs.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Deactivation ) {
					$item->register();
				}
			}
		}
	}
}
