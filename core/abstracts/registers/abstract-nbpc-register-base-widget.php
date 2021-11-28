<?php
/**
 * NBPC: Widget register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Widget' ) ) {
	abstract class NBPC_Register_Base_Widget implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			$this->add_action( 'widgets_init', 'register' );
		}

		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Widget ) {
					$item->register();
				}
			}
		}
	}
}
