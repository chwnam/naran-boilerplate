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

		const DB_VERSION = ''; // Set DB version here.

		/**
		 * Constructor
		 *
		 * You may need to use activation callback to create table and insert initial data.
		 * You may need to use 'plugins_loaded' callback to check db version and to update table.
		 */
		public function __construct() {
//			$this
//				->add_action( 'nbpc_activation', 'initial_setup' )
//				->add_action( 'plugins_loaded', 'update_table' )
//			;
		}

		/**
		 * Return custom table items
		 *
		 * @return Generator
		 * @see    NBPC_Reg_Custom_Table::create_table()
		 */
		public function get_items(): Generator {
			global $wpdb;

			yield;

//			yield new NBPC_Reg_Custom_Table(
//				"{$wpdb->prefix}my_table",
//				[
//					"id bigint(20) NOT NULL AUTO_INCREMENT",
//					"time timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP",
//					"title varchar(100) NOT NULL",
//					"url varchar(200) NOT NULL",
//				],
//				[
//					"PRIMARY KEY  (id)",
//					"KEY idx_title_url (title, url)",
//					"UNIQUE KEY unique_url (url)",
//				]
//			);
		}

		/**
		 * Return initial table data.
		 *
		 * @return array Key: table name
		 *               Val: Array of key-value pair.
		 */
		public function get_initial_data(): array {
			global $wpdb;

			return [
//				"{$wpdb->prefix}my_table" => [
//					[
//						'title' => 'My Blog',
//						'url'   => 'https://my.blog.io/',
//					],
//					[
//						...
//					]
//				],
			];
		}
	}
}
