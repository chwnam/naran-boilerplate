<?php
/**
 * NBPC: Registrable interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Reg' ) ) {
	interface NBPC_Reg {
		/**
		 * Register reg to core.
		 *
		 * @param mixed $dispatch Extra argument.
		 *
		 * @return mixed
		 */
		public function register( $dispatch = null );
	}
}
