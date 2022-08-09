<?php
/**
 * Naran Boilerplate Core
 *
 * interfaces/interface-nbpc-front-archive-module.php
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! interface_exists( 'NBPC_Front_Archive_Module' ) ) {
	interface NBPC_Front_Archive_Module extends NBPC_Front_Module {
		public function pre_get_posts( WP_Query $query );

		public function get_posts_per_page(): int;
	}
}
