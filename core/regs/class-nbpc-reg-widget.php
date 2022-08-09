<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-widget.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Widget' ) ) {
	class NBPC_Reg_Widget implements NBPC_Reg {
		/**
		 * Constructor method
		 *
		 * @param WP_Widget|string| $widget String is class name of Widget subclass.
		 */
		public function __construct(
			public WP_Widget|string $widget
		) {
		}

		public function register( $dispatch = null ): void {
			register_widget( $this->widget );
		}
	}
}
