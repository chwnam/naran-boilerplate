<?php
/**
 * NBPC: Custom table register
 */

/* ABSPATH check */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'NBPC_Register_Custom_Table' ) ) {
	class NBPC_Register_Custom_Table extends NBPC_Register_Base_Custom_Table {
		use NBPC_Hook_Impl;

		/**
		 * Constructor
		 *
		 * You may need to use activation callback to create table and insert initial data.
		 * You may need to use 'plugins_loaded' callback to check db version and to update table.
		 */
		public function __constructor() {
			 $this
				->add_action( 'nbpc_activation', 'initial_setup' )
				->add_action( 'plugins_loaded', 'update_table' )
			;
		}

		public function get_items(): Generator {
			yield; // yield new NBPC_Reg_Custom_Table();
		}

		/**
		 * Return initial table data.
		 *
		 * @return array Key: table name
		 *               Val: Array of key-value pair.
		 */
		public function get_initial_data(): array {
			return [
				// 'nbpc_table' => [ 'col_a' => '1', 'col_b' => 'foo', ... ],
			];
		}

		/**
		 * Current version of DB table.
		 *
		 * @return string
		 */
		protected function get_current_version(): string {
			return ''; // Set DB version here.
		}
	}
}
