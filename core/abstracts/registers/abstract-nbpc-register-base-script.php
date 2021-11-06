<?php
/**
 * NBPC: Script register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Script' ) ) {
	abstract class NBPC_Register_Base_Script implements NBPC_Register {
		use NBPC_Hook_Impl;

		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * @callback
		 * @action       init
		 */
		public function register() {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Script ) {
					$item->register();
				}
			}
		}

		/**
		 * @param string $rel_path
		 * @param bool   $replace_min
		 *
		 * @return string
		 */
		protected function src_helper( string $rel_path, bool $replace_min = true ): string {
			$rel_path = trim( $rel_path, '\\/' );

			if ( nbpc_script_debug() && $replace_min && substr( $rel_path, - 7 ) === '.min.js' ) {
				$rel_path = substr( $rel_path, 0, strlen( $rel_path ) - 7 ) . '.js';
			}

			return plugin_dir_url( nbpc()->get_main_file() ) . "assets/js/{$rel_path}";
		}
	}
}
