<?php
/**
 * NBPC: Script register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Script' ) ) {
	class NBPC_Register_Script implements NBPC_Register {
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

		public function get_items(): Generator {
			yield new NBPC_Reg_Script(
				'nbpc-foo',
				$this->src_helper( 'script.min.js' ),
				[],
			);
		}

		/**
		 * @param string $rel_path
		 * @param bool   $replace_min
		 *
		 * @return string
		 */
		private function src_helper( string $rel_path, bool $replace_min = true ): string {
			$rel_path = trim( $rel_path, '\\/' );

			if (
				( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) &&
				$replace_min &&
				substr( $rel_path, - 7 ) === '.min.js'
			) {
				$rel_path = substr( $rel_path, 0, strlen( $rel_path ) - 7 ) . '.js';
			}

			return plugin_dir_url( nbpc()->get_main_file() ) . "assets/js/{$rel_path}";
		}
	}
}
