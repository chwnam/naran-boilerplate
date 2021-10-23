<?php
/**
 * NBPC: Admin modules group
 *
 * Manage all admin modules
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Admins' ) ) {
	class NBPC_Admins implements NBPC_Module {
		use NBPC_Submodule_Impl;

		public function __construct() {
			$this->assign_modules(
				[
				]
			);
		}
	}
}
