<?php
/**
 * BA3M:
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Admin_Post' ) ) {
	class NBPC_Admin_Post implements NBPC_Admin_Module {
		use NBPC_Hook_Impl;
		use NBPC_Template_Impl;

		public function __construct() {
		}
	}
}
