<?php
/**
 * Naran Boilerplate Core
 *
 * abstracts/registers/abstract-nbpc-register-base-activation.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Activation' ) ) {
	abstract class NBPC_Register_Base_Activation implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'nbpc_activation', 'register' );
		}

		/**
		 * Method name can mislead, but it does activation callback jobs.
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Activation ) {
					$item->register();
				}
			}
		}
	}
}
