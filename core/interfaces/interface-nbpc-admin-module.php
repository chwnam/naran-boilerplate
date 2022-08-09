<?php
/**
 * Naran Boilerplate Core
 *
 * interfaces/interface-nbpc-admin-module.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Admin_Module' ) ) {
	interface NBPC_Admin_Module extends NBPC_Module {
	}
}
