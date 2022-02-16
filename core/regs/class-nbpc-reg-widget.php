<?php
/**
 * NBPC: Widget reg.
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Widget' ) ) {
	class NBPC_Reg_Widget implements NBPC_Reg {
		/**
		 * @var object|string
		 */
		public $widget;

		/**
		 * Constructor method
		 *
		 * @param string|object $widget
		 */
		public function __construct( $widget ) {
			$this->widget = $widget;
		}

		public function register( $dispatch = null ): void {
			register_widget( $this->widget );
		}
	}
}
