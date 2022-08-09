<?php
/**
 * Naran Boilerplate Core
 *
 * interfaces/interface-nbpc-front-module.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Front_Module' ) ) {
	interface NBPC_Front_Module extends NBPC_Module {
		public function display(): void;
	}
}
