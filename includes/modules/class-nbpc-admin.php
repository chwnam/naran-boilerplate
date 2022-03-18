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

if ( ! class_exists( 'NBPC_Admin' ) ) {
	class NBPC_Admin implements NBPC_Module {
		use NBPC_Submodule_Impl;

		/**
		 * Constructor method
		 */
		public function __construct() {
			$this->assign_modules(
				[
					// Define submodules here.
				]
			);
		}
	}
}