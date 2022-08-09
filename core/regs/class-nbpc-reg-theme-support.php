<?php
/**
 * Naran Boilerplate Core
 *
 * regs/class-nbpc-reg-theme-support.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Reg_Theme_Support' ) ) {
	class NBPC_Reg_Theme_Support implements NBPC_Reg {
		private string $feature;

		private array $args;

		public function __construct( string $feature, ...$args ) {
			$this->feature = $feature;
			$this->args    = $args;
		}

		public function register( $dispatch = null ) {
			add_theme_support( $this->feature, ...$this->args );
		}
	}
}
