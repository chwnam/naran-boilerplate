<?php
/**
 * NBPC: Admin page module.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Admin_Page' ) ) {
	class NBPC_Admin_Page implements NBPC_Admin_Module {
		use NBPC_Hook_Impl;
		use NBPC_Template_Impl;

		public function __construct() {
		}
	}
}
