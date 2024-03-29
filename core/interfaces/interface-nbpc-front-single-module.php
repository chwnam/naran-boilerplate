<?php
/**
 * Naran Boilerplate Core
 *
 * interfaces/interface-nbpc-front-single-module.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Front_Single_Module' ) ) {
	interface NBPC_Front_Single_Module extends NBPC_Front_Module {
		public function pre_get_posts( WP_Query $query );
	}
}
