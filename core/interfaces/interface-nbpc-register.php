<?php
/**
 * NBPC: Register interface
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Register' ) ) {
	interface NBPC_Register {
		public function get_items(): Generator;

		public function register();
	}
}
