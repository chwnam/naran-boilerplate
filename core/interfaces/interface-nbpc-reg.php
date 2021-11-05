<?php
/**
 * NBPC: Registerable interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Reg' ) ) {
	interface NBPC_Reg {
		public function register( $dispatch = null );
	}
}
