<?php
/**
 * NBPC: Style register base
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Base_Style' ) ) {
	abstract class NBPC_Register_Base_Style implements NBPC_Register {
		use NBPC_Hook_Impl;

		/**
		 * Constructor method.
		 */
		public function __construct() {
			$this->add_action( 'init', 'register' );
		}

		/**
		 * @callback
		 * @action       init
		 *
		 * @return void
		 */
		public function register(): void {
			foreach ( $this->get_items() as $item ) {
				if ( $item instanceof NBPC_Reg_Style ) {
					$item->register();
				}
			}
		}

		/**
		 * 'src' location helper.
		 *
		 * @param string $rel_path
		 * @param bool   $replace_min
		 *
		 * @return string
		 */
		protected function src_helper( string $rel_path, bool $replace_min = true ): string {
			$rel_path = trim( $rel_path, '\\/' );

			if ( $replace_min && nbpc_script_debug() && substr( $rel_path, - 8 ) === '.min.css' ) {
				$rel_path = substr( $rel_path, 0, - 8 ) . '.css';
			}

			if ( nbpc_is_theme() ) {
				return get_stylesheet_directory_uri() . "/assets/css/$rel_path";
			} else {
				return plugin_dir_url( nbpc()->get_main_file() ) . 'assets/css/' . $rel_path;
			}
		}
	}
}
