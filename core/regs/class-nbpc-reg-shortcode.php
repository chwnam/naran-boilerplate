<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-shortcode.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Shortcode' ) ) {
	class NBPC_Reg_Shortcode implements NBPC_Reg {
		/**
		 * Constructor method
		 */
		public function __construct(
			public string $tag,
			public Closure|array|string $callback,
			public Closure|array|string|null $heading_action = null
		) {
		}

		public function register( $dispatch = null ): void {
			add_shortcode( $this->tag, $dispatch );
		}
	}
}
